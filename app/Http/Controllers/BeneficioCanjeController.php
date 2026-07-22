<?php

namespace App\Http\Controllers;

use App\Models\MembresiaCliente;
use App\Models\Producto;
use App\Models\Servicio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BeneficioCanjeController extends Controller
{
    /**
     * Registrar la entrega de un beneficio incluido, mientras la suscripción
     * esté vigente. Los productos descuentan inventario.
     */
    public function store(Request $request, MembresiaCliente $membresiaCliente): RedirectResponse
    {
        abort_unless($request->user()->puedeGestionarVentas(), 403);

        if (! $membresiaCliente->estaVigente()) {
            throw ValidationException::withMessages([
                'beneficio' => 'La suscripción no está vigente; no se pueden entregar beneficios.',
            ]);
        }

        $data = $request->validate([
            'beneficiable_type' => ['required', Rule::in(['producto', 'servicio'])],
            'beneficiable_id' => ['required', 'integer'],
            'cantidad' => ['nullable', 'integer', 'min:1', 'max:100'],
            'nota' => ['nullable', 'string', 'max:255'],
        ]);

        $cantidad = $data['cantidad'] ?? 1;

        // El beneficio debe estar incluido en el plan de esta suscripción.
        $incluido = $membresiaCliente->membresia?->beneficios()
            ->where('beneficiable_type', $data['beneficiable_type'])
            ->where('beneficiable_id', $data['beneficiable_id'])
            ->exists();

        if (! $incluido) {
            throw ValidationException::withMessages([
                'beneficio' => 'Ese beneficio no está incluido en esta membresía.',
            ]);
        }

        $modelo = $data['beneficiable_type'] === 'producto'
            ? Producto::find($data['beneficiable_id'])
            : Servicio::find($data['beneficiable_id']);

        if (! $modelo) {
            throw ValidationException::withMessages(['beneficio' => 'El beneficio ya no existe.']);
        }

        DB::transaction(function () use ($membresiaCliente, $data, $cantidad, $modelo, $request) {
            if ($data['beneficiable_type'] === 'producto') {
                if ($modelo->stock < $cantidad) {
                    throw ValidationException::withMessages([
                        'beneficio' => "No hay stock suficiente de «{$modelo->nombre}» (disponible: {$modelo->stock}).",
                    ]);
                }
                $modelo->decrement('stock', $cantidad);
            }

            $membresiaCliente->canjes()->create([
                'beneficiable_type' => $data['beneficiable_type'],
                'beneficiable_id' => $modelo->id,
                'descripcion' => $modelo->nombre,
                'cantidad' => $cantidad,
                'atendido_por' => $request->user()->id,
                'nota' => $data['nota'] ?? null,
            ]);
        });

        return back()->with('status', "Beneficio entregado: {$modelo->nombre}.");
    }
}
