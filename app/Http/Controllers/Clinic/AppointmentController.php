<?php

declare(strict_types=1);

namespace App\Http\Controllers\Clinic;

use App\Data\Clinic\CreateAppointmentData;
use App\Data\Clinic\CreateAppointmentNoteData;
use App\Data\Clinic\UpdateAppointmentData;
use App\Http\Controllers\Controller;
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

    public function store(CreateAppointmentData $data): RedirectResponse
    {
        return $this->handleExceptions(function () use ($data) {
            $this->appointmentService->create($data, (int) auth()->id());

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

    public function update(UpdateAppointmentData $data, Appointment $appointment): RedirectResponse
    {
        return $this->handleExceptions(function () use ($data, $appointment) {
            $this->appointmentService->update($appointment, $data);

            return to_route('clinic.appointments.index')
                ->with('toast', ['type' => 'success', 'message' => 'Turno actualizado correctamente.']);
        }, 'appointment.update');
    }

    public function addNote(Appointment $appointment, CreateAppointmentNoteData $data): RedirectResponse
    {
        return $this->handleExceptions(function () use ($appointment, $data) {
            $this->appointmentService->addNote($appointment, $data, (int) auth()->id());

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
