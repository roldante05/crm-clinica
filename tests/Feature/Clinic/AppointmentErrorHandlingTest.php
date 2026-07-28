<?php

declare(strict_types=1);

use App\Enums\DayOfWeek;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

test('store creates appointment and redirects with success toast', function () {
    $this->actingAs($this->admin);

    $doctor = Doctor::factory()->create();
    DoctorSchedule::factory()->forDoctor($doctor)->morning()->forDay(DayOfWeek::from(now()->dayOfWeek))->create();
    $patient = Patient::factory()->create();

    $response = $this->post(route('clinic.appointments.store'), [
        'doctor_id' => $doctor->id,
        'patient_id' => $patient->id,
        'appointment_date' => now()->toDateString(),
        'appointment_time' => '09:00',
        'reason' => 'Control general',
    ]);

    $response->assertRedirect(route('clinic.appointments.index'));
    $response->assertSessionHas('toast.type', 'success');
    expect(Appointment::count())->toBe(1);
});

test('store catches exception and returns error toast', function () {
    $this->actingAs($this->admin);

    Appointment::creating(function () {
        throw new Exception('Simulated DB error');
    });

    $doctor = Doctor::factory()->create();
    DoctorSchedule::factory()->forDoctor($doctor)->morning()->forDay(DayOfWeek::from(now()->dayOfWeek))->create();
    $patient = Patient::factory()->create();

    $response = $this->post(route('clinic.appointments.store'), [
        'doctor_id' => $doctor->id,
        'patient_id' => $patient->id,
        'appointment_date' => now()->toDateString(),
        'appointment_time' => '09:00',
        'reason' => 'Control general',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('toast.type', 'error');
    $response->assertSessionHas('toast.message', 'Ocurrió un error inesperado. Intente nuevamente.');
    expect(Appointment::count())->toBe(0);
});

test('store logs exception with user context', function () {
    $this->actingAs($this->admin);

    Log::spy();

    Appointment::creating(function () {
        throw new Exception('Simulated DB error');
    });

    $doctor = Doctor::factory()->create();
    DoctorSchedule::factory()->forDoctor($doctor)->morning()->forDay(DayOfWeek::from(now()->dayOfWeek))->create();
    $patient = Patient::factory()->create();

    $this->post(route('clinic.appointments.store'), [
        'doctor_id' => $doctor->id,
        'patient_id' => $patient->id,
        'appointment_date' => now()->toDateString(),
        'appointment_time' => '09:00',
        'reason' => 'Control general',
    ]);

    Log::shouldHaveReceived('error')
        ->once()
        ->withArgs(fn ($message, $context) => str_contains($message, 'appointment.store')
            && ($context['user_id'] ?? null) === $this->admin->id
            && ($context['action'] ?? null) === 'appointment.store');
});

test('update updates appointment and redirects with success toast', function () {
    $this->actingAs($this->admin);

    $appointment = Appointment::factory()->create();

    $response = $this->put(route('clinic.appointments.update', $appointment), [
        'doctor_id' => $appointment->doctor_id,
        'patient_id' => $appointment->patient_id,
        'appointment_date' => $appointment->appointment_date,
        'appointment_time' => $appointment->appointment_time,
        'reason' => 'Motivo actualizado',
    ]);

    $response->assertRedirect(route('clinic.appointments.index'));
    $response->assertSessionHas('toast.type', 'success');
    expect($appointment->fresh()->reason)->toBe('Motivo actualizado');
});

test('destroy deletes appointment and redirects with success toast', function () {
    $this->actingAs($this->admin);

    $appointment = Appointment::factory()->create();

    $response = $this->delete(route('clinic.appointments.destroy', $appointment));

    $response->assertRedirect(route('clinic.appointments.index'));
    $response->assertSessionHas('toast.type', 'success');
    expect(Appointment::find($appointment->id))->toBeNull();
});

test('addNote creates note and redirects back with success toast', function () {
    $this->actingAs($this->admin);

    $appointment = Appointment::factory()->create();

    $response = $this->post(route('clinic.appointments.notes.store', $appointment), [
        'content' => 'Paciente presenta mejoría.',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('toast.type', 'success');
    expect($appointment->notes()->count())->toBe(1);
});
