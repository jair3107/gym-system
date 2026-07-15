<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedicionNutricionRequest;
use App\Models\MedicionNutricion;
use App\Models\SeguimientoNutricion;
use Illuminate\Http\RedirectResponse;

class MedicionNutricionController extends Controller
{
    /**
     * Registrar una medición de peso (progreso) en un seguimiento.
     */
    public function store(StoreMedicionNutricionRequest $request, SeguimientoNutricion $seguimiento): RedirectResponse
    {
        $this->autorizarAcceso($seguimiento);

        $seguimiento->mediciones()->create($request->validated());

        return redirect()
            ->route('seguimientos-nutricion.show', $seguimiento)
            ->with('status', 'Medición registrada correctamente.');
    }

    /**
     * Eliminar una medición.
     */
    public function destroy(MedicionNutricion $medicion): RedirectResponse
    {
        $seguimiento = $medicion->seguimiento;
        $this->autorizarAcceso($seguimiento);

        $medicion->delete();

        return redirect()
            ->route('seguimientos-nutricion.show', $seguimiento)
            ->with('status', 'Medición eliminada.');
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
}
