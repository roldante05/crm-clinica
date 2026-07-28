<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Appointment;
use App\Models\AppointmentNote;
use App\Models\Doctor;
use App\Models\User;

test('user can have doctor profile', function () {
    $user = User::factory()->doctor()->create();
    Doctor::factory()->create(['user_id' => $user->id]);

    expect($user->doctor)->toBeInstanceOf(Doctor::class);
});

test('user created appointments relationship', function () {
    $secretary = User::factory()->secretary()->create();
    Appointment::factory()->count(3)->createdBy($secretary)->create();

    expect($secretary->createdAppointments)->toHaveCount(3);
});

test('user confirmed appointments relationship', function () {
    $user = User::factory()->create();
    Appointment::factory()->count(2)->create(['confirmed_by' => $user->id]);

    expect($user->confirmedAppointments)->toHaveCount(2);
});

test('user cancelled appointments relationship', function () {
    $user = User::factory()->create();
    Appointment::factory()->count(2)->create(['cancelled_by' => $user->id]);

    expect($user->cancelledAppointments)->toHaveCount(2);
});

test('user authored notes relationship', function () {
    $doctor = User::factory()->doctor()->create();
    AppointmentNote::factory()->count(4)->authoredBy($doctor)->create();

    expect($doctor->authoredNotes)->toHaveCount(4);
});

test('user scope active filters inactive', function () {
    User::factory()->count(3)->create();
    User::factory()->count(2)->inactive()->create();

    expect(User::active()->count())->toBe(3);
});

test('user scope doctors filters by role', function () {
    User::factory()->doctor()->count(3)->create();
    User::factory()->secretary()->count(2)->create();

    expect(User::doctors()->count())->toBe(3);
});

test('user scope secretaries filters by role', function () {
    User::factory()->doctor()->count(2)->create();
    User::factory()->secretary()->count(4)->create();

    expect(User::secretaries()->count())->toBe(4);
});

test('user scope admins filters by role', function () {
    User::factory()->admin()->count(2)->create();
    User::factory()->doctor()->count(3)->create();

    expect(User::admins()->count())->toBe(2);
});

test('user role casts as enum', function () {
    $user = User::factory()->admin()->create();

    expect($user->role)->toBeInstanceOf(UserRole::class)
        ->and($user->role)->toBe(UserRole::Admin);
});
