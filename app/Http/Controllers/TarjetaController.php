<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTarjetaRequest;
use App\Models\Cliente;
use App\Models\Tarjeta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TarjetaController extends Controller
{
    /**
     * Guardar una tarjeta para el cliente. Solo se persisten datos no sensibles
     * (marca + últimos 4); el número completo y el CVV se descartan.
     */
    public function store(StoreTarjetaRequest $request, Cliente $cliente): RedirectResponse
    {
        $data = $request->validated();
        $numero = $data['numero'];

        DB::transaction(function () use ($cliente, $data, $numero) {
            // La primera tarjeta (o si no hay primaria) queda como primaria.
            $esPrimaria = ! $cliente->tienePrimaria();

            if ($esPrimaria) {
                $cliente->tarjetas()->update(['primaria' => false]);
            }

            $cliente->tarjetas()->create([
                'titular' => $data['titular'],
                'marca' => Tarjeta::marcaDesde($numero),
                'tipo' => $data['tipo'],
                'ultimos4' => substr($numero, -4),
                'mes_exp' => $data['mes_exp'],
                'anio_exp' => $data['anio_exp'],
                'primaria' => $esPrimaria,
            ]);
        });

        return back()->with('status', 'Tarjeta guardada correctamente.');
    }

    /**
     * Marcar una tarjeta como método de pago primario.
     */
    public function primaria(Request $request, Tarjeta $tarjeta): RedirectResponse
    {
        abort_unless($request->user()->puedeGestionarVentas(), 403);

        DB::transaction(function () use ($tarjeta) {
            Tarjeta::where('cliente_id', $tarjeta->cliente_id)->update(['primaria' => false]);
            $tarjeta->update(['primaria' => true]);
        });

        return back()->with('status', 'Método de pago primario actualizado.');
    }

    /**
     * Eliminar una tarjeta. Si era la primaria y quedan otras, promueve la más
     * reciente para mantener siempre una primaria cuando hay tarjetas.
     */
    public function destroy(Request $request, Tarjeta $tarjeta): RedirectResponse
    {
        abort_unless($request->user()->puedeGestionarVentas(), 403);

        $clienteId = $tarjeta->cliente_id;
        $eraPrimaria = $tarjeta->primaria;

        DB::transaction(function () use ($tarjeta, $clienteId, $eraPrimaria) {
            $tarjeta->delete();

            if ($eraPrimaria) {
                $siguiente = Tarjeta::where('cliente_id', $clienteId)->latest()->first();
                $siguiente?->update(['primaria' => true]);
            }
        });

        return back()->with('status', 'Tarjeta eliminada.');
    }
}
