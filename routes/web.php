<?php

use App\Http\Controllers\Clinic\AppointmentController;
use App\Http\Controllers\Clinic\DashboardController;
use App\Http\Controllers\Clinic\DoctorController;
use App\Http\Controllers\Clinic\DoctorScheduleController;
use App\Http\Controllers\Clinic\PatientController;
use App\Http\Controllers\Clinic\SpecialtyController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    // Clinic modules
    Route::prefix('clinic')->name('clinic.')->group(function () {
        // Admin‑only routes
        Route::middleware(['role:admin'])->group(function () {
            Route::resource('specialties', SpecialtyController::class);
            Route::resource('doctors', DoctorController::class);
            Route::prefix('doctors/{doctor}/schedules')->name('schedules.')->group(function () {
                Route::get('/', [DoctorScheduleController::class, 'index'])->name('index');
                Route::get('/create', [DoctorScheduleController::class, 'create'])->name('create');
                Route::post('/', [DoctorScheduleController::class, 'store'])->name('store');
                Route::get('/{schedule}/edit', [DoctorScheduleController::class, 'edit'])->name('edit');
                Route::put('/{schedule}', [DoctorScheduleController::class, 'update'])->name('update');
                Route::delete('/{schedule}', [DoctorScheduleController::class, 'destroy'])->name('destroy');
            });
        });

        // Admin + Secretaria routes (patients and appointments)
        Route::middleware(['role:admin,secretary,doctor'])->group(function () {
            Route::resource('patients', PatientController::class);
            Route::resource('appointments', AppointmentController::class);
            Route::post('appointments/{appointment}/notes', [AppointmentController::class, 'addNote'])->name('appointments.notes.store');
        });
    });
});

require __DIR__.'/settings.php';
