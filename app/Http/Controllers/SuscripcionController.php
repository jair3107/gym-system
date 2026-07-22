<?php

namespace App\Http\Controllers;

use App\Enums\MetodoPago;
use App\Models\MembresiaCliente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SuscripcionController extends Controller
{
    /**
     * Activar o desactivar la auto-renovación de una suscripción.
     */
    public function toggleAutoRenovacion(Request $request, MembresiaCliente $membresiaCliente): RedirectResponse
    {
        abort_unless($request->user()->puedeGestionarVentas(), 403);

        $activar = $request->boolean('auto_renovacion');

        if ($activar && ! ($membresiaCliente->membresia?->renovable ?? false)) {
            throw ValidationException::withMessages([
                'auto_renovacion' => 'Este plan no permite auto-renovación.',
            ]);
        }

        $tarjeta = $activar ? $membresiaCliente->cliente?->tarjetaPrimaria() : null;

        if ($activar && ! $tarjeta) {
            throw ValidationException::withMessages([
                'auto_renovacion' => 'El cliente no tiene una tarjeta primaria; no se puede activar la auto-renovación.',
            ]);
        }

        $membresiaCliente->update([
            'auto_renovacion' => $activar,
            'tarjeta_id' => $activar ? $tarjeta->id : null,
            'metodo_pago' => $activar ? MetodoPago::Tarjeta : $membresiaCliente->metodo_pago,
        ]);

        return back()->with('status', $activar
            ? 'Auto-renovación activada.'
            : 'Auto-renovación desactivada.');
    }
}
