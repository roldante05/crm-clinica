<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clinic\StoreSpecialtyRequest;
use App\Http\Requests\Clinic\UpdateSpecialtyRequest;
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

    public function store(StoreSpecialtyRequest $request): RedirectResponse
    {
        Specialty::create($request->validated());

        return to_route('clinic.specialties.index')
            ->with('toast', ['type' => 'success', 'message' => 'Especialidad creada correctamente.']);
    }

    public function edit(Specialty $specialty): Response
    {
        return Inertia::render('clinic/specialties/Edit', [
            'specialty' => $specialty,
        ]);
    }

    public function update(UpdateSpecialtyRequest $request, Specialty $specialty): RedirectResponse
    {
        $specialty->update($request->validated());

        return to_route('clinic.specialties.index')
            ->with('toast', ['type' => 'success', 'message' => 'Especialidad actualizada correctamente.']);
    }

    public function destroy(Specialty $specialty): RedirectResponse
    {
        $specialty->delete();

        return to_route('clinic.specialties.index')
            ->with('toast', ['type' => 'success', 'message' => 'Especialidad eliminada correctamente.']);
    }
}
