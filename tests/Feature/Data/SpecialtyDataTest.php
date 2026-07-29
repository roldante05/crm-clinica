<?php

declare(strict_types=1);

use App\Data\Clinic\CreateSpecialtyData;
use App\Data\Clinic\UpdateSpecialtyData;
use App\Models\Specialty;
use Illuminate\Routing\Route;
use Illuminate\Validation\ValidationException;

test('create specialty data validates with name', function () {
    $data = CreateSpecialtyData::validateAndCreate([
        'name' => 'Cardiología',
    ]);

    expect($data)->toBeInstanceOf(CreateSpecialtyData::class)
        ->and($data->name)->toBe('Cardiología');
});

test('create specialty data fails without name', function () {
    CreateSpecialtyData::validateAndCreate([]);
})->throws(ValidationException::class);

test('create specialty data accepts optional fields', function () {
    $data = CreateSpecialtyData::validateAndCreate([
        'name' => 'Cardiología',
        'description' => 'Especialidad del corazón',
        'is_active' => true,
    ]);

    expect($data->description)->toBe('Especialidad del corazón')
        ->and($data->is_active)->toBeTrue();
});

test('update specialty data validates with route parameter', function () {
    $specialty = Specialty::factory()->create();

    request()->setRouteResolver(function () use ($specialty) {
        $route = new Route('PUT', '/specialties/{specialty}', []);
        $route->bind(request());
        $route->setParameter('specialty', $specialty);

        return $route;
    });

    $data = UpdateSpecialtyData::validateAndCreate([
        'name' => $specialty->name,
        'description' => 'Updated description',
    ]);

    expect($data)->toBeInstanceOf(UpdateSpecialtyData::class)
        ->and($data->description)->toBe('Updated description');
});
