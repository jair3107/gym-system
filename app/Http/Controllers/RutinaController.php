<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRutinaRequest;
use App\Http\Requests\UpdateRutinaRequest;
use App\Models\Rutina;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RutinaController extends Controller
{
    /**
     * Listado de rutinas. El entrenador solo ve las suyas; el dueño ve todas.
     */
    public function index(Request $request): View
    {
        $rutinas = $this->baseQuery()
            ->with('entrenador')
            ->buscar($request->input('buscar'))
            ->orderByDesc('activa')
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('rutinas.index', [
            'rutinas' => $rutinas,
            'filtros' => $request->only(['buscar']),
        ]);
    }

    /**
     * Formulario de alta.
     */
    public function create(): View
    {
        return view('rutinas.create');
    }

    /**
     * Persistir una nueva rutina.
     */
    public function store(StoreRutinaRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $rutina = $request->user()->rutinas()->create([
            'nombre' => $data['nombre'],
            'objetivo' => $data['objetivo'] ?? null,
            'nivel' => $data['nivel'] ?? null,
            'duracion_min' => $data['duracion_min'] ?? null,
            'descripcion' => $data['descripcion'] ?? null,
            'activa' => $data['activa'] ?? true,
            'plan' => $this->normalizarPlan($data['plan'] ?? []),
        ]);

        return redirect()
            ->route('rutinas.show', $rutina)
            ->with('status', 'Rutina creada correctamente.');
    }

    /**
     * Ver una rutina (incluye la tabla semanal).
     */
    public function show(Rutina $rutina): View
    {
        $this->autorizarAcceso($rutina);
        $rutina->load('entrenador');

        return view('rutinas.show', ['rutina' => $rutina]);
    }

    /**
     * Formulario de edición.
     */
    public function edit(Rutina $rutina): View
    {
        $this->autorizarAcceso($rutina);

        return view('rutinas.edit', ['rutina' => $rutina]);
    }

    /**
     * Actualizar una rutina.
     */
    public function update(UpdateRutinaRequest $request, Rutina $rutina): RedirectResponse
    {
        $this->autorizarAcceso($rutina);

        $data = $request->validated();

        $rutina->update([
            'nombre' => $data['nombre'],
            'objetivo' => $data['objetivo'] ?? null,
            'nivel' => $data['nivel'] ?? null,
            'duracion_min' => $data['duracion_min'] ?? null,
            'descripcion' => $data['descripcion'] ?? null,
            'activa' => $data['activa'] ?? false,
            'plan' => $this->normalizarPlan($data['plan'] ?? []),
        ]);

        return redirect()
            ->route('rutinas.show', $rutina)
            ->with('status', 'Rutina actualizada correctamente.');
    }

    /**
     * Eliminar una rutina.
     */
    public function destroy(Rutina $rutina): RedirectResponse
    {
        $this->autorizarAcceso($rutina);
        $rutina->delete();

        return redirect()
            ->route('rutinas.index')
            ->with('status', 'Rutina eliminada correctamente.');
    }

    /**
     * Base del listado según el rol: el dueño ve todo, el entrenador lo suyo.
     */
    protected function baseQuery()
    {
        $user = auth()->user();

        return $user->isSuperAdmin()
            ? Rutina::query()
            : Rutina::query()->deEntrenador($user->id);
    }

    /**
     * El dueño accede a cualquier rutina; el entrenador solo a las suyas.
     */
    protected function autorizarAcceso(Rutina $rutina): void
    {
        $user = auth()->user();

        if (! $user->isSuperAdmin() && $rutina->entrenador_id !== $user->id) {
            abort(403, 'Esta rutina no te pertenece.');
        }
    }

    /**
     * Reconstruye el plan semanal usando solo días y bloques conocidos,
     * descartando celdas vacías.
     *
     * @param  array<string, mixed>  $plan
     * @return array{duraciones: array<string, string>, dias: array<string, array<string, string>>}
     */
    protected function normalizarPlan(array $plan): array
    {
        $duraciones = [];
        foreach (array_keys(Rutina::BLOQUES) as $bloque) {
            $valor = trim((string) ($plan['duraciones'][$bloque] ?? ''));
            if ($valor !== '') {
                $duraciones[$bloque] = $valor;
            }
        }

        $dias = [];
        foreach (array_keys(Rutina::DIAS) as $dia) {
            $celdas = [];
            foreach (array_keys(Rutina::BLOQUES) as $bloque) {
                $valor = trim((string) ($plan['dias'][$dia][$bloque] ?? ''));
                if ($valor !== '') {
                    $celdas[$bloque] = $valor;
                }
            }
            if ($celdas !== []) {
                $dias[$dia] = $celdas;
            }
        }

        return ['duraciones' => $duraciones, 'dias' => $dias];
    }
}
