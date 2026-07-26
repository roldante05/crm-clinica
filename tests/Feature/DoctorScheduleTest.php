<?php

use App\Enums\DayOfWeek;
use App\Models\Doctor;
use App\Models\DoctorSchedule;

test('doctor schedule belongs to doctor', function () {
    $schedule = DoctorSchedule::factory()->create();

    expect($schedule->doctor)->toBeInstanceOf(Doctor::class);
});

test('doctor schedule casts day of week as enum', function () {
    $schedule = DoctorSchedule::factory()->create([
        'day_of_week' => DayOfWeek::Monday,
    ]);

    expect($schedule->day_of_week)->toBeInstanceOf(DayOfWeek::class)
        ->and($schedule->day_of_week)->toBe(DayOfWeek::Monday);
});

test('doctor scope active filters inactive', function () {
    $doctor = Doctor::factory()->create();

    DoctorSchedule::factory()->forDoctor($doctor)->morning()->forDay(DayOfWeek::Monday)->create();
    DoctorSchedule::factory()->forDoctor($doctor)->afternoon()->forDay(DayOfWeek::Monday)->create();
    DoctorSchedule::factory()->forDoctor($doctor)->morning()->forDay(DayOfWeek::Tuesday)->inactive()->create();

    expect(DoctorSchedule::active()->count())->toBe(2);
});

test('doctor scope for day filters correctly', function () {
    $doctor = Doctor::factory()->create();

    DoctorSchedule::factory()->forDoctor($doctor)->morning()->forDay(DayOfWeek::Monday)->create();
    DoctorSchedule::factory()->forDoctor($doctor)->afternoon()->forDay(DayOfWeek::Monday)->create();
    DoctorSchedule::factory()->forDoctor($doctor)->morning()->forDay(DayOfWeek::Friday)->create();

    expect(DoctorSchedule::forDay(DayOfWeek::Monday)->count())->toBe(2)
        ->and(DoctorSchedule::forDay(DayOfWeek::Friday)->count())->toBe(1);
});

test('doctor scope for doctor filters correctly', function () {
    $doctor1 = Doctor::factory()->create();
    $doctor2 = Doctor::factory()->create();

    DoctorSchedule::factory()->forDoctor($doctor1)->morning()->forDay(DayOfWeek::Monday)->create();
    DoctorSchedule::factory()->forDoctor($doctor1)->afternoon()->forDay(DayOfWeek::Monday)->create();
    DoctorSchedule::factory()->forDoctor($doctor2)->morning()->forDay(DayOfWeek::Monday)->create();

    expect(DoctorSchedule::forDoctor($doctor1->id)->count())->toBe(2)
        ->and(DoctorSchedule::forDoctor($doctor2->id)->count())->toBe(1);
});

test('morning schedule factory creates correct times', function () {
    $schedule = DoctorSchedule::factory()->morning()->create();

    expect($schedule->start_time)->toBe('08:00')
        ->and($schedule->end_time)->toBe('12:00');
});

test('afternoon schedule factory creates correct times', function () {
    $schedule = DoctorSchedule::factory()->afternoon()->create();

    expect($schedule->start_time)->toBe('14:00')
        ->and($schedule->end_time)->toBe('18:00');
});
