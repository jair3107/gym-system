<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDietaRequest;
use App\Http\Requests\UpdateDietaRequest;
use App\Models\Dieta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DietaController extends Controller
{
    /**
     * Listado de dietas. El nutriólogo solo ve las suyas; el dueño ve todas.
     */
    public function index(Request $request): View
    {
        $dietas = $this->baseQuery()
            ->with('nutriologo')
            ->buscar($request->input('buscar'))
            ->orderByDesc('activa')
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('dietas.index', [
            'dietas' => $dietas,
            'filtros' => $request->only(['buscar']),
        ]);
    }

    /**
     * Formulario de alta.
     */
    public function create(): View
    {
        return view('dietas.create');
    }

    /**
     * Persistir una nueva dieta.
     */
    public function store(StoreDietaRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $dieta = $request->user()->dietas()->create([
            'nombre' => $data['nombre'],
            'objetivo' => $data['objetivo'] ?? null,
            'calorias_objetivo' => $data['calorias_objetivo'] ?? null,
            'descripcion' => $data['descripcion'] ?? null,
            'activa' => $data['activa'] ?? true,
            'plan' => $this->normalizarPlan($data['plan'] ?? []),
        ]);

        return redirect()
            ->route('dietas.show', $dieta)
            ->with('status', 'Dieta creada correctamente.');
    }

    /**
     * Ver una dieta (incluye la tabla semanal).
     */
    public function show(Dieta $dieta): View
    {
        $this->autorizarAcceso($dieta);
        $dieta->load('nutriologo');

        return view('dietas.show', ['dieta' => $dieta]);
    }

    /**
     * Formulario de edición.
     */
    public function edit(Dieta $dieta): View
    {
        $this->autorizarAcceso($dieta);

        return view('dietas.edit', ['dieta' => $dieta]);
    }

    /**
     * Actualizar una dieta.
     */
    public function update(UpdateDietaRequest $request, Dieta $dieta): RedirectResponse
    {
        $this->autorizarAcceso($dieta);

        $data = $request->validated();

        $dieta->update([
            'nombre' => $data['nombre'],
            'objetivo' => $data['objetivo'] ?? null,
            'calorias_objetivo' => $data['calorias_objetivo'] ?? null,
            'descripcion' => $data['descripcion'] ?? null,
            'activa' => $data['activa'] ?? false,
            'plan' => $this->normalizarPlan($data['plan'] ?? []),
        ]);

        return redirect()
            ->route('dietas.show', $dieta)
            ->with('status', 'Dieta actualizada correctamente.');
    }

    /**
     * Eliminar una dieta.
     */
    public function destroy(Dieta $dieta): RedirectResponse
    {
        $this->autorizarAcceso($dieta);
        $dieta->delete();

        return redirect()
            ->route('dietas.index')
            ->with('status', 'Dieta eliminada correctamente.');
    }

    /**
     * Base del listado según el rol: el dueño ve todo, el nutriólogo lo suyo.
     */
    protected function baseQuery()
    {
        $user = auth()->user();

        return $user->isSuperAdmin()
            ? Dieta::query()
            : Dieta::query()->deNutriologo($user->id);
    }

    /**
     * El dueño accede a cualquier dieta; el nutriólogo solo a las suyas.
     */
    protected function autorizarAcceso(Dieta $dieta): void
    {
        $user = auth()->user();

        if (! $user->isSuperAdmin() && $dieta->nutriologo_id !== $user->id) {
            abort(403, 'Esta dieta no te pertenece.');
        }
    }

    /**
     * Reconstruye el plan semanal usando solo días y tiempos conocidos,
     * descartando celdas vacías.
     *
     * @param  array<string, mixed>  $plan
     * @return array{horarios: array<string, string>, dias: array<string, array<string, string>>}
     */
    protected function normalizarPlan(array $plan): array
    {
        $horarios = [];
        foreach (array_keys(Dieta::TIEMPOS) as $tiempo) {
            $valor = trim((string) ($plan['horarios'][$tiempo] ?? ''));
            if ($valor !== '') {
                $horarios[$tiempo] = $valor;
            }
        }

        $dias = [];
        foreach (array_keys(Dieta::DIAS) as $dia) {
            $celdas = [];
            foreach (array_keys(Dieta::TIEMPOS) as $tiempo) {
                $valor = trim((string) ($plan['dias'][$dia][$tiempo] ?? ''));
                if ($valor !== '') {
                    $celdas[$tiempo] = $valor;
                }
            }
            if ($celdas !== []) {
                $dias[$dia] = $celdas;
            }
        }

        return ['horarios' => $horarios, 'dias' => $dias];
    }
}
