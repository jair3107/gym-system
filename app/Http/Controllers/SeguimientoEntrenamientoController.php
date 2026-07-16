<?php

namespace App\Http\Controllers;

use App\Enums\SeguimientoEstado;
use App\Http\Requests\StoreSeguimientoEntrenamientoRequest;
use App\Http\Requests\UpdateSeguimientoEntrenamientoRequest;
use App\Models\Cliente;
use App\Models\Rutina;
use App\Models\SeguimientoEntrenamiento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeguimientoEntrenamientoController extends Controller
{
    /**
     * Listado de seguimientos. El entrenador ve los suyos; el dueño ve todos.
     */
    public function index(Request $request): View
    {
        $seguimientos = $this->baseQuery()
            ->with(['cliente.user', 'rutina', 'entrenador'])
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->input('estado')))
            ->orderByDesc('fecha_inicio')
            ->paginate(15)
            ->withQueryString();

        return view('seguimientos-entrenamiento.index', [
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
        return view('seguimientos-entrenamiento.create', $this->formData());
    }

    /**
     * Persistir un nuevo seguimiento.
     */
    public function store(StoreSeguimientoEntrenamientoRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // El entrenador responsable es el asignado al alumno.
        $cliente = Cliente::findOrFail($data['cliente_id']);

        $seguimiento = SeguimientoEntrenamiento::create([
            'entrenador_id' => $cliente->entrenador_id,
            'cliente_id' => $cliente->id,
            'rutina_id' => $data['rutina_id'] ?? null,
            'fecha_inicio' => $data['fecha_inicio'],
            'fecha_fin' => $data['fecha_fin'] ?? null,
            'peso_inicial' => $data['peso_inicial'] ?? null,
            'peso_meta' => $data['peso_meta'] ?? null,
            'masa_inicial' => $data['masa_inicial'] ?? null,
            'masa_meta' => $data['masa_meta'] ?? null,
            'estado' => $data['estado'],
            'notas' => $data['notas'] ?? null,
        ]);

        return redirect()
            ->route('seguimientos-entrenamiento.show', $seguimiento)
            ->with('status', 'Seguimiento creado correctamente.');
    }

    /**
     * Ficha del seguimiento (rutina + progreso).
     */
    public function show(SeguimientoEntrenamiento $seguimiento): View
    {
        $this->autorizarAcceso($seguimiento);
        $seguimiento->load(['cliente.user', 'rutina', 'entrenador', 'mediciones']);

        return view('seguimientos-entrenamiento.show', ['seguimiento' => $seguimiento]);
    }

    /**
     * Formulario de edición.
     */
    public function edit(SeguimientoEntrenamiento $seguimiento): View
    {
        $this->autorizarAcceso($seguimiento);
        $seguimiento->load('cliente.user');

        return view('seguimientos-entrenamiento.edit', array_merge(
            $this->formData(),
            ['seguimiento' => $seguimiento],
        ));
    }

    /**
     * Actualizar el seguimiento (el alumno no cambia).
     */
    public function update(UpdateSeguimientoEntrenamientoRequest $request, SeguimientoEntrenamiento $seguimiento): RedirectResponse
    {
        $this->autorizarAcceso($seguimiento);

        $data = $request->validated();

        $seguimiento->update([
            'rutina_id' => $data['rutina_id'] ?? null,
            'fecha_inicio' => $data['fecha_inicio'],
            'fecha_fin' => $data['fecha_fin'] ?? null,
            'peso_inicial' => $data['peso_inicial'] ?? null,
            'peso_meta' => $data['peso_meta'] ?? null,
            'masa_inicial' => $data['masa_inicial'] ?? null,
            'masa_meta' => $data['masa_meta'] ?? null,
            'estado' => $data['estado'],
            'notas' => $data['notas'] ?? null,
        ]);

        return redirect()
            ->route('seguimientos-entrenamiento.show', $seguimiento)
            ->with('status', 'Seguimiento actualizado correctamente.');
    }

    /**
     * Eliminar el seguimiento (y sus mediciones por cascada).
     */
    public function destroy(SeguimientoEntrenamiento $seguimiento): RedirectResponse
    {
        $this->autorizarAcceso($seguimiento);
        $seguimiento->delete();

        return redirect()
            ->route('seguimientos-entrenamiento.index')
            ->with('status', 'Seguimiento eliminado correctamente.');
    }

    /**
     * Base del listado según el rol: el dueño ve todo, el entrenador lo suyo.
     */
    protected function baseQuery()
    {
        $user = auth()->user();

        return $user->isSuperAdmin()
            ? SeguimientoEntrenamiento::query()
            : SeguimientoEntrenamiento::query()->deEntrenador($user->id);
    }

    /**
     * El dueño accede a cualquier seguimiento; el entrenador solo a los suyos.
     */
    protected function autorizarAcceso(SeguimientoEntrenamiento $seguimiento): void
    {
        $user = auth()->user();

        if (! $user->isSuperAdmin() && $seguimiento->entrenador_id !== $user->id) {
            abort(403, 'Este seguimiento no te pertenece.');
        }
    }

    /**
     * Alumnos y rutinas disponibles para los formularios, según el rol.
     *
     * @return array<string, mixed>
     */
    protected function formData(): array
    {
        $user = auth()->user();

        $alumnos = $user->isSuperAdmin()
            ? Cliente::query()->whereNotNull('entrenador_id')->with('user')->get()
            : $user->alumnos()->with('user')->get();

        $rutinas = $user->isSuperAdmin()
            ? Rutina::query()->where('activa', true)->orderBy('nombre')->get()
            : $user->rutinas()->where('activa', true)->orderBy('nombre')->get();

        return [
            'alumnos' => $alumnos,
            'rutinas' => $rutinas,
            'estados' => SeguimientoEstado::cases(),
        ];
    }
}
