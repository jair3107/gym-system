<?php

namespace Tests\Feature;

use App\Enums\ClienteEstado;
use App\Enums\MembresiaEstado;
use App\Enums\MetodoPago;
use App\Enums\UserRole;
use App\Models\Cliente;
use App\Models\Membresia;
use App\Models\MembresiaCliente;
use App\Models\Producto;
use App\Models\Puesto;
use App\Models\Servicio;
use App\Models\User;
use App\Models\Venta;
use App\Notifications\SuscripcionFinalizada;
use App\Notifications\SuscripcionPorRenovar;
use App\Notifications\SuscripcionPorVencer;
use App\Notifications\SuscripcionRenovada;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SuscripcionTest extends TestCase
{
    use RefreshDatabase;

    private function empleadoCon(string $slug): User
    {
        $user = User::factory()->create(['role' => UserRole::Empleado]);
        $puesto = Puesto::firstOrCreate(['slug' => $slug], ['nombre' => ucfirst($slug)]);
        $user->puestos()->attach($puesto);

        return $user;
    }

    private function cajero(): User
    {
        return $this->empleadoCon(Puesto::CAJERO);
    }

    private function conTarjetaPrimaria(Cliente $cliente): Cliente
    {
        $cliente->tarjetas()->create([
            'titular' => 'Test',
            'marca' => 'visa',
            'tipo' => \App\Enums\TipoTarjeta::Debito,
            'ultimos4' => '4242',
            'mes_exp' => 12,
            'anio_exp' => (int) now()->addYears(3)->format('Y'),
            'primaria' => true,
        ]);

        return $cliente;
    }

    private function suscripcion(Cliente $cliente, Membresia $membresia, array $overrides = []): MembresiaCliente
    {
        return $cliente->membresiasCliente()->create(array_merge([
            'membresia_id' => $membresia->id,
            'fecha_inicio' => now()->subDays(30)->toDateString(),
            'fecha_fin' => now()->addDays(15)->toDateString(),
            'estado' => MembresiaEstado::Activa,
            'auto_renovacion' => false,
        ], $overrides));
    }

    // ---- Venta / datos de suscripción ------------------------------------

    public function test_la_venta_guarda_datos_de_suscripcion_si_el_plan_es_renovable(): void
    {
        $cajero = $this->cajero();
        $cliente = $this->conTarjetaPrimaria(Cliente::factory()->create(['estado' => ClienteEstado::Inactivo]));
        $membresia = Membresia::factory()->create(['duracion_dias' => 30, 'activa' => true, 'renovable' => true]);

        $this->actingAs($cajero)->post(route('ventas.store'), [
            'cliente_id' => $cliente->id,
            'metodo_pago' => MetodoPago::Tarjeta->value,
            'auto_renovacion' => '1',
            'items' => [['tipo' => 'membresia', 'id' => $membresia->id, 'cantidad' => 1]],
        ])->assertRedirect();

        $sub = MembresiaCliente::first();
        $this->assertTrue($sub->auto_renovacion);
        $this->assertSame(MetodoPago::Tarjeta, $sub->metodo_pago);
        $this->assertSame($cajero->id, $sub->cajero_id);
    }

    public function test_no_activa_auto_renovacion_si_el_plan_no_lo_permite(): void
    {
        $cajero = $this->cajero();
        $cliente = Cliente::factory()->create(['estado' => ClienteEstado::Inactivo]);
        $membresia = Membresia::factory()->create(['duracion_dias' => 30, 'activa' => true, 'renovable' => false]);

        $this->actingAs($cajero)->post(route('ventas.store'), [
            'cliente_id' => $cliente->id,
            'metodo_pago' => MetodoPago::Efectivo->value,
            'auto_renovacion' => '1',
            'items' => [['tipo' => 'membresia', 'id' => $membresia->id, 'cantidad' => 1]],
        ])->assertRedirect();

        $this->assertFalse(MembresiaCliente::first()->auto_renovacion);
    }

    // ---- Autorenovación / vencimiento ------------------------------------

    public function test_procesar_autorenueva_con_autocobro_y_notifica(): void
    {
        Notification::fake();
        $cajero = $this->cajero();
        $cliente = $this->conTarjetaPrimaria(Cliente::factory()->create(['estado' => ClienteEstado::Activo]));
        $membresia = Membresia::factory()->create(['precio' => 650, 'duracion_dias' => 30, 'renovable' => true]);

        $vieja = $this->suscripcion($cliente, $membresia, [
            'fecha_fin' => now()->subDay()->toDateString(),
            'estado' => MembresiaEstado::Activa,
            'auto_renovacion' => true,
            'metodo_pago' => MetodoPago::Tarjeta,
            'cajero_id' => $cajero->id,
        ]);

        $this->artisan('membresias:procesar')->assertSuccessful();

        $this->assertSame(MembresiaEstado::Renovada, $vieja->fresh()->estado);

        $nueva = MembresiaCliente::where('renovacion_de_id', $vieja->id)->first();
        $this->assertNotNull($nueva);
        $this->assertSame(MembresiaEstado::Activa, $nueva->estado);
        $this->assertTrue($nueva->auto_renovacion);
        $this->assertTrue($nueva->fecha_fin->isFuture());

        $venta = Venta::where('automatica', true)->first();
        $this->assertNotNull($venta);
        $this->assertEquals(650, $venta->total);

        $this->assertSame(ClienteEstado::Activo, $cliente->fresh()->estado);
        Notification::assertSentTo($cliente->user, SuscripcionRenovada::class);
    }

    public function test_procesar_finaliza_y_notifica_sin_auto_renovacion(): void
    {
        Notification::fake();
        $cliente = Cliente::factory()->create(['estado' => ClienteEstado::Activo]);
        $membresia = Membresia::factory()->create(['duracion_dias' => 30, 'renovable' => true]);

        $this->suscripcion($cliente, $membresia, [
            'fecha_fin' => now()->subDay()->toDateString(),
            'auto_renovacion' => false,
        ]);

        $this->artisan('membresias:procesar')->assertSuccessful();

        $this->assertSame(MembresiaEstado::Vencida, MembresiaCliente::first()->estado);
        $this->assertSame(ClienteEstado::Inactivo, $cliente->fresh()->estado);
        Notification::assertSentTo($cliente->user, SuscripcionFinalizada::class);
    }

    // ---- Recordatorios previos -------------------------------------------

    public function test_procesar_envia_recordatorio_previo_segun_auto_renovacion(): void
    {
        Notification::fake();
        $membresia = Membresia::factory()->create(['duracion_dias' => 30, 'renovable' => true]);

        $conAuto = Cliente::factory()->create(['estado' => ClienteEstado::Activo]);
        $this->suscripcion($conAuto, $membresia, [
            'fecha_fin' => now()->addDays(2)->toDateString(),
            'auto_renovacion' => true,
        ]);

        $sinAuto = Cliente::factory()->create(['estado' => ClienteEstado::Activo]);
        $this->suscripcion($sinAuto, $membresia, [
            'fecha_fin' => now()->addDays(2)->toDateString(),
            'auto_renovacion' => false,
        ]);

        $this->artisan('membresias:procesar')->assertSuccessful();

        Notification::assertSentTo($conAuto->user, SuscripcionPorRenovar::class);
        Notification::assertSentTo($sinAuto->user, SuscripcionPorVencer::class);

        // No se reenvía en una segunda corrida.
        $this->artisan('membresias:procesar')->assertSuccessful();
        Notification::assertSentToTimes($conAuto->user, SuscripcionPorRenovar::class, 1);
        $this->assertNotNull(MembresiaCliente::where('cliente_id', $conAuto->id)->first()->recordatorio_enviado_at);
    }

    // ---- Beneficios en el plan -------------------------------------------

    public function test_el_plan_guarda_beneficios_incluidos(): void
    {
        $producto = Producto::factory()->create(['activo' => true]);
        $servicio = Servicio::factory()->create(['activo' => true]);

        $this->actingAs($this->cajero())->post(route('membresias.store'), [
            'nombre' => 'Anual',
            'precio' => 6000,
            'duracion_dias' => 365,
            'activa' => '1',
            'renovable' => '1',
            'beneficio_productos' => [$producto->id],
            'beneficio_servicios' => [$servicio->id],
        ])->assertRedirect(route('membresias.index'));

        $membresia = Membresia::where('nombre', 'Anual')->first();
        $this->assertSame(2, $membresia->beneficios()->count());
        $this->assertTrue($membresia->beneficios()->where('beneficiable_type', 'producto')->exists());
        $this->assertTrue($membresia->beneficios()->where('beneficiable_type', 'servicio')->exists());
    }

    public function test_un_admin_gestiona_membresias_un_entrenador_no(): void
    {
        $admin = $this->empleadoCon(Puesto::ADMIN);
        $entrenador = $this->empleadoCon(Puesto::ENTRENADOR);

        $this->actingAs($admin)->get(route('membresias.index'))->assertOk();
        $this->actingAs($admin)->get(route('membresias.create'))->assertOk();
        $this->actingAs($this->cajero())->get(route('membresias.index'))->assertOk();
        $this->actingAs($entrenador)->get(route('membresias.index'))->assertForbidden();
    }

    // ---- Canje de beneficios ---------------------------------------------

    public function test_entregar_beneficio_incluido_descuenta_stock(): void
    {
        $cajero = $this->cajero();
        $cliente = Cliente::factory()->create(['estado' => ClienteEstado::Activo]);
        $producto = Producto::factory()->create(['stock' => 5, 'activo' => true]);
        $membresia = Membresia::factory()->create(['renovable' => true]);
        $membresia->beneficios()->create(['beneficiable_type' => 'producto', 'beneficiable_id' => $producto->id]);

        $sub = $this->suscripcion($cliente, $membresia, ['fecha_fin' => now()->addDays(10)->toDateString()]);

        $this->actingAs($cajero)->post(route('canjes.store', $sub), [
            'beneficiable_type' => 'producto',
            'beneficiable_id' => $producto->id,
        ])->assertRedirect();

        $this->assertSame(4, $producto->fresh()->stock);
        $this->assertSame(1, $sub->canjes()->count());
    }

    public function test_no_se_entrega_beneficio_no_incluido(): void
    {
        $cajero = $this->cajero();
        $cliente = Cliente::factory()->create(['estado' => ClienteEstado::Activo]);
        $producto = Producto::factory()->create(['stock' => 5, 'activo' => true]);
        $membresia = Membresia::factory()->create();
        $sub = $this->suscripcion($cliente, $membresia, ['fecha_fin' => now()->addDays(10)->toDateString()]);

        $this->actingAs($cajero)->post(route('canjes.store', $sub), [
            'beneficiable_type' => 'producto',
            'beneficiable_id' => $producto->id,
        ])->assertSessionHasErrors('beneficio');

        $this->assertSame(5, $producto->fresh()->stock);
        $this->assertSame(0, $sub->canjes()->count());
    }

    public function test_no_se_entrega_beneficio_si_la_suscripcion_no_esta_vigente(): void
    {
        $cajero = $this->cajero();
        $cliente = Cliente::factory()->create(['estado' => ClienteEstado::Inactivo]);
        $producto = Producto::factory()->create(['stock' => 5, 'activo' => true]);
        $membresia = Membresia::factory()->create();
        $membresia->beneficios()->create(['beneficiable_type' => 'producto', 'beneficiable_id' => $producto->id]);
        $sub = $this->suscripcion($cliente, $membresia, [
            'fecha_fin' => now()->subDay()->toDateString(),
            'estado' => MembresiaEstado::Vencida,
        ]);

        $this->actingAs($cajero)->post(route('canjes.store', $sub), [
            'beneficiable_type' => 'producto',
            'beneficiable_id' => $producto->id,
        ])->assertSessionHasErrors('beneficio');

        $this->assertSame(5, $producto->fresh()->stock);
    }

    // ---- Centro de notificaciones ----------------------------------------

    public function test_el_centro_de_notificaciones_carga(): void
    {
        $this->actingAs($this->cajero())->get(route('notificaciones.index'))->assertOk();
    }
}
