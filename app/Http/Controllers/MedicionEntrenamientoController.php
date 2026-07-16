<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedicionEntrenamientoRequest;
use App\Models\MedicionEntrenamiento;
use App\Models\SeguimientoEntrenamiento;
use Illuminate\Http\RedirectResponse;

class MedicionEntrenamientoController extends Controller
{
    /**
     * Registrar una medición de progreso (peso + masa muscular) en un seguimiento.
     */
    public function store(StoreMedicionEntrenamientoRequest $request, SeguimientoEntrenamiento $seguimiento): RedirectResponse
    {
        $this->autorizarAcceso($seguimiento);

        $seguimiento->mediciones()->create($request->validated());

        return redirect()
            ->route('seguimientos-entrenamiento.show', $seguimiento)
            ->with('status', 'Medición registrada correctamente.');
    }

    /**
     * Eliminar una medición.
     */
    public function destroy(MedicionEntrenamiento $medicion): RedirectResponse
    {
        $seguimiento = $medicion->seguimiento;
        $this->autorizarAcceso($seguimiento);

        $medicion->delete();

        return redirect()
            ->route('seguimientos-entrenamiento.show', $seguimiento)
            ->with('status', 'Medición eliminada.');
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
}
