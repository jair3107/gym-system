<?php

namespace Tests\Feature;

use App\Enums\ClienteEstado;
use App\Enums\MembresiaEstado;
use App\Enums\MetodoPago;
use App\Enums\TipoTarjeta;
use App\Enums\UserRole;
use App\Models\Cliente;
use App\Models\Membresia;
use App\Models\MembresiaCliente;
use App\Models\Puesto;
use App\Models\Tarjeta;
use App\Models\User;
use App\Models\Venta;
use App\Notifications\SuscripcionFinalizada;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TarjetaTest extends TestCase
{
    use RefreshDatabase;

    private function cajero(): User
    {
        $user = User::factory()->create(['role' => UserRole::Empleado]);
        $puesto = Puesto::firstOrCreate(['slug' => Puesto::CAJERO], ['nombre' => 'Cajero']);
        $user->puestos()->attach($puesto);

        return $user;
    }

    private function tarjeta(Cliente $cliente, array $overrides = []): Tarjeta
    {
        return $cliente->tarjetas()->create(array_merge([
            'titular' => 'Test',
            'marca' => 'visa',
            'tipo' => TipoTarjeta::Debito,
            'ultimos4' => '4242',
            'mes_exp' => 12,
            'anio_exp' => (int) now()->addYears(3)->format('Y'),
            'primaria' => true,
        ], $overrides));
    }

    // ---- Alta y primaria automática --------------------------------------

    public function test_la_primera_tarjeta_queda_primaria_y_no_guarda_el_numero(): void
    {
        $cajero = $this->cajero();
        $cliente = Cliente::factory()->create();

        $this->actingAs($cajero)->post(route('tarjetas.store', $cliente), [
            'titular' => 'Juan Pérez',
            'numero' => '4242 4242 4242 4242',
            'tipo' => TipoTarjeta::Debito->value,
            'mes_exp' => 12,
            'anio_exp' => (int) now()->addYears(2)->format('Y'),
            'cvv' => '123',
        ])->assertRedirect();

        $tarjeta = Tarjeta::first();
        $this->assertNotNull($tarjeta);
        $this->assertTrue($tarjeta->primaria);
        $this->assertSame('4242', $tarjeta->ultimos4);
        $this->assertSame('visa', $tarjeta->marca);
        // No existe columna con el número completo ni el CVV.
        $this->assertArrayNotHasKey('numero', $tarjeta->getAttributes());
        $this->assertArrayNotHasKey('cvv', $tarjeta->getAttributes());
    }

    public function test_la_segunda_tarjeta_no_es_primaria_hasta_marcarla(): void
    {
        $cajero = $this->cajero();
        $cliente = Cliente::factory()->create();
        $primera = $this->tarjeta($cliente);

        $this->actingAs($cajero)->post(route('tarjetas.store', $cliente), [
            'titular' => 'Otra',
            'numero' => '5555555555554444',
            'tipo' => TipoTarjeta::Credito->value,
            'mes_exp' => 6,
            'anio_exp' => (int) now()->addYears(2)->format('Y'),
        ])->assertRedirect();

        $segunda = Tarjeta::where('id', '!=', $primera->id)->first();
        $this->assertFalse($segunda->primaria);
        $this->assertSame('mastercard', $segunda->marca);

        // Al marcarla primaria, la anterior deja de serlo.
        $this->actingAs($cajero)->patch(route('tarjetas.primaria', $segunda))->assertRedirect();
        $this->assertTrue($segunda->fresh()->primaria);
        $this->assertFalse($primera->fresh()->primaria);
    }

    public function test_al_eliminar_la_primaria_se_promueve_otra(): void
    {
        $cajero = $this->cajero();
        $cliente = Cliente::factory()->create();
        $primaria = $this->tarjeta($cliente, ['primaria' => true, 'ultimos4' => '1111']);
        $otra = $this->tarjeta($cliente, ['primaria' => false, 'ultimos4' => '2222']);

        $this->actingAs($cajero)->delete(route('tarjetas.destroy', $primaria))->assertRedirect();

        $this->assertDatabaseMissing('tarjetas', ['id' => $primaria->id]);
        $this->assertTrue($otra->fresh()->primaria);
    }

    public function test_rechaza_numero_invalido_y_tarjeta_vencida(): void
    {
        $cajero = $this->cajero();
        $cliente = Cliente::factory()->create();

        // Luhn inválido.
        $this->actingAs($cajero)->post(route('tarjetas.store', $cliente), [
            'titular' => 'X', 'numero' => '4242424242424241',
            'tipo' => TipoTarjeta::Debito->value, 'mes_exp' => 12, 'anio_exp' => (int) now()->addYears(2)->format('Y'),
        ])->assertSessionHasErrors('numero');

        // Vencida.
        $this->actingAs($cajero)->post(route('tarjetas.store', $cliente), [
            'titular' => 'X', 'numero' => '4242424242424242',
            'tipo' => TipoTarjeta::Debito->value, 'mes_exp' => 1, 'anio_exp' => 2020,
        ])->assertSessionHasErrors('anio_exp');

        $this->assertSame(0, Tarjeta::count());
    }

    // ---- Gating de auto-renovación ---------------------------------------

    public function test_no_activa_auto_renovacion_en_venta_sin_tarjeta_primaria(): void
    {
        $cajero = $this->cajero();
        $cliente = Cliente::factory()->create(['estado' => ClienteEstado::Inactivo]);
        $membresia = Membresia::factory()->create(['duracion_dias' => 30, 'activa' => true, 'renovable' => true]);

        $this->actingAs($cajero)->post(route('ventas.store'), [
            'cliente_id' => $cliente->id,
            'metodo_pago' => MetodoPago::Efectivo->value,
            'auto_renovacion' => '1',
            'items' => [['tipo' => 'membresia', 'id' => $membresia->id, 'cantidad' => 1]],
        ])->assertSessionHasErrors('auto_renovacion');

        $this->assertSame(0, Venta::count());
    }

    public function test_activa_auto_renovacion_con_tarjeta_primaria_y_guarda_la_tarjeta(): void
    {
        $cajero = $this->cajero();
        $cliente = Cliente::factory()->create(['estado' => ClienteEstado::Inactivo]);
        $tarjeta = $this->tarjeta($cliente);
        $membresia = Membresia::factory()->create(['duracion_dias' => 30, 'activa' => true, 'renovable' => true]);

        $this->actingAs($cajero)->post(route('ventas.store'), [
            'cliente_id' => $cliente->id,
            'metodo_pago' => MetodoPago::Efectivo->value,
            'auto_renovacion' => '1',
            'items' => [['tipo' => 'membresia', 'id' => $membresia->id, 'cantidad' => 1]],
        ])->assertRedirect();

        $sub = MembresiaCliente::first();
        $this->assertTrue($sub->auto_renovacion);
        $this->assertSame($tarjeta->id, $sub->tarjeta_id);
        $this->assertSame(MetodoPago::Tarjeta, $sub->metodo_pago);
    }

    public function test_toggle_auto_renovacion_requiere_tarjeta_primaria(): void
    {
        $cajero = $this->cajero();
        $cliente = Cliente::factory()->create(['estado' => ClienteEstado::Activo]);
        $membresia = Membresia::factory()->create(['renovable' => true]);
        $sub = $cliente->membresiasCliente()->create([
            'membresia_id' => $membresia->id,
            'fecha_inicio' => now()->subDay()->toDateString(),
            'fecha_fin' => now()->addDays(20)->toDateString(),
            'estado' => MembresiaEstado::Activa,
            'auto_renovacion' => false,
        ]);

        // Sin tarjeta → error.
        $this->actingAs($cajero)->patch(route('suscripciones.auto-renovacion', $sub), ['auto_renovacion' => '1'])
            ->assertSessionHasErrors('auto_renovacion');
        $this->assertFalse($sub->fresh()->auto_renovacion);

        // Con tarjeta → se activa.
        $tarjeta = $this->tarjeta($cliente);
        $this->actingAs($cajero)->patch(route('suscripciones.auto-renovacion', $sub), ['auto_renovacion' => '1'])
            ->assertRedirect();
        $this->assertTrue($sub->fresh()->auto_renovacion);
        $this->assertSame($tarjeta->id, $sub->fresh()->tarjeta_id);
    }

    public function test_el_motor_finaliza_si_no_hay_tarjeta_primaria_aunque_tenga_auto(): void
    {
        Notification::fake();
        $cliente = Cliente::factory()->create(['estado' => ClienteEstado::Activo]);
        $membresia = Membresia::factory()->create(['renovable' => true]);

        // Suscripción con auto-renovación pero el cliente ya no tiene tarjeta primaria.
        $cliente->membresiasCliente()->create([
            'membresia_id' => $membresia->id,
            'fecha_inicio' => now()->subDays(31)->toDateString(),
            'fecha_fin' => now()->subDay()->toDateString(),
            'estado' => MembresiaEstado::Activa,
            'auto_renovacion' => true,
        ]);

        $this->artisan('membresias:procesar')->assertSuccessful();

        $this->assertSame(MembresiaEstado::Vencida, MembresiaCliente::first()->estado);
        $this->assertSame(ClienteEstado::Inactivo, $cliente->fresh()->estado);
        Notification::assertSentTo($cliente->user, SuscripcionFinalizada::class);
    }
}
