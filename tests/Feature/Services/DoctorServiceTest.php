<?php

declare(strict_types=1);

use App\Enums\AppointmentStatus;
use App\Exceptions\AuthorizationException;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Specialty;
use App\Models\User;
use App\Services\DoctorService;

beforeEach(function () {
    $this->service = app(DoctorService::class);
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

test('create persists doctor', function () {
    $user = User::factory()->doctor()->create();
    $specialty = Specialty::factory()->create();

    $doctor = $this->service->create([
        'user_id' => $user->id,
        'specialty_id' => $specialty->id,
        'license_number' => 'MAT-12345',
        'phone' => '+54 11 5555-0000',
    ]);

    expect($doctor)->toBeInstanceOf(Doctor::class)
        ->and(Doctor::count())->toBe(1);
});

test('create throws AuthorizationException for doctor user', function () {
    $doctorUser = User::factory()->doctor()->create();
    $this->actingAs($doctorUser);
    $user = User::factory()->doctor()->create();
    $specialty = Specialty::factory()->create();

    $this->expectException(AuthorizationException::class);
    $this->service->create([
        'user_id' => $user->id,
        'specialty_id' => $specialty->id,
        'license_number' => 'MAT-99999',
    ]);
});

test('update persists changes (bug fix)', function () {
    $doctor = Doctor::factory()->create(['phone' => '+54 11 5555-0000']);

    $updated = $this->service->update($doctor, ['phone' => '+54 11 5555-9999']);

    expect($updated->phone)->toBe('+54 11 5555-9999');
});

test('update throws AuthorizationException when doctor tries to update another', function () {
    $doctorUser = User::factory()->doctor()->create();
    $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);
    $otherDoctor = Doctor::factory()->create();

    $this->actingAs($doctorUser);

    $this->expectException(AuthorizationException::class);
    $this->service->update($otherDoctor, ['phone' => '+54 11 5555-1111']);
});

test('delete cancels future appointments, deactivates user, and soft-deletes doctor', function () {
    $user = User::factory()->doctor()->create(['is_active' => true]);
    $doctor = Doctor::factory()->create(['user_id' => $user->id]);
    $futureAppointment = Appointment::factory()->forDoctor($doctor)->create([
        'appointment_date' => now()->addDays(5)->toDateString(),
        'status' => AppointmentStatus::Scheduled,
    ]);

    $this->service->delete($doctor);

    expect(Doctor::find($doctor->id))->toBeNull()
        ->and(Doctor::withTrashed()->find($doctor->id))->not->toBeNull()
        ->and($user->fresh()->is_active)->toBeFalse();

    $futureAppointment->refresh();
    expect($futureAppointment->status)->toBe(AppointmentStatus::Cancelled);
});

test('delete throws AuthorizationException for doctor user', function () {
    $doctorUser = User::factory()->doctor()->create();
    $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);
    $this->actingAs($doctorUser);

    $this->expectException(AuthorizationException::class);
    $this->service->delete($doctor);
});
