<?php

declare(strict_types=1);

use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

test('store creates patient and redirects with success toast', function () {
    $this->actingAs($this->admin);

    $response = $this->post(route('clinic.patients.store'), [
        'first_name' => 'Carlos',
        'last_name' => 'López',
        'phone' => '+54 11 5555-1234',
    ]);

    $response->assertRedirect(route('clinic.patients.index'));
    $response->assertSessionHas('toast.type', 'success');
    expect(Patient::count())->toBe(1);
});

test('store catches exception and returns error toast', function () {
    $this->actingAs($this->admin);

    Patient::creating(function () {
        throw new Exception('Simulated DB error');
    });

    $response = $this->post(route('clinic.patients.store'), [
        'first_name' => 'Carlos',
        'last_name' => 'López',
        'phone' => '+54 11 5555-1234',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('toast.type', 'error');
    $response->assertSessionHas('toast.message', 'Ocurrió un error inesperado. Intente nuevamente.');
    expect(Patient::count())->toBe(0);
});

test('store logs exception with user context', function () {
    $this->actingAs($this->admin);

    Log::spy();

    Patient::creating(function () {
        throw new Exception('Simulated DB error');
    });

    $this->post(route('clinic.patients.store'), [
        'first_name' => 'Carlos',
        'last_name' => 'López',
        'phone' => '+54 11 5555-1234',
    ]);

    Log::shouldHaveReceived('error')
        ->once()
        ->withArgs(fn ($message, $context) => str_contains($message, 'patient.store')
            && ($context['user_id'] ?? null) === $this->admin->id
            && ($context['action'] ?? null) === 'patient.store');
});

test('update persists changes and redirects with success toast', function () {
    $this->actingAs($this->admin);

    $patient = Patient::factory()->create([
        'first_name' => 'Juan',
        'last_name' => 'Pérez',
    ]);

    $response = $this->put(route('clinic.patients.update', $patient), [
        'first_name' => 'Juan Carlos',
        'last_name' => 'Pérez García',
        'phone' => '+54 11 5555-5678',
    ]);

    $response->assertRedirect(route('clinic.patients.index'));
    $response->assertSessionHas('toast.type', 'success');

    $patient->refresh();
    expect($patient->first_name)->toBe('Juan Carlos');
    expect($patient->last_name)->toBe('Pérez García');
    expect($patient->phone)->toBe('+54 11 5555-5678');
});

test('update returns 403 for doctor user', function () {
    $doctorUser = User::factory()->doctor()->create();
    $this->actingAs($doctorUser);

    $patient = Patient::factory()->create();

    $response = $this->put(route('clinic.patients.update', $patient), [
        'first_name' => 'Hacked',
        'last_name' => 'Name',
        'phone' => '+54 11 5555-0000',
    ]);

    $response->assertForbidden();
});

test('destroy deletes patient and cancels future appointments', function () {
    $this->actingAs($this->admin);

    $patient = Patient::factory()->create();

    $response = $this->delete(route('clinic.patients.destroy', $patient));

    $response->assertRedirect(route('clinic.patients.index'));
    $response->assertSessionHas('toast.type', 'success');
    expect(Patient::find($patient->id))->toBeNull();
    expect(Patient::withTrashed()->find($patient->id))->not->toBeNull();
});

test('destroy returns 403 for doctor user', function () {
    $doctorUser = User::factory()->doctor()->create();
    $this->actingAs($doctorUser);

    $patient = Patient::factory()->create();

    $response = $this->delete(route('clinic.patients.destroy', $patient));

    $response->assertForbidden();
});
