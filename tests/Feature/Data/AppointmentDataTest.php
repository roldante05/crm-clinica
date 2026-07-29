<?php

declare(strict_types=1);

use App\Data\Clinic\CreateAppointmentData;
use App\Data\Clinic\UpdateAppointmentData;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use Illuminate\Routing\Route;
use Illuminate\Validation\ValidationException;

test('create appointment data validates with required fields', function () {
    $doctor = Doctor::factory()->create();
    $patient = Patient::factory()->create();
    DoctorSchedule::factory()->forDoctor($doctor)->create([
        'day_of_week' => now()->addDay()->dayOfWeek,
        'start_time' => '08:00',
        'end_time' => '18:00',
    ]);

    request()->setRouteResolver(function () {
        $route = new Route('POST', '/appointments', []);
        $route->bind(request());

        return $route;
    });

    $data = CreateAppointmentData::validateAndCreate([
        'doctor_id' => $doctor->id,
        'patient_id' => $patient->id,
        'appointment_date' => now()->addDay()->toDateString(),
        'appointment_time' => '09:00',
    ]);

    expect($data)->toBeInstanceOf(CreateAppointmentData::class)
        ->and($data->appointment_time)->toBe('09:00');
});

test('create appointment data fails without doctor_id', function () {
    CreateAppointmentData::validateAndCreate([
        'patient_id' => 1,
        'appointment_date' => now()->addDay()->toDateString(),
        'appointment_time' => '09:00',
    ]);
})->throws(ValidationException::class);

test('create appointment data fails with past date', function () {
    CreateAppointmentData::validateAndCreate([
        'doctor_id' => 1,
        'patient_id' => 1,
        'appointment_date' => now()->subDay()->toDateString(),
        'appointment_time' => '09:00',
    ]);
})->throws(ValidationException::class);

test('create appointment data detects time conflict', function () {
    $doctor = Doctor::factory()->create();
    $patient = Patient::factory()->create();
    $otherPatient = Patient::factory()->create();
    DoctorSchedule::factory()->forDoctor($doctor)->create([
        'day_of_week' => now()->addDay()->dayOfWeek,
        'start_time' => '08:00',
        'end_time' => '18:00',
    ]);

    Appointment::factory()->forDoctor($doctor)->forPatient($patient)->create([
        'appointment_date' => now()->addDay()->toDateString(),
        'appointment_time' => '09:00',
    ]);

    // withValidator only fires in auto-resolution path (controller),
    // not in validateAndCreate. This is tested via HTTP feature tests.
})->skip('withValidator is tested via HTTP feature tests');

test('create appointment data accepts optional duration and reason', function () {
    $doctor = Doctor::factory()->create();
    $patient = Patient::factory()->create();
    DoctorSchedule::factory()->forDoctor($doctor)->create([
        'day_of_week' => now()->addDay()->dayOfWeek,
        'start_time' => '08:00',
        'end_time' => '18:00',
    ]);

    request()->setRouteResolver(function () {
        $route = new Route('POST', '/appointments', []);
        $route->bind(request());

        return $route;
    });

    $data = CreateAppointmentData::validateAndCreate([
        'doctor_id' => $doctor->id,
        'patient_id' => $patient->id,
        'appointment_date' => now()->addDay()->toDateString(),
        'appointment_time' => '09:00',
        'duration_minutes' => 45,
        'reason' => 'Control de rutina',
    ]);

    expect($data->duration_minutes)->toBe(45)
        ->and($data->reason)->toBe('Control de rutina');
});

test('update appointment data validates with sometimes fields', function () {
    $data = UpdateAppointmentData::validateAndCreate([
        'appointment_date' => now()->addDay()->toDateString(),
        'appointment_time' => '10:00',
    ]);

    expect($data)->toBeInstanceOf(UpdateAppointmentData::class);
});

test('update appointment data requires cancellation_reason when status is cancelled', function () {
    UpdateAppointmentData::validateAndCreate([
        'status' => 'cancelled',
    ]);
})->throws(ValidationException::class);

test('update appointment data allows valid statuses', function () {
    $data = UpdateAppointmentData::validateAndCreate([
        'status' => 'completed',
    ]);

    expect($data->status)->toBe('completed');
});

test('update appointment data fails with invalid status', function () {
    UpdateAppointmentData::validateAndCreate([
        'status' => 'invalid_status',
    ]);
})->throws(ValidationException::class);
