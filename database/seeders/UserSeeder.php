<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name' => 'Admin Sistema',
            'email' => 'admin@clinica.com',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
            'phone' => '+54 11 4000-0001',
        ]);

        // Secretary
        User::create([
            'name' => 'María García',
            'email' => 'maria@clinica.com',
            'password' => Hash::make('password'),
            'role' => UserRole::Secretary,
            'phone' => '+54 11 4000-0002',
        ]);

        // Doctors
        $doctors = [
            ['name' => 'Dr. Carlos López', 'email' => 'carlos.lopez@clinica.com', 'phone' => '+54 11 4000-0010'],
            ['name' => 'Dra. Ana Martínez', 'email' => 'ana.martinez@clinica.com', 'phone' => '+54 11 4000-0011'],
            ['name' => 'Dr. Roberto Sánchez', 'email' => 'roberto.sanchez@clinica.com', 'phone' => '+54 11 4000-0012'],
            ['name' => 'Dra. Laura Fernández', 'email' => 'laura.fernandez@clinica.com', 'phone' => '+54 11 4000-0013'],
            ['name' => 'Dr. Miguel Ángel Torres', 'email' => 'miguel.torres@clinica.com', 'phone' => '+54 11 4000-0014'],
        ];

        foreach ($doctors as $doctor) {
            User::create([
                ...$doctor,
                'password' => Hash::make('password'),
                'role' => UserRole::Doctor,
            ]);
        }
    }
}
