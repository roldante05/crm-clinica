<?php

declare(strict_types=1);

use App\Enums\AppointmentStatus;
use App\Enums\DayOfWeek;
use App\Exceptions\AuthorizationException;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use App\Models\User;
use App\Services\AppointmentService;

beforeEach(function () {
    $this->service = app(AppointmentService::class);
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

test('create sets duration from schedule when not provided', function () {
    $doctor = Doctor::factory()->create();
    DoctorSchedule::factory()->forDoctor($doctor)->morning()->forDay(DayOfWeek::from(now()->dayOfWeek))->create();
    $patient = Patient::factory()->create();

    $appointment = $this->service->create([
        'doctor_id' => $doctor->id,
        'patient_id' => $patient->id,
        'appointment_date' => now()->toDateString(),
        'appointment_time' => '09:00',
        'reason' => 'Control',
    ], $this->admin->id);

    expect($appointment)->toBeInstanceOf(Appointment::class)
        ->and($appointment->duration_minutes)->toBe(30)
        ->and($appointment->created_by)->toBe($this->admin->id);
});

test('create uses provided duration when given', function () {
    $doctor = Doctor::factory()->create();
    $patient = Patient::factory()->create();

    $appointment = $this->service->create([
        'doctor_id' => $doctor->id,
        'patient_id' => $patient->id,
        'appointment_date' => now()->toDateString(),
        'appointment_time' => '10:00',
        'duration_minutes' => 45,
        'reason' => 'Control',
    ], $this->admin->id);

    expect($appointment->duration_minutes)->toBe(45);
});

test('update calls confirm on appointment when status is Confirmed', function () {
    $appointment = Appointment::factory()->create(['status' => AppointmentStatus::Scheduled]);

    $this->service->update($appointment, ['status' => AppointmentStatus::Confirmed->value]);

    expect($appointment->fresh()->status)->toBe(AppointmentStatus::Confirmed);
});

test('update cancels appointment with reason', function () {
    $appointment = Appointment::factory()->create(['status' => AppointmentStatus::Scheduled]);

    $this->service->update($appointment, [
        'status' => AppointmentStatus::Cancelled->value,
        'cancellation_reason' => 'Motivo de prueba',
    ]);

    $fresh = $appointment->fresh();
    expect($fresh->status)->toBe(AppointmentStatus::Cancelled)
        ->and($fresh->cancellation_reason)->toBe('Motivo de prueba');
});

test('addNote creates note for appointment', function () {
    $appointment = Appointment::factory()->create();

    $this->service->addNote($appointment, 'Nota de prueba', $this->admin->id);

    expect($appointment->notes()->count())->toBe(1)
        ->and($appointment->notes()->first()->content)->toBe('Nota de prueba');
});

test('delete removes appointment', function () {
    $appointment = Appointment::factory()->create();

    $this->service->delete($appointment);

    expect(Appointment::find($appointment->id))->toBeNull();
});

test('show loads relationships', function () {
    $appointment = Appointment::factory()->create();

    $result = $this->service->show($appointment);

    expect($result->relationLoaded('doctor'))->toBeTrue()
        ->and($result->relationLoaded('notes'))->toBeTrue();
});

test('show throws AuthorizationException for doctor viewing other appointment', function () {
    $doctorUser = User::factory()->doctor()->create();
    $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);
    $otherDoctor = Doctor::factory()->create();
    $appointment = Appointment::factory()->forDoctor($otherDoctor)->create();

    $this->actingAs($doctorUser);

    $this->expectException(AuthorizationException::class);
    $this->service->show($appointment);
});
