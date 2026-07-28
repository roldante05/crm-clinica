<?php

declare(strict_types=1);

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clinic\StoreDoctorScheduleRequest;
use App\Http\Requests\Clinic\UpdateDoctorScheduleRequest;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class DoctorScheduleController extends Controller
{
    public function index(Doctor $doctor): Response
    {
        $schedules = $doctor->schedules()->orderBy('day_of_week')->orderBy('start_time')->get();

        return Inertia::render('clinic/schedules/Index', [
            'doctor' => $doctor->load('user', 'specialty'),
            'schedules' => $schedules,
        ]);
    }

    public function create(Doctor $doctor): Response
    {
        return Inertia::render('clinic/schedules/Create', [
            'doctor' => $doctor->load('user'),
        ]);
    }

    public function store(Doctor $doctor, StoreDoctorScheduleRequest $request): RedirectResponse
    {
        return $this->handleExceptions(function () use ($doctor, $request) {
            $doctor->schedules()->create($request->validated());

            return to_route('clinic.schedules.index', $doctor)
                ->with('toast', ['type' => 'success', 'message' => 'Horario creado correctamente.']);
        }, 'schedule.store');
    }

    public function edit(Doctor $doctor, DoctorSchedule $schedule): Response
    {
        return Inertia::render('clinic/schedules/Edit', [
            'doctor' => $doctor->load('user'),
            'schedule' => $schedule,
        ]);
    }

    public function update(Doctor $doctor, DoctorSchedule $schedule, UpdateDoctorScheduleRequest $request): RedirectResponse
    {
        return $this->handleExceptions(function () use ($doctor, $schedule, $request) {
            $schedule->update($request->validated());

            return to_route('clinic.schedules.index', $doctor)
                ->with('toast', ['type' => 'success', 'message' => 'Horario actualizado correctamente.']);
        }, 'schedule.update');
    }

    public function destroy(Doctor $doctor, DoctorSchedule $schedule): RedirectResponse
    {
        return $this->handleExceptions(function () use ($doctor, $schedule) {
            $schedule->delete();

            return to_route('clinic.schedules.index', $doctor)
                ->with('toast', ['type' => 'success', 'message' => 'Horario eliminado correctamente.']);
        }, 'schedule.destroy');
    }
}
