<?php

use App\Enums\DayOfWeek;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Specialty;
use App\Models\User;

test('doctor belongs to user', function () {
    $doctor = Doctor::factory()->create();

    expect($doctor->user)->toBeInstanceOf(User::class)
        ->and($doctor->user->id)->toBe($doctor->user_id);
});

test('doctor belongs to specialty', function () {
    $specialty = Specialty::factory()->create();
    $doctor = Doctor::factory()->forSpecialty($specialty)->create();

    expect($doctor->specialty->id)->toBe($specialty->id)
        ->and($doctor->specialty->name)->toBe($specialty->name);
});

test('doctor has many schedules', function () {
    $doctor = Doctor::factory()->create();
    DoctorSchedule::factory()->forDoctor($doctor)->morning()->forDay(DayOfWeek::Monday)->create();
    DoctorSchedule::factory()->forDoctor($doctor)->afternoon()->forDay(DayOfWeek::Monday)->create();
    DoctorSchedule::factory()->forDoctor($doctor)->morning()->forDay(DayOfWeek::Tuesday)->create();

    expect($doctor->schedules)->toHaveCount(3);
});

test('doctor has many appointments', function () {
    $doctor = Doctor::factory()->create();
    $times = ['08:00', '10:00', '12:00', '14:00', '16:00'];

    foreach ($times as $time) {
        Appointment::factory()->forDoctor($doctor)->create([
            'appointment_date' => now()->toDateString(),
            'appointment_time' => $time,
        ]);
    }

    expect($doctor->appointments)->toHaveCount(5);
});

test('doctor full name accessor', function () {
    $doctor = Doctor::factory()->create();
    $doctor->user->update(['name' => 'Dr. Carlos López']);

    expect($doctor->full_name)->toBe('Dr. Carlos López');
});

test('doctor scope active filters inactive', function () {
    Doctor::factory()->count(3)->create();
    Doctor::factory()->count(2)->inactive()->create();

    expect(Doctor::active()->count())->toBe(3);
});

test('doctor scope for specialty filters correctly', function () {
    $cardio = Specialty::factory()->create(['name' => 'Cardiología']);
    $pedia = Specialty::factory()->create(['name' => 'Pediatría']);

    Doctor::factory()->count(2)->forSpecialty($cardio)->create();
    Doctor::factory()->count(3)->forSpecialty($pedia)->create();

    expect(Doctor::forSpecialty($cardio->id)->count())->toBe(2)
        ->and(Doctor::forSpecialty($pedia->id)->count())->toBe(3);
});
