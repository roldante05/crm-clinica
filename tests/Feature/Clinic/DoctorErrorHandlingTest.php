<?php

declare(strict_types=1);

use App\Models\Doctor;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

test('store creates doctor and redirects with success toast', function () {
    $this->actingAs($this->admin);

    $user = User::factory()->doctor()->create();
    $specialty = Specialty::factory()->create();

    $response = $this->post(route('clinic.doctors.store'), [
        'user_id' => $user->id,
        'specialty_id' => $specialty->id,
        'license_number' => 'MAT-99999',
        'phone' => '+54 11 5555-0000',
    ]);

    $response->assertRedirect(route('clinic.doctors.index'));
    $response->assertSessionHas('toast.type', 'success');
    expect(Doctor::count())->toBe(1);
});

test('store catches exception and returns error toast', function () {
    $this->actingAs($this->admin);

    Doctor::creating(function () {
        throw new Exception('Simulated DB error');
    });

    $user = User::factory()->doctor()->create();
    $specialty = Specialty::factory()->create();

    $response = $this->post(route('clinic.doctors.store'), [
        'user_id' => $user->id,
        'specialty_id' => $specialty->id,
        'license_number' => 'MAT-99999',
        'phone' => '+54 11 5555-0000',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('toast.type', 'error');
    $response->assertSessionHas('toast.message', 'Ocurrió un error inesperado. Intente nuevamente.');
    expect(Doctor::count())->toBe(0);
});

test('store logs exception with user context', function () {
    $this->actingAs($this->admin);

    Log::spy();

    Doctor::creating(function () {
        throw new Exception('Simulated DB error');
    });

    $user = User::factory()->doctor()->create();
    $specialty = Specialty::factory()->create();

    $this->post(route('clinic.doctors.store'), [
        'user_id' => $user->id,
        'specialty_id' => $specialty->id,
        'license_number' => 'MAT-99999',
        'phone' => '+54 11 5555-0000',
    ]);

    Log::shouldHaveReceived('error')
        ->once()
        ->withArgs(fn ($message, $context) => str_contains($message, 'doctor.store')
            && ($context['user_id'] ?? null) === $this->admin->id
            && ($context['action'] ?? null) === 'doctor.store');
});

test('store returns 403 for doctor user', function () {
    $doctorUser = User::factory()->doctor()->create();
    $this->actingAs($doctorUser);

    $user = User::factory()->doctor()->create();
    $specialty = Specialty::factory()->create();

    $response = $this->post(route('clinic.doctors.store'), [
        'user_id' => $user->id,
        'specialty_id' => $specialty->id,
        'license_number' => 'MAT-99998',
        'phone' => '+54 11 5555-1111',
    ]);

    $response->assertForbidden();
});

test('update persists changes and redirects with success toast', function () {
    $this->actingAs($this->admin);

    $doctor = Doctor::factory()->create(['phone' => '+54 11 5555-0000']);

    $response = $this->put(route('clinic.doctors.update', $doctor), [
        'user_id' => $doctor->user_id,
        'specialty_id' => $doctor->specialty_id,
        'license_number' => $doctor->license_number,
        'phone' => '+54 11 5555-9999',
    ]);

    $response->assertRedirect(route('clinic.doctors.index'));
    $response->assertSessionHas('toast.type', 'success');

    $doctor->refresh();
    expect($doctor->phone)->toBe('+54 11 5555-9999');
});

test('update returns 403 when doctor tries to update another doctor', function () {
    $doctorUser = User::factory()->doctor()->create();
    $this->actingAs($doctorUser);

    $doctor = Doctor::factory()->create();

    $response = $this->put(route('clinic.doctors.update', $doctor), [
        'user_id' => $doctor->user_id,
        'specialty_id' => $doctor->specialty_id,
        'license_number' => $doctor->license_number,
    ]);

    $response->assertForbidden();
});

test('destroy deletes doctor and redirects with success toast', function () {
    $this->actingAs($this->admin);

    $doctor = Doctor::factory()->create();

    $response = $this->delete(route('clinic.doctors.destroy', $doctor));

    $response->assertRedirect(route('clinic.doctors.index'));
    $response->assertSessionHas('toast.type', 'success');
    expect(Doctor::find($doctor->id))->toBeNull();
});

test('destroy returns 403 for doctor user', function () {
    $doctorUser = User::factory()->doctor()->create();
    $this->actingAs($doctorUser);

    $doctor = Doctor::factory()->create();

    $response = $this->delete(route('clinic.doctors.destroy', $doctor));

    $response->assertForbidden();
});
