<?php

namespace App\Http\Controllers\Clinic;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Specialty;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $today = now()->toDateString();

        $stats = [
            'today_appointments' => Appointment::where('appointment_date', $today)->count(),
            'today_confirmed' => Appointment::where('appointment_date', $today)->where('status', AppointmentStatus::Confirmed)->count(),
            'today_pending' => Appointment::where('appointment_date', $today)->where('status', AppointmentStatus::Scheduled)->count(),
            'today_completed' => Appointment::where('appointment_date', $today)->where('status', AppointmentStatus::Completed)->count(),
            'today_cancelled' => Appointment::where('appointment_date', $today)->where('status', AppointmentStatus::Cancelled)->count(),
            'today_no_show' => Appointment::where('appointment_date', $today)->where('status', AppointmentStatus::NoShow)->count(),
            'total_patients' => Patient::count(),
            'total_doctors' => Doctor::active()->count(),
            'total_specialties' => Specialty::active()->count(),
            'upcoming_appointments' => Appointment::where('appointment_date', '>=', $today)
                ->whereNotIn('status', [AppointmentStatus::Cancelled, AppointmentStatus::Completed, AppointmentStatus::NoShow])
                ->count(),
        ];

        $todayAppointments = Appointment::with(['doctor.user', 'doctor.specialty', 'patient'])
            ->where('appointment_date', $today)
            ->orderBy('appointment_time')
            ->get();

        return Inertia::render('clinic/Dashboard', [
            'stats' => $stats,
            'todayAppointments' => $todayAppointments,
        ]);
    }
}
