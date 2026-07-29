<?php

declare(strict_types=1);

use App\Data\Clinic\CreatePatientData;
use App\Data\Clinic\UpdatePatientData;
use App\Enums\AppointmentStatus;
use App\Exceptions\AuthorizationException;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use App\Services\PatientService;

beforeEach(function () {
    $this->service = app(PatientService::class);
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

test('create persists patient', function () {
    $data = new CreatePatientData(
        first_name: 'Carlos',
        last_name: 'López',
        email: null,
        phone: '+54 11 5555-1234',
        secondary_phone: null,
        document_type: null,
        document_number: null,
        date_of_birth: null,
        gender: null,
        address: null,
        city: null,
        notes: null,
    );

    $patient = $this->service->create($data);

    expect($patient)->toBeInstanceOf(Patient::class)
        ->and(Patient::count())->toBe(1);
});

test('create throws AuthorizationException for doctor user', function () {
    $doctorUser = User::factory()->doctor()->create();
    $this->actingAs($doctorUser);

    $data = new CreatePatientData(
        first_name: 'Test',
        last_name: 'User',
        email: null,
        phone: '+54 11 5555-0000',
        secondary_phone: null,
        document_type: null,
        document_number: null,
        date_of_birth: null,
        gender: null,
        address: null,
        city: null,
        notes: null,
    );

    $this->expectException(AuthorizationException::class);
    $this->service->create($data);
});

test('update persists changes', function () {
    $patient = Patient::factory()->create(['first_name' => 'Juan']);

    $data = new UpdatePatientData(
        first_name: 'Juan Carlos',
        last_name: $patient->last_name,
        email: $patient->email,
        phone: $patient->phone,
        secondary_phone: $patient->secondary_phone,
        document_type: $patient->document_type,
        document_number: $patient->document_number,
        date_of_birth: $patient->date_of_birth?->toDateString(),
        gender: $patient->gender?->value,
        address: $patient->address,
        city: $patient->city,
        notes: $patient->notes,
    );

    $updated = $this->service->update($patient, $data);

    expect($updated->first_name)->toBe('Juan Carlos');
});

test('update throws AuthorizationException for doctor user', function () {
    $doctorUser = User::factory()->doctor()->create();
    $this->actingAs($doctorUser);
    $patient = Patient::factory()->create();

    $data = new UpdatePatientData(
        first_name: 'Hacked',
        last_name: $patient->last_name,
        email: $patient->email,
        phone: $patient->phone,
        secondary_phone: $patient->secondary_phone,
        document_type: $patient->document_type,
        document_number: $patient->document_number,
        date_of_birth: $patient->date_of_birth?->toDateString(),
        gender: $patient->gender?->value,
        address: $patient->address,
        city: $patient->city,
        notes: $patient->notes,
    );

    $this->expectException(AuthorizationException::class);
    $this->service->update($patient, $data);
});

test('delete cancels future appointments and soft-deletes patient', function () {
    $patient = Patient::factory()->create();
    $futureAppointment = Appointment::factory()->forPatient($patient)->create([
        'appointment_date' => now()->addDays(5)->toDateString(),
        'status' => AppointmentStatus::Scheduled,
    ]);
    $pastAppointment = Appointment::factory()->forPatient($patient)->create([
        'appointment_date' => now()->subDays(5)->toDateString(),
        'status' => AppointmentStatus::Completed,
    ]);

    $this->service->delete($patient);

    expect(Patient::find($patient->id))->toBeNull()
        ->and(Patient::withTrashed()->find($patient->id))->not->toBeNull();

    $futureAppointment->refresh();
    expect($futureAppointment->status)->toBe(AppointmentStatus::Cancelled);

    $pastAppointment->refresh();
    expect($pastAppointment->status)->toBe(AppointmentStatus::Completed); // unchanged
});

test('delete throws AuthorizationException for doctor user', function () {
    $doctorUser = User::factory()->doctor()->create();
    $this->actingAs($doctorUser);
    $patient = Patient::factory()->create();

    $this->expectException(AuthorizationException::class);
    $this->service->delete($patient);
});
