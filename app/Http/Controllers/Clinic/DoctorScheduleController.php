<?php

declare(strict_types=1);

namespace App\Http\Controllers\Clinic;

use App\Data\Clinic\CreateDoctorScheduleData;
use App\Data\Clinic\UpdateDoctorScheduleData;
use App\Http\Controllers\Controller;
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

    public function store(Doctor $doctor, CreateDoctorScheduleData $data): RedirectResponse
    {
        return $this->handleExceptions(function () use ($doctor, $data) {
            $doctor->schedules()->create(array_filter($data->toArray(), fn ($v) => $v !== null));

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

    public function update(Doctor $doctor, DoctorSchedule $schedule, UpdateDoctorScheduleData $data): RedirectResponse
    {
        return $this->handleExceptions(function () use ($doctor, $schedule, $data) {
            $schedule->update(array_filter($data->toArray(), fn ($v) => $v !== null));

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
