<?php

namespace App\Console\Commands;

use App\Enums\ClienteEstado;
use App\Enums\MembresiaEstado;
use App\Enums\MetodoPago;
use App\Enums\UserRole;
use App\Models\MembresiaCliente;
use App\Models\User;
use App\Models\Venta;
use App\Notifications\SuscripcionFinalizada;
use App\Notifications\SuscripcionPorRenovar;
use App\Notifications\SuscripcionPorVencer;
use App\Notifications\SuscripcionRenovada;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcesarMembresias extends Command
{
    protected $signature = 'membresias:procesar {--dias-aviso=3 : Días de anticipación para el recordatorio}';

    protected $description = 'Envía recordatorios de vencimiento, autorenueva (autocobro) las suscripciones y vence las demás, notificando al cliente.';

    public function handle(): int
    {
        $diasAviso = (int) $this->option('dias-aviso');
        $hoy = now()->startOfDay();

        $avisos = $this->enviarRecordatorios($hoy, $diasAviso);
        [$renovadas, $finalizadas] = $this->procesarVencimientos($hoy);

        $this->info("Recordatorios enviados: {$avisos}. Renovadas: {$renovadas}. Finalizadas: {$finalizadas}.");

        return self::SUCCESS;
    }

    /**
     * Avisa a los clientes cuya suscripción vence dentro de la ventana indicada.
     */
    protected function enviarRecordatorios(\Carbon\Carbon $hoy, int $diasAviso): int
    {
        $porAvisar = MembresiaCliente::query()
            ->activas()
            ->whereNull('recordatorio_enviado_at')
            ->whereDate('fecha_fin', '>=', $hoy->toDateString())
            ->whereDate('fecha_fin', '<=', $hoy->copy()->addDays($diasAviso)->toDateString())
            ->with(['cliente.user', 'membresia'])
            ->get();

        foreach ($porAvisar as $suscripcion) {
            $user = $suscripcion->cliente?->user;

            if ($user) {
                $user->notify($suscripcion->auto_renovacion
                    ? new SuscripcionPorRenovar($suscripcion)
                    : new SuscripcionPorVencer($suscripcion));
            }

            $suscripcion->update(['recordatorio_enviado_at' => now()]);
        }

        return $porAvisar->count();
    }

    /**
     * Renueva (autocobro) o vence las suscripciones cuya fecha ya pasó.
     *
     * @return array{0:int,1:int} [renovadas, finalizadas]
     */
    protected function procesarVencimientos(\Carbon\Carbon $hoy): array
    {
        $vencidas = MembresiaCliente::query()
            ->activas()
            ->whereDate('fecha_fin', '<', $hoy->toDateString())
            ->with(['cliente.user', 'membresia', 'venta'])
            ->get();

        $renovadas = 0;
        $finalizadas = 0;

        foreach ($vencidas as $suscripcion) {
            // Solo se autorenueva si el plan lo permite y el cliente conserva
            // una tarjeta primaria para el autocobro.
            if ($suscripcion->auto_renovacion
                && $suscripcion->membresia
                && $suscripcion->cliente?->tienePrimaria()) {
                $nueva = $this->renovar($suscripcion);
                $suscripcion->cliente?->user?->notify(new SuscripcionRenovada($nueva));
                $renovadas++;

                continue;
            }

            $suscripcion->update(['estado' => MembresiaEstado::Vencida]);

            $cliente = $suscripcion->cliente;
            if ($cliente
                && $cliente->estado === ClienteEstado::Activo
                && ! $cliente->membresiaVigente()) {
                $cliente->update(['estado' => ClienteEstado::Inactivo]);
            }

            $cliente?->user?->notify(new SuscripcionFinalizada($suscripcion));
            $finalizadas++;
        }

        return [$renovadas, $finalizadas];
    }

    /**
     * Ejecuta el autocobro: crea la venta automática y el nuevo periodo.
     */
    protected function renovar(MembresiaCliente $suscripcion): MembresiaCliente
    {
        return DB::transaction(function () use ($suscripcion) {
            $membresia = $suscripcion->membresia;
            $inicio = $suscripcion->fecha_fin->copy()->addDay()->startOfDay();
            $metodo = $suscripcion->metodo_pago ?? MetodoPago::Efectivo;

            $cajeroId = $suscripcion->cajero_id
                ?? $suscripcion->venta?->cajero_id
                ?? User::query()->where('role', UserRole::SuperAdmin->value)->value('id');

            $venta = Venta::create([
                'cajero_id' => $cajeroId,
                'cliente_id' => $suscripcion->cliente_id,
                'total' => $membresia->precio,
                'metodo_pago' => $metodo,
                'automatica' => true,
                'notas' => 'Renovación automática de suscripción.',
            ]);

            $venta->items()->create([
                'vendible_type' => 'membresia',
                'vendible_id' => $membresia->id,
                'descripcion' => $membresia->nombre,
                'precio_unitario' => $membresia->precio,
                'cantidad' => 1,
                'subtotal' => $membresia->precio,
            ]);

            $nueva = MembresiaCliente::create([
                'cliente_id' => $suscripcion->cliente_id,
                'membresia_id' => $membresia->id,
                'venta_id' => $venta->id,
                'cajero_id' => $cajeroId,
                'fecha_inicio' => $inicio->toDateString(),
                'fecha_fin' => $inicio->copy()->addDays($membresia->duracion_dias)->toDateString(),
                'estado' => MembresiaEstado::Activa,
                'auto_renovacion' => true,
                'metodo_pago' => $metodo,
                'tarjeta_id' => $suscripcion->cliente?->tarjetaPrimaria()?->id ?? $suscripcion->tarjeta_id,
                'renovacion_de_id' => $suscripcion->id,
            ]);

            $suscripcion->update(['estado' => MembresiaEstado::Renovada]);
            $suscripcion->cliente?->update(['estado' => ClienteEstado::Activo]);

            return $nueva;
        });
    }
}
