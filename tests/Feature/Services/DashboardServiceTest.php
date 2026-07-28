<?php

declare(strict_types=1);

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Services\DashboardService;

test('getStats returns today appointments count', function () {
    $patient = Patient::factory()->create();
    $doctors = Doctor::factory()->count(5)->create();

    foreach ($doctors as $doctor) {
        Appointment::factory()->create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'appointment_date' => now()->toDateString(),
        ]);
    }

    $service = app(DashboardService::class);
    $stats = $service->getStats();

    expect($stats)->toHaveKey('today_appointments')
        ->and($stats['today_appointments'])->toBe(5);
});

test('getTodayAppointments returns today appointments', function () {
    $patient = Patient::factory()->create();
    $doctors = Doctor::factory()->count(3)->create();

    foreach ($doctors as $doctor) {
        Appointment::factory()->create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'appointment_date' => now()->toDateString(),
        ]);
    }

    $service = app(DashboardService::class);
    $appointments = $service->getTodayAppointments();

    expect($appointments)->toHaveCount(3);
});
