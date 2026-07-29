<?php

declare(strict_types=1);

use App\Data\Clinic\CreateDoctorScheduleData;
use App\Data\Clinic\UpdateDoctorScheduleData;
use Illuminate\Routing\Route;
use Illuminate\Validation\ValidationException;

test('create doctor schedule data validates with required fields', function () {
    request()->setRouteResolver(function () {
        $route = new Route('POST', '/doctors/{doctor}/schedules', []);
        $route->bind(request());

        return $route;
    });

    $data = CreateDoctorScheduleData::validateAndCreate([
        'day_of_week' => 1,
        'start_time' => '09:00',
        'end_time' => '17:00',
        'slot_duration_minutes' => 30,
    ]);

    expect($data)->toBeInstanceOf(CreateDoctorScheduleData::class)
        ->and($data->day_of_week)->toBe(1)
        ->and($data->start_time)->toBe('09:00');
});

test('create doctor schedule data fails without day_of_week', function () {
    CreateDoctorScheduleData::validateAndCreate([
        'start_time' => '09:00',
        'end_time' => '17:00',
        'slot_duration_minutes' => 30,
    ]);
})->throws(ValidationException::class);

test('create doctor schedule data fails with invalid day_of_week', function () {
    CreateDoctorScheduleData::validateAndCreate([
        'day_of_week' => 7,
        'start_time' => '09:00',
        'end_time' => '17:00',
        'slot_duration_minutes' => 30,
    ]);
})->throws(ValidationException::class);

test('create doctor schedule data fails when end_time before start_time', function () {
    CreateDoctorScheduleData::validateAndCreate([
        'day_of_week' => 1,
        'start_time' => '17:00',
        'end_time' => '09:00',
        'slot_duration_minutes' => 30,
    ]);
})->throws(ValidationException::class);

test('create doctor schedule data accepts is_active', function () {
    request()->setRouteResolver(function () {
        $route = new Route('POST', '/doctors/{doctor}/schedules', []);
        $route->bind(request());

        return $route;
    });

    $data = CreateDoctorScheduleData::validateAndCreate([
        'day_of_week' => 1,
        'start_time' => '09:00',
        'end_time' => '17:00',
        'slot_duration_minutes' => 30,
        'is_active' => false,
    ]);

    expect($data->is_active)->toBeFalse();
});

test('update doctor schedule data validates', function () {
    $data = UpdateDoctorScheduleData::validateAndCreate([
        'day_of_week' => 2,
        'start_time' => '10:00',
        'end_time' => '18:00',
        'slot_duration_minutes' => 30,
    ]);

    expect($data)->toBeInstanceOf(UpdateDoctorScheduleData::class)
        ->and($data->start_time)->toBe('10:00');
});

test('update doctor schedule data fails with invalid slot_duration', function () {
    UpdateDoctorScheduleData::validateAndCreate([
        'day_of_week' => 1,
        'start_time' => '09:00',
        'end_time' => '17:00',
        'slot_duration_minutes' => 5,
    ]);
})->throws(ValidationException::class);
