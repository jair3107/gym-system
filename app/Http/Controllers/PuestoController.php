<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePuestoRequest;
use App\Http\Requests\UpdatePuestoRequest;
use App\Models\Puesto;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PuestoController extends Controller
{
    public function index(): View
    {
        return view('puestos.index', [
            'puestos' => Puesto::withCount('empleados')->orderBy('nombre')->get(),
        ]);
    }

    public function create(): View
    {
        return view('puestos.create');
    }

    public function store(StorePuestoRequest $request): RedirectResponse
    {
        Puesto::create($request->validated());

        return redirect()
            ->route('puestos.index')
            ->with('status', 'Puesto creado correctamente.');
    }

    public function edit(Puesto $puesto): View
    {
        return view('puestos.edit', ['puesto' => $puesto]);
    }

    public function update(UpdatePuestoRequest $request, Puesto $puesto): RedirectResponse
    {
        $puesto->update($request->validated());

        return redirect()
            ->route('puestos.index')
            ->with('status', 'Puesto actualizado correctamente.');
    }

    public function destroy(Puesto $puesto): RedirectResponse
    {
        $puesto->delete();

        return redirect()
            ->route('puestos.index')
            ->with('status', 'Puesto eliminado correctamente.');
    }
}
