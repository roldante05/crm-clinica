<?php

declare(strict_types=1);

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService,
    ) {}

    public function __invoke(): Response
    {
        $stats = $this->dashboardService->getStats();
        $todayAppointments = $this->dashboardService->getTodayAppointments();

        return Inertia::render('clinic/Dashboard', [
            'stats' => $stats,
            'todayAppointments' => $todayAppointments,
        ]);
    }
}
