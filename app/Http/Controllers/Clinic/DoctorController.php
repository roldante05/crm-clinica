<?php

declare(strict_types=1);

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clinic\StoreDoctorRequest;
use App\Http\Requests\Clinic\UpdateDoctorRequest;
use App\Models\Doctor;
use App\Services\DoctorService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class DoctorController extends Controller
{
    public function __construct(
        protected DoctorService $doctorService,
    ) {}

    public function index(): Response
    {
        $doctors = $this->doctorService->index();

        return Inertia::render('clinic/doctors/Index', [
            'doctors' => $doctors,
        ]);
    }

    public function create(): Response
    {
        return $this->handleExceptions(function () {
            return Inertia::render('clinic/doctors/Create', [
                'specialties' => $this->doctorService->getSpecialties(),
                'users' => $this->doctorService->getDoctorsUsers(),
            ]);
        }, 'doctor.create');
    }

    public function store(StoreDoctorRequest $request): RedirectResponse
    {
        return $this->handleExceptions(function () use ($request) {
            $this->doctorService->create($request->validated());

            return to_route('clinic.doctors.index')
                ->with('toast', ['type' => 'success', 'message' => 'Médico creado correctamente.']);
        }, 'doctor.store');
    }

    public function edit(Doctor $doctor): Response
    {
        $doctor = $this->doctorService->show($doctor);

        return Inertia::render('clinic/doctors/Edit', [
            'doctor' => $doctor,
            'specialties' => $this->doctorService->getSpecialties(),
        ]);
    }

    public function update(UpdateDoctorRequest $request, Doctor $doctor): RedirectResponse
    {
        return $this->handleExceptions(function () use ($request, $doctor) {
            $this->doctorService->update($doctor, $request->validated());

            return to_route('clinic.doctors.index')
                ->with('toast', ['type' => 'success', 'message' => 'Médico actualizado correctamente.']);
        }, 'doctor.update');
    }

    public function destroy(Doctor $doctor): RedirectResponse
    {
        return $this->handleExceptions(function () use ($doctor) {
            $this->doctorService->delete($doctor);

            return to_route('clinic.doctors.index')
                ->with('toast', ['type' => 'success', 'message' => 'Médico eliminado correctamente.']);
        }, 'doctor.destroy');
    }
}
