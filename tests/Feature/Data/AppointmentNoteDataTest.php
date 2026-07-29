<?php

declare(strict_types=1);

use App\Data\Clinic\CreateAppointmentNoteData;
use Illuminate\Validation\ValidationException;

test('create appointment note data validates with content', function () {
    $data = CreateAppointmentNoteData::validateAndCreate([
        'content' => 'Paciente presenta mejoría notable.',
    ]);

    expect($data)->toBeInstanceOf(CreateAppointmentNoteData::class)
        ->and($data->content)->toBe('Paciente presenta mejoría notable.');
});

test('create appointment note data fails without content', function () {
    CreateAppointmentNoteData::validateAndCreate([]);
})->throws(ValidationException::class);

test('create appointment note data fails with empty content', function () {
    CreateAppointmentNoteData::validateAndCreate([
        'content' => '',
    ]);
})->throws(ValidationException::class);
