<?php

declare(strict_types=1);

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
    $patient = $this->service->create([
        'first_name' => 'Carlos',
        'last_name' => 'López',
        'phone' => '+54 11 5555-1234',
    ]);

    expect($patient)->toBeInstanceOf(Patient::class)
        ->and(Patient::count())->toBe(1);
});

test('create throws AuthorizationException for doctor user', function () {
    $doctorUser = User::factory()->doctor()->create();
    $this->actingAs($doctorUser);

    $this->expectException(AuthorizationException::class);
    $this->service->create([
        'first_name' => 'Test',
        'last_name' => 'User',
        'phone' => '+54 11 5555-0000',
    ]);
});

test('update persists changes', function () {
    $patient = Patient::factory()->create(['first_name' => 'Juan']);

    $updated = $this->service->update($patient, ['first_name' => 'Juan Carlos']);

    expect($updated->first_name)->toBe('Juan Carlos');
});

test('update throws AuthorizationException for doctor user', function () {
    $doctorUser = User::factory()->doctor()->create();
    $this->actingAs($doctorUser);
    $patient = Patient::factory()->create();

    $this->expectException(AuthorizationException::class);
    $this->service->update($patient, ['first_name' => 'Hacked']);
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
