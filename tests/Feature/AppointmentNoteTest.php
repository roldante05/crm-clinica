<?php

declare(strict_types=1);

use App\Models\Appointment;
use App\Models\AppointmentNote;
use App\Models\User;

test('note belongs to appointment', function () {
    $note = AppointmentNote::factory()->create();

    expect($note->appointment)->toBeInstanceOf(Appointment::class);
});

test('note belongs to author user', function () {
    $note = AppointmentNote::factory()->create();

    expect($note->author)->toBeInstanceOf(User::class)
        ->and($note->author->id)->toBe($note->author_id);
});

test('note can be created with content', function () {
    $note = AppointmentNote::factory()->create([
        'content' => 'Paciente presenta dolor torácico leve. Se indica reposo.',
    ]);

    expect($note->content)->toBe('Paciente presenta dolor torácico leve. Se indica reposo.');
});

test('appointment cascade deletes notes', function () {
    $appointment = Appointment::factory()->create();
    AppointmentNote::factory()->count(3)->forAppointment($appointment)->create();

    expect($appointment->notes)->toHaveCount(3);

    $appointment->delete();

    expect(AppointmentNote::where('appointment_id', $appointment->id)->count())->toBe(0);
});
