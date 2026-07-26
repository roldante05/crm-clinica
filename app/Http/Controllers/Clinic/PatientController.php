<?php

namespace App\Http\Controllers\Clinic;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Clinic\StorePatientRequest;
use App\Http\Requests\Clinic\UpdatePatientRequest;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PatientController extends Controller
{
    public function index(): Response
    {
        // Doctors can only see patients they have appointments with
        if (auth()->user()?->role === UserRole::Doctor) {
            $doctorId = auth()->user()->doctor?->id;
            $patients = Patient::whereHas('appointments', fn ($q) => $q->where('doctor_id', $doctorId))
                ->latest()
                ->paginate(15);
        } else {
            $patients = Patient::latest()->paginate(15);
        }

        return Inertia::render('clinic/patients/Index', [
            'patients' => $patients,
        ]);
    }

    public function create(): Response
    {
        // Prevent doctors from creating patients
        if (auth()->user()?->role === UserRole::Doctor) {
            abort(403);
        }

        return Inertia::render('clinic/patients/Create');
    }

    public function store(StorePatientRequest $request): RedirectResponse
    {
        Patient::create($request->validated());

        return to_route('clinic.patients.index')
            ->with('toast', ['type' => 'success', 'message' => 'Paciente creado correctamente.']);
    }

    public function show(Patient $patient): Response
    {
        $patient->load(['appointments' => function ($query) {
            $query->with(['doctor.user', 'doctor.specialty'])
                ->latest('appointment_date')
                ->latest('appointment_time')
                ->limit(20);
        }]);

        // Doctors can only view patients they have appointments with
        if (auth()->user()?->role === UserRole::Doctor) {
            $doctorId = auth()->user()->doctor?->id;
            $hasAppointment = $patient->appointments()
                ->where('doctor_id', $doctorId)
                ->exists();
            if (! $hasAppointment) {
                abort(403);
            }
        }

        return Inertia::render('clinic/patients/Show', [
            'patient' => $patient,
        ]);
    }

    public function edit(Patient $patient): Response
    {
        return Inertia::render('clinic/patients/Edit', [
            'patient' => $patient,
        ]);
    }

    public function update(UpdatePatientRequest $request, Patient $patient): RedirectResponse
    {
        // Prevent doctors from updating patients
        if (auth()->user()?->role === UserRole::Doctor) {
            abort(403);
        }

        return to_route('clinic.patients.index')
            ->with('toast', ['type' => 'success', 'message' => 'Paciente actualizado correctamente.']);
    }

    public function destroy(Patient $patient): RedirectResponse
    {
        // Prevent doctors from deleting patients
        if (auth()->user()?->role === UserRole::Doctor) {
            abort(403);
        }

        return to_route('clinic.patients.index')
            ->with('toast', ['type' => 'success', 'message' => 'Paciente eliminado correctamente.']);
    }
}
