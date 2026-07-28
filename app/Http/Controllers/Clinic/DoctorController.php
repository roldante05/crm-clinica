<?php

namespace App\Http\Controllers\Clinic;

use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Clinic\StoreDoctorRequest;
use App\Http\Requests\Clinic\UpdateDoctorRequest;
use App\Models\Doctor;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class DoctorController extends Controller
{
    public function index(): Response
    {
        $doctors = Doctor::with(['user', 'specialty'])
            ->latest()
            ->paginate(15);

        return Inertia::render('clinic/doctors/Index', [
            'doctors' => $doctors,
        ]);
    }

    public function create(): Response
    {
        // Prevent doctors from accessing creation routes
        if (auth()->user()?->role === UserRole::Doctor) {
            abort(403);
        }

        return Inertia::render('clinic/doctors/Create', [
            'specialties' => Specialty::active()->orderBy('name')->get(),
            'users' => User::where('role', UserRole::Doctor)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(StoreDoctorRequest $request): RedirectResponse
    {
        Doctor::create($request->validated());

        return to_route('clinic.doctors.index')
            ->with('toast', ['type' => 'success', 'message' => 'Médico creado correctamente.']);
    }

    public function edit(Doctor $doctor): Response
    {
        // Doctors can only edit their own profile
        if (auth()->user()?->role === UserRole::Doctor) {
            $myDoctorId = auth()->user()->doctor?->id;
            if ($doctor->id !== $myDoctorId) {
                abort(403);
            }
        }

        return Inertia::render('clinic/doctors/Edit', [
            'doctor' => $doctor->load(['user', 'specialty']),
            'specialties' => Specialty::active()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateDoctorRequest $request, Doctor $doctor): RedirectResponse
    {
        // Prevent doctors from updating other doctors
        if (auth()->user()?->role === UserRole::Doctor && $doctor->id !== auth()->user()->doctor?->id) {
            abort(403);
        }

        return to_route('clinic.doctors.index')
            ->with('toast', ['type' => 'success', 'message' => 'Médico actualizado correctamente.']);
    }

    public function destroy(Doctor $doctor): RedirectResponse
    {
        if (auth()->user()?->role === UserRole::Doctor) {
            abort(403);
        }

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

        return to_route('clinic.doctors.index')
            ->with('toast', ['type' => 'success', 'message' => 'Médico eliminado correctamente.']);
    }
}
