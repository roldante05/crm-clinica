<?php

declare(strict_types=1);

namespace App\Http\Controllers\Clinic;

use App\Data\Clinic\CreatePatientData;
use App\Data\Clinic\UpdatePatientData;
use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Services\PatientService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PatientController extends Controller
{
    public function __construct(
        protected PatientService $patientService,
    ) {}

    public function index(): Response
    {
        $patients = $this->patientService->index();

        return Inertia::render('clinic/patients/Index', [
            'patients' => $patients,
        ]);
    }

    public function create(): Response
    {
        return $this->handleExceptions(function () {
            return Inertia::render('clinic/patients/Create');
        }, 'patient.create');
    }

    public function store(CreatePatientData $data): RedirectResponse
    {
        return $this->handleExceptions(function () use ($data) {
            $this->patientService->create($data);

            return to_route('clinic.patients.index')
                ->with('toast', ['type' => 'success', 'message' => 'Paciente creado correctamente.']);
        }, 'patient.store');
    }

    public function show(Patient $patient): Response
    {
        $patient = $this->patientService->show($patient);

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

    public function update(UpdatePatientData $data, Patient $patient): RedirectResponse
    {
        return $this->handleExceptions(function () use ($data, $patient) {
            $this->patientService->update($patient, $data);

            return to_route('clinic.patients.index')
                ->with('toast', ['type' => 'success', 'message' => 'Paciente actualizado correctamente.']);
        }, 'patient.update');
    }

    public function destroy(Patient $patient): RedirectResponse
    {
        return $this->handleExceptions(function () use ($patient) {
            $this->patientService->delete($patient);

            return to_route('clinic.patients.index')
                ->with('toast', ['type' => 'success', 'message' => 'Paciente eliminado correctamente.']);
        }, 'patient.destroy');
    }
}
