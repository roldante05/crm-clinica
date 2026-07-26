<?php

namespace Database\Seeders;

use App\Models\Specialty;
use Illuminate\Database\Seeder;

class SpecialtySeeder extends Seeder
{
    public function run(): void
    {
        $specialties = [
            ['name' => 'Clínica General', 'description' => 'Atención médica general y preventiva'],
            ['name' => 'Cardiología', 'description' => 'Diagnóstico y tratamiento de enfermedades del corazón'],
            ['name' => 'Pediatría', 'description' => 'Atención médica de niños y adolescentes'],
            ['name' => 'Ginecología', 'description' => 'Salud femenina y sistema reproductivo'],
            ['name' => 'Dermatología', 'description' => 'Enfermedades de la piel, cabello y uñas'],
            ['name' => 'Traumatología', 'description' => 'Lesiones del sistema musculoesquelético'],
            ['name' => 'Oftalmología', 'description' => 'Enfermedades y trastornos de los ojos'],
            ['name' => 'Odontología', 'description' => 'Salud bucal y dental'],
            ['name' => 'Psiquiatría', 'description' => 'Salud mental y trastornos psiquiátricos'],
            ['name' => 'Nutrición', 'description' => 'Alimentación y dietética'],
        ];

        foreach ($specialties as $specialty) {
            Specialty::create($specialty);
        }
    }
}
