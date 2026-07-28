<?php

declare(strict_types=1);

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clinic\StoreAppointmentNoteRequest;
use App\Http\Requests\Clinic\StoreAppointmentRequest;
use App\Http\Requests\Clinic\UpdateAppointmentRequest;
use App\Models\Appointment;
use App\Services\AppointmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AppointmentController extends Controller
{
    public function __construct(
        protected AppointmentService $appointmentService,
    ) {}

    public function index(Request $request): Response
    {
        $appointments = $this->appointmentService->index($request);
        $doctors = $this->appointmentService->getDoctorsList();

        return Inertia::render('clinic/appointments/Index', [
            'appointments' => $appointments,
            'filters' => $request->only(['date', 'doctor_id', 'status']),
            'doctors' => $doctors,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('clinic/appointments/Create', [
            'doctors' => $this->appointmentService->getDoctorsWithSchedules(),
            'patients' => $this->appointmentService->getPatients(),
        ]);
    }

    public function store(StoreAppointmentRequest $request): RedirectResponse
    {
        return $this->handleExceptions(function () use ($request) {
            $this->appointmentService->create($request->validated(), (int) auth()->id());

            return to_route('clinic.appointments.index')
                ->with('toast', ['type' => 'success', 'message' => 'Turno creado correctamente.']);
        }, 'appointment.store');
    }

    public function show(Appointment $appointment): Response
    {
        $appointment = $this->appointmentService->show($appointment);

        return Inertia::render('clinic/appointments/Show', [
            'appointment' => $appointment,
        ]);
    }

    public function edit(Appointment $appointment): Response
    {
        return Inertia::render('clinic/appointments/Edit', [
            'appointment' => $appointment->load(['doctor.user', 'patient']),
            'doctors' => $this->appointmentService->getDoctorsWithSchedulesForEdit(),
            'patients' => $this->appointmentService->getPatientsForEdit(),
        ]);
    }

    public function update(UpdateAppointmentRequest $request, Appointment $appointment): RedirectResponse
    {
        return $this->handleExceptions(function () use ($request, $appointment) {
            $this->appointmentService->update($appointment, $request->validated());

            return to_route('clinic.appointments.index')
                ->with('toast', ['type' => 'success', 'message' => 'Turno actualizado correctamente.']);
        }, 'appointment.update');
    }

    public function addNote(Appointment $appointment, StoreAppointmentNoteRequest $request): RedirectResponse
    {
        return $this->handleExceptions(function () use ($appointment, $request) {
            $this->appointmentService->addNote($appointment, $request->validated('content'), (int) auth()->id());

            return back()
                ->with('toast', ['type' => 'success', 'message' => 'Nota agregada correctamente.']);
        }, 'appointment.addNote');
    }

    public function destroy(Appointment $appointment): RedirectResponse
    {
        return $this->handleExceptions(function () use ($appointment) {
            $this->appointmentService->delete($appointment);

            return to_route('clinic.appointments.index')
                ->with('toast', ['type' => 'success', 'message' => 'Turno eliminado correctamente.']);
        }, 'appointment.destroy');
    }
}
