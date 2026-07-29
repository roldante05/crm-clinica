<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\Clinic\CreateAppointmentData;
use App\Data\Clinic\CreateAppointmentNoteData;
use App\Data\Clinic\UpdateAppointmentData;
use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Exceptions\AuthorizationException;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AppointmentService
{
    public function index(Request $request): LengthAwarePaginator
    {
        $query = Appointment::with(['doctor.user', 'doctor.specialty', 'patient', 'createdBy']);

        if (auth()->user()?->role === UserRole::Doctor) {
            $doctorId = auth()->user()->doctor?->id;
            $query->where('doctor_id', $doctorId);
        }

        if ($request->filled('date')) {
            $query->where('appointment_date', $request->date);
        } elseif (! $request->filled('doctor_id') && ! $request->filled('status')) {
            $query->where('appointment_date', '>=', now()->toDateString());
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->paginate(20);
    }

    public function getDoctorsList(): Collection
    {
        return Doctor::with('user')->active()
            ->join('users', 'doctors.user_id', '=', 'users.id')
            ->orderBy('users.name')
            ->select('doctors.*')
            ->get();
    }

    public function getDoctorsWithSchedules(): Collection
    {
        return Doctor::with(['user', 'specialty', 'schedules'])->active()
            ->join('users', 'doctors.user_id', '=', 'users.id')
            ->orderBy('users.name')
            ->select('doctors.*')
            ->get();
    }

    public function getDoctorsWithSchedulesForEdit(): Collection
    {
        return Doctor::with(['user', 'schedules'])->active()
            ->join('users', 'doctors.user_id', '=', 'users.id')
            ->orderBy('users.name')
            ->select('doctors.*')
            ->get();
    }

    public function getPatients(): Collection
    {
        return Patient::orderBy('last_name')->orderBy('first_name')->get();
    }

    public function getPatientsForEdit(): Collection
    {
        return Patient::orderBy('last_name')->get();
    }

    public function create(CreateAppointmentData $data, int $userId): Appointment
    {
        $appointmentData = array_filter($data->toArray(), fn ($v) => $v !== null);
        $appointmentData['created_by'] = $userId;

        if (! isset($appointmentData['duration_minutes'])) {
            $dayOfWeek = Carbon::parse($appointmentData['appointment_date'])->dayOfWeek;
            $schedule = DoctorSchedule::where('doctor_id', $appointmentData['doctor_id'])
                ->where('day_of_week', $dayOfWeek)
                ->where('start_time', '<=', $appointmentData['appointment_time'])
                ->where('end_time', '>', $appointmentData['appointment_time'])
                ->active()
                ->first();

            $appointmentData['duration_minutes'] = $schedule?->slot_duration_minutes ?? 30;
        }

        return Appointment::create($appointmentData);
    }

    public function update(Appointment $appointment, UpdateAppointmentData $data): Appointment
    {
        $appointmentData = array_filter($data->toArray(), fn ($v) => $v !== null);

        if (isset($appointmentData['status'])) {
            $status = AppointmentStatus::from($appointmentData['status']);

            match ($status) {
                AppointmentStatus::Confirmed => $appointment->confirm((int) auth()->id()),
                AppointmentStatus::Completed => $appointment->complete(),
                AppointmentStatus::Cancelled => $appointment->cancel($appointmentData['cancellation_reason'] ?? '', (int) auth()->id()),
                AppointmentStatus::NoShow => $appointment->markNoShow(),
                default => $appointment->update($appointmentData),
            };
        } else {
            $appointment->update($appointmentData);
        }

        return $appointment->fresh();
    }

    public function addNote(Appointment $appointment, CreateAppointmentNoteData $data, int $authorId): void
    {
        $appointment->notes()->create([
            'author_id' => $authorId,
            'content' => $data->content,
        ]);
    }

    public function delete(Appointment $appointment): void
    {
        $appointment->delete();
    }

    public function show(Appointment $appointment): Appointment
    {
        if (auth()->user()?->role === UserRole::Doctor) {
            $doctorId = auth()->user()->doctor?->id;
            if ($appointment->doctor_id !== $doctorId) {
                throw new AuthorizationException('No tiene permiso para ver este turno.');
            }
        }

        return $appointment->load(['doctor.user', 'doctor.specialty', 'patient', 'createdBy', 'notes.author']);
    }
}
