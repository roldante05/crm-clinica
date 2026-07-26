<?php

namespace Database\Seeders;

use App\Models\Patient;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        $patients = [
            ['first_name' => 'Juan', 'last_name' => 'Pérez', 'phone' => '+54 11 5000-0001', 'document_type' => 'DNI', 'document_number' => '30123456', 'gender' => 'male', 'date_of_birth' => '1985-03-15'],
            ['first_name' => 'María', 'last_name' => 'González', 'phone' => '+54 11 5000-0002', 'document_type' => 'DNI', 'document_number' => '28456789', 'gender' => 'female', 'date_of_birth' => '1990-07-22'],
            ['first_name' => 'Pedro', 'last_name' => 'Rodríguez', 'phone' => '+54 11 5000-0003', 'document_type' => 'DNI', 'document_number' => '32789012', 'gender' => 'male', 'date_of_birth' => '1978-11-08'],
            ['first_name' => 'Lucía', 'last_name' => 'Fernández', 'phone' => '+54 11 5000-0004', 'document_type' => 'DNI', 'document_number' => '35012345', 'gender' => 'female', 'date_of_birth' => '1995-01-30'],
            ['first_name' => 'Carlos', 'last_name' => 'López', 'phone' => '+54 11 5000-0005', 'document_type' => 'DNI', 'document_number' => '27890123', 'gender' => 'male', 'date_of_birth' => '1982-06-12'],
            ['first_name' => 'Ana', 'last_name' => 'Martínez', 'phone' => '+54 11 5000-0006', 'document_type' => 'DNI', 'document_number' => '31234567', 'gender' => 'female', 'date_of_birth' => '1988-09-05'],
            ['first_name' => 'Roberto', 'last_name' => 'Sánchez', 'phone' => '+54 11 5000-0007', 'document_type' => 'DNI', 'document_number' => '29567890', 'gender' => 'male', 'date_of_birth' => '1975-04-18'],
            ['first_name' => 'Laura', 'last_name' => 'Ramírez', 'phone' => '+54 11 5000-0008', 'document_type' => 'DNI', 'document_number' => '33890123', 'gender' => 'female', 'date_of_birth' => '1992-12-01'],
            ['first_name' => 'Fernando', 'last_name' => 'Torres', 'phone' => '+54 11 5000-0009', 'document_type' => 'DNI', 'document_number' => '26345678', 'gender' => 'male', 'date_of_birth' => '1970-08-25'],
            ['first_name' => 'Valentina', 'last_name' => 'Díaz', 'phone' => '+54 11 5000-0010', 'document_type' => 'DNI', 'document_number' => '34678901', 'gender' => 'female', 'date_of_birth' => '1993-02-14'],
        ];

        foreach ($patients as $index => $patient) {
            Patient::create([
                ...$patient,
                'email' => strtolower($patient['first_name']).'.'.strtolower($patient['last_name']).'@email.com',
                'medical_record_number' => 'PM-'.str_pad($index + 1, 5, '0', STR_PAD_LEFT),
                'city' => 'Buenos Aires',
                'address' => 'Av. '.fake()->streetName().' '.fake()->buildingNumber(),
            ]);
        }
    }
}
