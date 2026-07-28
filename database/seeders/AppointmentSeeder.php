<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $doctors = Doctor::all();
        $patients = Patient::all();
        $secretary = User::where('role', UserRole::Secretary)->first();

        // Track used slots to avoid unique constraint violations
        $usedSlots = [];

        // Today's appointments (5-8)
        $todayCount = rand(5, 8);
        for ($i = 0; $i < $todayCount; $i++) {
            $hour = 8 + ($i * 2);
            $doctor = $doctors->random();
            $date = now()->toDateString();
            $slotKey = "{$doctor->id}-{$date}-{$hour}:00";

            if (isset($usedSlots[$slotKey])) {
                continue;
            }

            $usedSlots[$slotKey] = true;

            Appointment::create([
                'doctor_id' => $doctor->id,
                'patient_id' => $patients->random()->id,
                'appointment_date' => $date,
                'appointment_time' => sprintf('%02d:00', $hour),
                'duration_minutes' => 30,
                'status' => fake()->randomElement([
                    AppointmentStatus::Scheduled,
                    AppointmentStatus::Confirmed,
                    AppointmentStatus::Completed,
                ]),
                'reason' => fake()->randomElement([
                    'Control anual',
                    'Dolor de cabeza',
                    'Fiebre persistente',
                    'Resultados de análisis',
                    'Seguimiento tratamiento',
                    'Consulta general',
                ]),
                'created_by' => $secretary->id,
            ]);
        }

        // Tomorrow's appointments (3-5)
        $tomorrowCount = rand(3, 5);
        for ($i = 0; $i < $tomorrowCount; $i++) {
            $hour = 9 + ($i * 2);
            $doctor = $doctors->random();
            $date = now()->addDay()->toDateString();
            $slotKey = "{$doctor->id}-{$date}-{$hour}:00";

            if (isset($usedSlots[$slotKey])) {
                continue;
            }

            $usedSlots[$slotKey] = true;

            Appointment::create([
                'doctor_id' => $doctor->id,
                'patient_id' => $patients->random()->id,
                'appointment_date' => $date,
                'appointment_time' => sprintf('%02d:00', $hour),
                'duration_minutes' => 30,
                'status' => AppointmentStatus::Scheduled,
                'reason' => fake()->randomElement([
                    'Primera consulta',
                    'Seguimiento',
                    'Resultado de estudios',
                    'Consulta de rutina',
                ]),
                'created_by' => $secretary->id,
            ]);
        }

        // Past appointments (10-15)
        $pastCount = rand(10, 15);
        for ($i = 0; $i < $pastCount; $i++) {
            $hour = 8 + rand(0, 8);
            $doctor = $doctors->random();
            $date = now()->subDays(rand(1, 30))->toDateString();
            $slotKey = "{$doctor->id}-{$date}-{$hour}:00";

            if (isset($usedSlots[$slotKey])) {
                continue;
            }

            $usedSlots[$slotKey] = true;

            Appointment::create([
                'doctor_id' => $doctor->id,
                'patient_id' => $patients->random()->id,
                'appointment_date' => $date,
                'appointment_time' => sprintf('%02d:00', $hour),
                'duration_minutes' => 30,
                'status' => fake()->randomElement([
                    AppointmentStatus::Completed,
                    AppointmentStatus::Cancelled,
                    AppointmentStatus::NoShow,
                ]),
                'reason' => fake()->sentence(),
                'created_by' => $secretary->id,
            ]);
        }
    }
}
