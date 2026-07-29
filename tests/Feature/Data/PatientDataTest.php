<?php

declare(strict_types=1);

use App\Data\Clinic\CreatePatientData;
use App\Data\Clinic\UpdatePatientData;
use App\Models\Patient;
use Illuminate\Routing\Route;
use Illuminate\Validation\ValidationException;

test('create patient data validates with all required fields', function () {
    $data = CreatePatientData::validateAndCreate([
        'first_name' => 'Juan',
        'last_name' => 'Pérez',
        'phone' => '+54 11 5555-1234',
    ]);

    expect($data)->toBeInstanceOf(CreatePatientData::class)
        ->and($data->first_name)->toBe('Juan')
        ->and($data->last_name)->toBe('Pérez')
        ->and($data->phone)->toBe('+54 11 5555-1234');
});

test('create patient data fails when first_name is missing', function () {
    CreatePatientData::validateAndCreate([
        'last_name' => 'Pérez',
        'phone' => '+54 11 5555-1234',
    ]);
})->throws(ValidationException::class);

test('create patient data fails with invalid gender', function () {
    CreatePatientData::validateAndCreate([
        'first_name' => 'Juan',
        'last_name' => 'Pérez',
        'phone' => '+54 11 5555-1234',
        'gender' => 'invalid_value',
    ]);
})->throws(ValidationException::class);

test('create patient data accepts optional fields', function () {
    $data = CreatePatientData::validateAndCreate([
        'first_name' => 'Juan',
        'last_name' => 'Pérez',
        'phone' => '+54 11 5555-1234',
        'email' => 'juan@example.com',
        'secondary_phone' => '+54 11 5555-5678',
        'document_type' => 'DNI',
        'document_number' => '12345678',
        'gender' => 'male',
        'address' => 'Av. Siempre Viva 123',
        'city' => 'Buenos Aires',
        'notes' => 'Paciente de prueba',
    ]);

    expect($data->email)->toBe('juan@example.com')
        ->and($data->gender)->toBe('male')
        ->and($data->address)->toBe('Av. Siempre Viva 123');
});

test('create patient data fails with future date_of_birth', function () {
    CreatePatientData::validateAndCreate([
        'first_name' => 'Juan',
        'last_name' => 'Pérez',
        'phone' => '+54 11 5555-1234',
        'date_of_birth' => now()->addYear()->toDateString(),
    ]);
})->throws(ValidationException::class);

test('update patient data validates with route parameter reference', function () {
    $patient = Patient::factory()->create();

    request()->setRouteResolver(function () use ($patient) {
        $route = new Route('PUT', '/patients/{patient}', []);
        $route->bind(request());
        $route->setParameter('patient', $patient);

        return $route;
    });

    $data = UpdatePatientData::validateAndCreate([
        'first_name' => 'Juan',
        'last_name' => 'Pérez',
        'phone' => '+54 11 5555-1234',
    ]);

    expect($data)->toBeInstanceOf(UpdatePatientData::class)
        ->and($data->first_name)->toBe('Juan');
});

test('update patient data allows same email when updating own record', function () {
    $patient = Patient::factory()->create(['email' => 'same@example.com']);

    request()->setRouteResolver(function () use ($patient) {
        $route = new Route('PUT', '/patients/{patient}', []);
        $route->bind(request());
        $route->setParameter('patient', $patient);

        return $route;
    });

    $data = UpdatePatientData::validateAndCreate([
        'first_name' => $patient->first_name,
        'last_name' => $patient->last_name,
        'phone' => $patient->phone,
        'email' => 'same@example.com',
    ]);

    expect($data->email)->toBe('same@example.com');
});
