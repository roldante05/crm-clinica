<?php

declare(strict_types=1);

namespace App\Http\Controllers\Clinic;

use App\Data\Clinic\CreateSpecialtyData;
use App\Data\Clinic\UpdateSpecialtyData;
use App\Http\Controllers\Controller;
use App\Models\Specialty;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SpecialtyController extends Controller
{
    public function index(): Response
    {
        $specialties = Specialty::withCount('doctors')->latest()->paginate(15);

        return Inertia::render('clinic/specialties/Index', [
            'specialties' => $specialties,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('clinic/specialties/Create');
    }

    public function store(CreateSpecialtyData $data): RedirectResponse
    {
        return $this->handleExceptions(function () use ($data) {
            Specialty::create(array_filter($data->toArray(), fn ($v) => $v !== null));

            return to_route('clinic.specialties.index')
                ->with('toast', ['type' => 'success', 'message' => 'Especialidad creada correctamente.']);
        }, 'specialty.store');
    }

    public function edit(Specialty $specialty): Response
    {
        return Inertia::render('clinic/specialties/Edit', [
            'specialty' => $specialty,
        ]);
    }

    public function update(UpdateSpecialtyData $data, Specialty $specialty): RedirectResponse
    {
        return $this->handleExceptions(function () use ($data, $specialty) {
            $specialty->update(array_filter($data->toArray(), fn ($v) => $v !== null));

            return to_route('clinic.specialties.index')
                ->with('toast', ['type' => 'success', 'message' => 'Especialidad actualizada correctamente.']);
        }, 'specialty.update');
    }

    public function destroy(Specialty $specialty): RedirectResponse
    {
        return $this->handleExceptions(function () use ($specialty) {
            $specialty->delete();

            return to_route('clinic.specialties.index')
                ->with('toast', ['type' => 'success', 'message' => 'Especialidad eliminada correctamente.']);
        }, 'specialty.destroy');
    }
}
