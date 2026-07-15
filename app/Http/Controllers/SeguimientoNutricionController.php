<?php

namespace App\Http\Controllers;

use App\Enums\SeguimientoEstado;
use App\Http\Requests\StoreSeguimientoNutricionRequest;
use App\Http\Requests\UpdateSeguimientoNutricionRequest;
use App\Models\Cliente;
use App\Models\Dieta;
use App\Models\SeguimientoNutricion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeguimientoNutricionController extends Controller
{
    /**
     * Listado de seguimientos. El nutriólogo ve los suyos; el dueño ve todos.
     */
    public function index(Request $request): View
    {
        $seguimientos = $this->baseQuery()
            ->with(['cliente.user', 'dieta', 'nutriologo'])
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->input('estado')))
            ->orderByDesc('fecha_inicio')
            ->paginate(15)
            ->withQueryString();

        return view('seguimientos-nutricion.index', [
            'seguimientos' => $seguimientos,
            'estados' => SeguimientoEstado::cases(),
            'filtros' => $request->only(['estado']),
        ]);
    }

    /**
     * Formulario de alta.
     */
    public function create(): View
    {
        return view('seguimientos-nutricion.create', $this->formData());
    }

    /**
     * Persistir un nuevo seguimiento.
     */
    public function store(StoreSeguimientoNutricionRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // El nutriólogo responsable es el asignado al paciente.
        $cliente = Cliente::findOrFail($data['cliente_id']);

        $seguimiento = SeguimientoNutricion::create([
            'nutriologo_id' => $cliente->nutriologo_id,
            'cliente_id' => $cliente->id,
            'dieta_id' => $data['dieta_id'] ?? null,
            'fecha_inicio' => $data['fecha_inicio'],
            'fecha_fin' => $data['fecha_fin'] ?? null,
            'peso_inicial' => $data['peso_inicial'] ?? null,
            'peso_meta' => $data['peso_meta'] ?? null,
            'estado' => $data['estado'],
            'notas' => $data['notas'] ?? null,
        ]);

        return redirect()
            ->route('seguimientos-nutricion.show', $seguimiento)
            ->with('status', 'Seguimiento creado correctamente.');
    }

    /**
     * Ficha del seguimiento (dieta + progreso).
     */
    public function show(SeguimientoNutricion $seguimiento): View
    {
        $this->autorizarAcceso($seguimiento);
        $seguimiento->load(['cliente.user', 'dieta', 'nutriologo', 'mediciones']);

        return view('seguimientos-nutricion.show', ['seguimiento' => $seguimiento]);
    }

    /**
     * Formulario de edición.
     */
    public function edit(SeguimientoNutricion $seguimiento): View
    {
        $this->autorizarAcceso($seguimiento);
        $seguimiento->load('cliente.user');

        return view('seguimientos-nutricion.edit', array_merge(
            $this->formData(),
            ['seguimiento' => $seguimiento],
        ));
    }

    /**
     * Actualizar el seguimiento (el paciente no cambia).
     */
    public function update(UpdateSeguimientoNutricionRequest $request, SeguimientoNutricion $seguimiento): RedirectResponse
    {
        $this->autorizarAcceso($seguimiento);

        $data = $request->validated();

        $seguimiento->update([
            'dieta_id' => $data['dieta_id'] ?? null,
            'fecha_inicio' => $data['fecha_inicio'],
            'fecha_fin' => $data['fecha_fin'] ?? null,
            'peso_inicial' => $data['peso_inicial'] ?? null,
            'peso_meta' => $data['peso_meta'] ?? null,
            'estado' => $data['estado'],
            'notas' => $data['notas'] ?? null,
        ]);

        return redirect()
            ->route('seguimientos-nutricion.show', $seguimiento)
            ->with('status', 'Seguimiento actualizado correctamente.');
    }

    /**
     * Eliminar el seguimiento (y sus mediciones por cascada).
     */
    public function destroy(SeguimientoNutricion $seguimiento): RedirectResponse
    {
        $this->autorizarAcceso($seguimiento);
        $seguimiento->delete();

        return redirect()
            ->route('seguimientos-nutricion.index')
            ->with('status', 'Seguimiento eliminado correctamente.');
    }

    /**
     * Base del listado según el rol: el dueño ve todo, el nutriólogo lo suyo.
     */
    protected function baseQuery()
    {
        $user = auth()->user();

        return $user->isSuperAdmin()
            ? SeguimientoNutricion::query()
            : SeguimientoNutricion::query()->deNutriologo($user->id);
    }

    /**
     * El dueño accede a cualquier seguimiento; el nutriólogo solo a los suyos.
     */
    protected function autorizarAcceso(SeguimientoNutricion $seguimiento): void
    {
        $user = auth()->user();

        if (! $user->isSuperAdmin() && $seguimiento->nutriologo_id !== $user->id) {
            abort(403, 'Este seguimiento no te pertenece.');
        }
    }

    /**
     * Pacientes y dietas disponibles para los formularios, según el rol.
     *
     * @return array<string, mixed>
     */
    protected function formData(): array
    {
        $user = auth()->user();

        $pacientes = $user->isSuperAdmin()
            ? Cliente::query()->whereNotNull('nutriologo_id')->with('user')->get()
            : $user->pacientes()->with('user')->get();

        $dietas = $user->isSuperAdmin()
            ? Dieta::query()->where('activa', true)->orderBy('nombre')->get()
            : $user->dietas()->where('activa', true)->orderBy('nombre')->get();

        return [
            'pacientes' => $pacientes,
            'dietas' => $dietas,
            'estados' => SeguimientoEstado::cases(),
        ];
    }
}
