<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\Clinic\CreatePatientData;
use App\Data\Clinic\UpdatePatientData;
use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Exceptions\AuthorizationException;
use App\Models\Patient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PatientService
{
    public function index(): LengthAwarePaginator
    {
        if (auth()->user()?->role === UserRole::Doctor) {
            $doctorId = auth()->user()->doctor?->id;

            return Patient::whereHas('appointments', fn ($q) => $q->where('doctor_id', $doctorId))
                ->latest()
                ->paginate(15);
        }

        return Patient::latest()->paginate(15);
    }

    public function create(CreatePatientData $data): Patient
    {
        if (auth()->user()?->role === UserRole::Doctor) {
            throw new AuthorizationException('Los médicos no pueden crear pacientes.');
        }

        return Patient::create(array_filter($data->toArray(), fn ($v) => $v !== null));
    }

    public function show(Patient $patient): Patient
    {
        $patient->load([
            'appointments' => function ($query) {
                $query->with(['doctor.user', 'doctor.specialty'])
                    ->latest('appointment_date')
                    ->latest('appointment_time')
                    ->limit(20);
            },
        ]);

        if (auth()->user()?->role === UserRole::Doctor) {
            $doctorId = auth()->user()->doctor?->id;
            $hasAppointment = $patient->appointments()
                ->where('doctor_id', $doctorId)
                ->exists();

            if (! $hasAppointment) {
                throw new AuthorizationException('No tiene permiso para ver este paciente.');
            }
        }

        return $patient;
    }

    public function update(Patient $patient, UpdatePatientData $data): Patient
    {
        if (auth()->user()?->role === UserRole::Doctor) {
            throw new AuthorizationException('Los médicos no pueden modificar pacientes.');
        }

        $patient->update(array_filter($data->toArray(), fn ($v) => $v !== null));

        return $patient->fresh();
    }

    public function delete(Patient $patient): void
    {
        if (auth()->user()?->role === UserRole::Doctor) {
            throw new AuthorizationException('Los médicos no pueden eliminar pacientes.');
        }

        DB::transaction(function () use ($patient) {
            $patient->appointments()
                ->whereIn('status', [AppointmentStatus::Scheduled, AppointmentStatus::Confirmed])
                ->where('appointment_date', '>=', now()->toDateString())
                ->update([
                    'status' => AppointmentStatus::Cancelled,
                    'cancellation_reason' => 'Paciente dado de baja.',
                    'cancelled_at' => now(),
                    'cancelled_by' => auth()->id(),
                ]);

            $patient->delete();
        });
    }
}
