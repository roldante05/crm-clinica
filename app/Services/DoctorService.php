<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Exceptions\AuthorizationException;
use App\Models\Doctor;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DoctorService
{
    public function index(): LengthAwarePaginator
    {
        return Doctor::with(['user', 'specialty'])
            ->latest()
            ->paginate(15);
    }

    public function getSpecialties(): Collection
    {
        return Specialty::active()->orderBy('name')->get();
    }

    public function getDoctorsUsers(): Collection
    {
        return User::where('role', UserRole::Doctor)
            ->orderBy('name')
            ->get();
    }

    public function create(array $data): Doctor
    {
        if (auth()->user()?->role === UserRole::Doctor) {
            throw new AuthorizationException('Los médicos no pueden crear otros médicos.');
        }

        return Doctor::create($data);
    }

    public function show(Doctor $doctor): Doctor
    {
        if (auth()->user()?->role === UserRole::Doctor) {
            $myDoctorId = auth()->user()->doctor?->id;
            if ($doctor->id !== $myDoctorId) {
                throw new AuthorizationException('No tiene permiso para editar este médico.');
            }
        }

        return $doctor->load(['user', 'specialty']);
    }

    public function update(Doctor $doctor, array $data): Doctor
    {
        if (auth()->user()?->role === UserRole::Doctor && $doctor->id !== auth()->user()->doctor?->id) {
            throw new AuthorizationException('No tiene permiso para modificar este médico.');
        }

        $doctor->update($data);

        return $doctor->fresh();
    }

    public function delete(Doctor $doctor): void
    {
        if (auth()->user()?->role === UserRole::Doctor) {
            throw new AuthorizationException('Los médicos no pueden eliminar médicos.');
        }

        DB::transaction(function () use ($doctor) {
            $doctor->appointments()
                ->whereIn('status', [AppointmentStatus::Scheduled, AppointmentStatus::Confirmed])
                ->where('appointment_date', '>=', now()->toDateString())
                ->update([
                    'status' => AppointmentStatus::Cancelled,
                    'cancellation_reason' => 'Médico dado de baja.',
                    'cancelled_at' => now(),
                    'cancelled_by' => auth()->id(),
                ]);

            $doctor->user()->update(['is_active' => false]);

            $doctor->delete();
        });
    }
}
