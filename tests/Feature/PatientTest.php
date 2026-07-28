<?php

declare(strict_types=1);

use App\Models\Appointment;
use App\Models\Patient;

test('patient has many appointments', function () {
    $patient = Patient::factory()->create();
    Appointment::factory()->count(3)->forPatient($patient)->create();

    expect($patient->appointments)->toHaveCount(3);
});

test('patient full name accessor', function () {
    $patient = Patient::factory()->create([
        'first_name' => 'Juan',
        'last_name' => 'Pérez',
    ]);

    expect($patient->full_name)->toBe('Juan Pérez');
});

test('patient auto generates medical record number', function () {
    $patient = Patient::factory()->create();

    expect($patient->medical_record_number)->toMatch('/^PM-\d{5}$/');
});

test('patient medical record number is sequential', function () {
    $p1 = Patient::factory()->create();
    $p2 = Patient::factory()->create();

    expect($p2->medical_record_number)->toBe('PM-00002');
});

test('patient scope search finds by name', function () {
    Patient::factory()->create(['first_name' => 'Carlos', 'last_name' => 'López']);
    Patient::factory()->create(['first_name' => 'María', 'last_name' => 'García']);

    expect(Patient::search('Carlos')->count())->toBe(1)
        ->and(Patient::search('García')->count())->toBe(1);
});

test('patient scope search finds by medical record number', function () {
    $patient = Patient::factory()->create(['medical_record_number' => 'PM-99999']);

    expect(Patient::search('99999')->count())->toBe(1);
});

test('patient scope search finds by phone', function () {
    Patient::factory()->create(['phone' => '+54 11 5555-1234']);

    expect(Patient::search('5555')->count())->toBe(1);
});

test('patient soft delete preserves record', function () {
    $patient = Patient::factory()->create();
    $id = $patient->id;

    $patient->delete();

    expect(Patient::find($id))->toBeNull()
        ->and(Patient::withTrashed()->find($id))->not->toBeNull()
        ->and(Patient::onlyTrashed()->count())->toBe(1);
});

test('patient can be restored after soft delete', function () {
    $patient = Patient::factory()->create();
    $patient->delete();

    $patient->restore();

    expect(Patient::find($patient->id))->not->toBeNull()
        ->and(Patient::onlyTrashed()->count())->toBe(0);
});
