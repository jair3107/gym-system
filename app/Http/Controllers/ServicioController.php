<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServicioRequest;
use App\Http\Requests\UpdateServicioRequest;
use App\Models\Servicio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServicioController extends Controller
{
    /**
     * Catálogo de servicios sueltos.
     */
    public function index(Request $request): View
    {
        $servicios = Servicio::query()
            ->buscar($request->input('buscar'))
            ->orderByDesc('activo')
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('servicios.index', [
            'servicios' => $servicios,
            'filtros' => $request->only(['buscar']),
        ]);
    }

    public function create(): View
    {
        return view('servicios.create');
    }

    public function store(StoreServicioRequest $request): RedirectResponse
    {
        Servicio::create($request->validated());

        return redirect()
            ->route('servicios.index')
            ->with('status', 'Servicio creado correctamente.');
    }

    public function edit(Servicio $servicio): View
    {
        return view('servicios.edit', ['servicio' => $servicio]);
    }

    public function update(UpdateServicioRequest $request, Servicio $servicio): RedirectResponse
    {
        $servicio->update($request->validated());

        return redirect()
            ->route('servicios.index')
            ->with('status', 'Servicio actualizado correctamente.');
    }

    public function destroy(Servicio $servicio): RedirectResponse
    {
        $servicio->delete();

        return redirect()
            ->route('servicios.index')
            ->with('status', 'Servicio eliminado correctamente.');
    }
}
