<?php

declare(strict_types=1);

use App\Data\Clinic\CreateDoctorData;
use App\Data\Clinic\UpdateDoctorData;
use App\Models\Doctor;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Routing\Route;
use Illuminate\Validation\ValidationException;

test('create doctor data validates with all required fields', function () {
    $user = User::factory()->doctor()->create();
    $specialty = Specialty::factory()->create();

    $data = CreateDoctorData::validateAndCreate([
        'user_id' => $user->id,
        'specialty_id' => $specialty->id,
        'license_number' => 'MAT-12345',
    ]);

    expect($data)->toBeInstanceOf(CreateDoctorData::class)
        ->and($data->license_number)->toBe('MAT-12345')
        ->and($data->user_id)->toBe($user->id);
});

test('create doctor data fails when specialty_id is missing', function () {
    CreateDoctorData::validateAndCreate([
        'user_id' => 1,
        'license_number' => 'MAT-12345',
    ]);
})->throws(ValidationException::class);

test('create doctor data accepts optional fields', function () {
    $user = User::factory()->doctor()->create();
    $specialty = Specialty::factory()->create();

    $data = CreateDoctorData::validateAndCreate([
        'user_id' => $user->id,
        'specialty_id' => $specialty->id,
        'license_number' => 'MAT-12345',
        'phone' => '+54 11 5555-0000',
        'is_active' => true,
    ]);

    expect($data->phone)->toBe('+54 11 5555-0000')
        ->and($data->is_active)->toBeTrue();
});

test('update doctor data validates with route parameter', function () {
    $doctor = Doctor::factory()->create();

    request()->setRouteResolver(function () use ($doctor) {
        $route = new Route('PUT', '/doctors/{doctor}', []);
        $route->bind(request());
        $route->setParameter('doctor', $doctor);

        return $route;
    });

    $data = UpdateDoctorData::validateAndCreate([
        'specialty_id' => $doctor->specialty_id,
        'license_number' => $doctor->license_number,
        'phone' => '+54 11 5555-9999',
    ]);

    expect($data)->toBeInstanceOf(UpdateDoctorData::class)
        ->and($data->phone)->toBe('+54 11 5555-9999');
});
