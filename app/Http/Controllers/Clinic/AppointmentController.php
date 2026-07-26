<?php

namespace App\Http\Controllers\Clinic;

use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Clinic\StoreAppointmentNoteRequest;
use App\Http\Requests\Clinic\StoreAppointmentRequest;
use App\Http\Requests\Clinic\UpdateAppointmentRequest;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AppointmentController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Appointment::with(['doctor.user', 'doctor.specialty', 'patient', 'createdBy']);

        // Doctor sees only own appointments
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

        $appointments = $query->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->paginate(20);

        return Inertia::render('clinic/appointments/Index', [
            'appointments' => $appointments,
            'filters' => $request->only(['date', 'doctor_id', 'status']),
            'doctors' => Doctor::with('user')->active()
                ->join('users', 'doctors.user_id', '=', 'users.id')
                ->orderBy('users.name')
                ->select('doctors.*')
                ->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('clinic/appointments/Create', [
            'doctors' => Doctor::with(['user', 'specialty', 'schedules'])->active()
                ->join('users', 'doctors.user_id', '=', 'users.id')
                ->orderBy('users.name')
                ->select('doctors.*')
                ->get(),
            'patients' => Patient::orderBy('last_name')->orderBy('first_name')->get(),
        ]);
    }

    public function store(StoreAppointmentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();

        if (! isset($data['duration_minutes'])) {
            $dayOfWeek = Carbon::parse($data['appointment_date'])->dayOfWeek;
            $schedule = DoctorSchedule::where('doctor_id', $data['doctor_id'])
                ->where('day_of_week', $dayOfWeek)
                ->where('start_time', '<=', $data['appointment_time'])
                ->where('end_time', '>', $data['appointment_time'])
                ->active()
                ->first();

            $data['duration_minutes'] = $schedule?->slot_duration_minutes ?? 30;
        }

        Appointment::create($data);

        return to_route('clinic.appointments.index')
            ->with('toast', ['type' => 'success', 'message' => 'Turno creado correctamente.']);
    }

    public function show(Appointment $appointment): Response
    {
        $appointment->load(['doctor.user', 'doctor.specialty', 'patient', 'createdBy', 'notes.author']);

        // Doctor can only view own appointments
        if (auth()->user()?->role === UserRole::Doctor) {
            $doctorId = auth()->user()->doctor?->id;
            if ($appointment->doctor_id !== $doctorId) {
                abort(403);
            }
        }

        return Inertia::render('clinic/appointments/Show', [
            'appointment' => $appointment,
        ]);
    }

    public function edit(Appointment $appointment): Response
    {
        return Inertia::render('clinic/appointments/Edit', [
            'appointment' => $appointment->load(['doctor.user', 'patient']),
            'doctors' => Doctor::with(['user', 'schedules'])->active()
                ->join('users', 'doctors.user_id', '=', 'users.id')
                ->orderBy('users.name')
                ->select('doctors.*')
                ->get(),
            'patients' => Patient::orderBy('last_name')->get(),
        ]);
    }

    public function update(UpdateAppointmentRequest $request, Appointment $appointment): RedirectResponse
    {
        $data = $request->validated();

        if (isset($data['status'])) {
            $status = AppointmentStatus::from($data['status']);

            match ($status) {
                AppointmentStatus::Confirmed => $appointment->confirm(auth()->id()),
                AppointmentStatus::Completed => $appointment->complete(),
                AppointmentStatus::Cancelled => $appointment->cancel($data['cancellation_reason'] ?? '', auth()->id()),
                AppointmentStatus::NoShow => $appointment->markNoShow(),
                default => $appointment->update($data),
            };

            return to_route('clinic.appointments.index')
                ->with('toast', ['type' => 'success', 'message' => 'Turno actualizado correctamente.']);
        }

        $appointment->update($data);

        return to_route('clinic.appointments.index')
            ->with('toast', ['type' => 'success', 'message' => 'Turno actualizado correctamente.']);
    }

    public function addNote(Appointment $appointment, StoreAppointmentNoteRequest $request): RedirectResponse
    {
        $appointment->notes()->create([
            'author_id' => auth()->id(),
            'content' => $request->validated('content'),
        ]);

        return back()
            ->with('toast', ['type' => 'success', 'message' => 'Nota agregada correctamente.']);
    }

    public function destroy(Appointment $appointment): RedirectResponse
    {
        $appointment->delete();

        return to_route('clinic.appointments.index')
            ->with('toast', ['type' => 'success', 'message' => 'Turno eliminado correctamente.']);
    }
}
