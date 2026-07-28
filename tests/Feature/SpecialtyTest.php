<?php

declare(strict_types=1);

use App\Models\Doctor;
use App\Models\Specialty;

test('specialty has many doctors', function () {
    $specialty = Specialty::factory()->create();
    Doctor::factory()->count(3)->forSpecialty($specialty)->create();

    expect($specialty->doctors)->toHaveCount(3);
});

test('specialty scope active filters inactive', function () {
    Specialty::factory()->count(3)->create();
    Specialty::factory()->count(2)->inactive()->create();

    expect(Specialty::active()->count())->toBe(3);
});

test('specialty can be created with valid data', function () {
    $specialty = Specialty::create([
        'name' => 'Cardiología',
        'description' => 'Enfermedades del corazón',
        'is_active' => true,
    ]);

    expect($specialty->name)->toBe('Cardiología')
        ->and($specialty->is_active)->toBeTrue();
});
