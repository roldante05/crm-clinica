<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\DayOfWeek;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use Illuminate\Database\Seeder;

class DoctorScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $doctors = Doctor::all();

        $weekdays = [
            DayOfWeek::Monday,
            DayOfWeek::Tuesday,
            DayOfWeek::Wednesday,
            DayOfWeek::Thursday,
            DayOfWeek::Friday,
        ];

        foreach ($doctors as $doctor) {
            // Morning shift
            foreach ($weekdays as $day) {
                DoctorSchedule::create([
                    'doctor_id' => $doctor->id,
                    'day_of_week' => $day,
                    'start_time' => '08:00',
                    'end_time' => '12:00',
                    'slot_duration_minutes' => 30,
                    'is_active' => true,
                ]);
            }

            // Afternoon shift (not all doctors)
            if (rand(0, 1)) {
                foreach ($weekdays as $day) {
                    DoctorSchedule::create([
                        'doctor_id' => $doctor->id,
                        'day_of_week' => $day,
                        'start_time' => '14:00',
                        'end_time' => '18:00',
                        'slot_duration_minutes' => 30,
                        'is_active' => true,
                    ]);
                }
            }
        }
    }
}
