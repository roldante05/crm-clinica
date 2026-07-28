<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Specialty;
use Illuminate\Support\Collection;

class DashboardService
{
    public function getStats(): array
    {
        $today = now()->toDateString();

        return [
            'today_appointments' => Appointment::whereDate('appointment_date', $today)->count(),
            'today_confirmed' => Appointment::whereDate('appointment_date', $today)->where('status', AppointmentStatus::Confirmed)->count(),
            'today_pending' => Appointment::whereDate('appointment_date', $today)->where('status', AppointmentStatus::Scheduled)->count(),
            'today_completed' => Appointment::whereDate('appointment_date', $today)->where('status', AppointmentStatus::Completed)->count(),
            'today_cancelled' => Appointment::whereDate('appointment_date', $today)->where('status', AppointmentStatus::Cancelled)->count(),
            'today_no_show' => Appointment::whereDate('appointment_date', $today)->where('status', AppointmentStatus::NoShow)->count(),
            'total_patients' => Patient::count(),
            'total_doctors' => Doctor::active()->count(),
            'total_specialties' => Specialty::active()->count(),
            'upcoming_appointments' => Appointment::whereDate('appointment_date', '>=', $today)
                ->whereNotIn('status', [AppointmentStatus::Cancelled, AppointmentStatus::Completed, AppointmentStatus::NoShow])
                ->count(),
        ];
    }

    public function getTodayAppointments(): Collection
    {
        $today = now()->toDateString();

        return Appointment::with(['doctor.user', 'doctor.specialty', 'patient'])
            ->whereDate('appointment_date', $today)
            ->orderBy('appointment_time')
            ->get();
    }
}
