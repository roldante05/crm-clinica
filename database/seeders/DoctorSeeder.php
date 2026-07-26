<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Doctor;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Database\Seeder;

class DoctorSeeder extends Seeder
{
    public function run(): void
    {
        $doctorUsers = User::where('role', UserRole::Doctor)->get();
        $specialties = Specialty::all();

        $licenseBase = 10000;

        foreach ($doctorUsers as $index => $user) {
            Doctor::create([
                'user_id' => $user->id,
                'specialty_id' => $specialties[$index % $specialties->count()]->id,
                'license_number' => 'MAT-'.($licenseBase + $index),
                'phone' => $user->phone,
                'is_active' => true,
            ]);
        }
    }
}
