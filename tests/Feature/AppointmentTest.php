<?php

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AppointmentNote;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;

test('appointment belongs to doctor', function () {
    $appointment = Appointment::factory()->create();

    expect($appointment->doctor)->toBeInstanceOf(Doctor::class);
});

test('appointment belongs to patient', function () {
    $appointment = Appointment::factory()->create();

    expect($appointment->patient)->toBeInstanceOf(Patient::class);
});

test('appointment belongs to created by user', function () {
    $secretary = User::factory()->secretary()->create();
    $appointment = Appointment::factory()->createdBy($secretary)->create();

    expect($appointment->createdBy->id)->toBe($secretary->id);
});

test('appointment has many notes', function () {
    $appointment = Appointment::factory()->create();
    AppointmentNote::factory()->count(3)->forAppointment($appointment)->create();

    expect($appointment->notes)->toHaveCount(3);
});

test('appointment casts status as enum', function () {
    $appointment = Appointment::factory()->create([
        'status' => AppointmentStatus::Confirmed,
    ]);

    expect($appointment->status)->toBeInstanceOf(AppointmentStatus::class)
        ->and($appointment->status)->toBe(AppointmentStatus::Confirmed);
});

test('appointment confirm method updates status', function () {
    $appointment = Appointment::factory()->scheduled()->create();
    $user = User::factory()->create();

    $appointment->confirm($user->id);

    expect($appointment->fresh()->status)->toBe(AppointmentStatus::Confirmed)
        ->and($appointment->fresh()->confirmed_by)->toBe($user->id)
        ->and($appointment->fresh()->confirmed_at)->not->toBeNull();
});

test('appointment complete method updates status', function () {
    $appointment = Appointment::factory()->confirmed()->create();

    $appointment->complete();

    expect($appointment->fresh()->status)->toBe(AppointmentStatus::Completed);
});

test('appointment cancel method updates status with reason', function () {
    $appointment = Appointment::factory()->create();
    $user = User::factory()->create();

    $appointment->cancel('Paciente no puede asistir', $user->id);

    expect($appointment->fresh()->status)->toBe(AppointmentStatus::Cancelled)
        ->and($appointment->fresh()->cancellation_reason)->toBe('Paciente no puede asistir')
        ->and($appointment->fresh()->cancelled_by)->toBe($user->id)
        ->and($appointment->fresh()->cancelled_at)->not->toBeNull();
});

test('appointment mark no show method updates status', function () {
    $appointment = Appointment::factory()->create();

    $appointment->markNoShow();

    expect($appointment->fresh()->status)->toBe(AppointmentStatus::NoShow);
});

test('appointment scope today filters correctly', function () {
    $doctor = Doctor::factory()->create();
    $times = ['08:00', '10:00', '12:00'];

    foreach ($times as $time) {
        Appointment::factory()->forDoctor($doctor)->today()->create([
            'appointment_time' => $time,
        ]);
    }

    Appointment::factory()->forDoctor($doctor)->create([
        'appointment_date' => now()->addDay()->toDateString(),
        'appointment_time' => '08:00',
    ]);

    expect(Appointment::today()->count())->toBe(3);
});

test('appointment scope upcoming excludes cancelled and completed', function () {
    $doctor = Doctor::factory()->create();

    Appointment::factory()->forDoctor($doctor)->today()->scheduled()->create(['appointment_time' => '08:00']);
    Appointment::factory()->forDoctor($doctor)->today()->confirmed()->create(['appointment_time' => '10:00']);
    Appointment::factory()->forDoctor($doctor)->today()->cancelled()->create(['appointment_time' => '12:00']);
    Appointment::factory()->forDoctor($doctor)->today()->completed()->create(['appointment_time' => '14:00']);
    Appointment::factory()->forDoctor($doctor)->today()->noShow()->create(['appointment_time' => '16:00']);

    expect(Appointment::upcoming()->count())->toBe(2);
});

test('appointment scope for doctor filters correctly', function () {
    $doctor1 = Doctor::factory()->create();
    $doctor2 = Doctor::factory()->create();

    Appointment::factory()->count(3)->forDoctor($doctor1)->create();
    Appointment::factory()->count(2)->forDoctor($doctor2)->create();

    expect(Appointment::forDoctor($doctor1->id)->count())->toBe(3)
        ->and(Appointment::forDoctor($doctor2->id)->count())->toBe(2);
});

test('appointment scope for patient filters correctly', function () {
    $patient1 = Patient::factory()->create();
    $patient2 = Patient::factory()->create();

    Appointment::factory()->count(2)->forPatient($patient1)->create();
    Appointment::factory()->count(4)->forPatient($patient2)->create();

    expect(Appointment::forPatient($patient1->id)->count())->toBe(2)
        ->and(Appointment::forPatient($patient2->id)->count())->toBe(4);
});

test('appointment unique constraint prevents double booking', function () {
    $this->expectException(UniqueConstraintViolationException::class);

    $doctor = Doctor::factory()->create();
    $date = now()->toDateString();

    Appointment::factory()->forDoctor($doctor)->create([
        'appointment_date' => $date,
        'appointment_time' => '10:00',
    ]);

    Appointment::factory()->forDoctor($doctor)->create([
        'appointment_date' => $date,
        'appointment_time' => '10:00',
    ]);
});
