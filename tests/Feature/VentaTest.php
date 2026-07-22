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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VentaTest extends TestCase
{
    use RefreshDatabase;

    private function superadmin(): User
    {
        return User::factory()->create(['role' => UserRole::SuperAdmin]);
    }

    private function cajero(): User
    {
        $user = User::factory()->create(['role' => UserRole::Empleado]);
        $puesto = Puesto::firstOrCreate(['slug' => Puesto::CAJERO], ['nombre' => 'Recepcionista / Cajero']);
        $user->puestos()->attach($puesto);

        return $user;
    }

    public function test_un_empleado_sin_puesto_cajero_no_puede_acceder_a_la_caja(): void
    {
        $empleado = User::factory()->create(['role' => UserRole::Empleado]);

        $this->actingAs($empleado)->get(route('ventas.index'))->assertForbidden();
        $this->actingAs($empleado)->get(route('membresias.index'))->assertForbidden();
    }

    public function test_las_pantallas_de_caja_cargan(): void
    {
        $cajero = $this->cajero();

        $this->actingAs($cajero)->get(route('ventas.index'))->assertOk();
        $this->actingAs($cajero)->get(route('ventas.create'))->assertOk();
        $this->actingAs($cajero)->get(route('membresias.index'))->assertOk();
        $this->actingAs($cajero)->get(route('productos.index'))->assertOk();
        $this->actingAs($cajero)->get(route('servicios.index'))->assertOk();
    }

    public function test_registrar_venta_de_producto_descuenta_stock(): void
    {
        $cajero = $this->cajero();
        $producto = Producto::factory()->create(['precio' => 100, 'stock' => 10, 'activo' => true]);

        $this->actingAs($cajero)->post(route('ventas.store'), [
            'metodo_pago' => MetodoPago::Efectivo->value,
            'items' => [
                ['tipo' => 'producto', 'id' => $producto->id, 'cantidad' => 3],
            ],
        ])->assertRedirect();

        $venta = Venta::first();
        $this->assertNotNull($venta);
        $this->assertSame($cajero->id, $venta->cajero_id);
        $this->assertEquals(300, $venta->total);
        $this->assertSame(7, $producto->fresh()->stock);
        $this->assertDatabaseHas('venta_items', [
            'venta_id' => $venta->id,
            'vendible_type' => 'producto',
            'vendible_id' => $producto->id,
            'cantidad' => 3,
            'subtotal' => 300,
        ]);
    }

    public function test_no_se_puede_vender_mas_stock_del_disponible(): void
    {
        $cajero = $this->cajero();
        $producto = Producto::factory()->create(['precio' => 100, 'stock' => 2, 'activo' => true]);

        $this->actingAs($cajero)->post(route('ventas.store'), [
            'metodo_pago' => MetodoPago::Efectivo->value,
            'items' => [
                ['tipo' => 'producto', 'id' => $producto->id, 'cantidad' => 5],
            ],
        ])->assertSessionHasErrors('items');

        $this->assertSame(0, Venta::count());
        $this->assertSame(2, $producto->fresh()->stock);
    }

    public function test_el_total_se_calcula_con_el_precio_del_servidor(): void
    {
        $cajero = $this->cajero();
        $servicio = Servicio::factory()->create(['precio' => 250, 'activo' => true]);

        $this->actingAs($cajero)->post(route('ventas.store'), [
            'metodo_pago' => MetodoPago::Tarjeta->value,
            // Aunque el cliente enviara un precio, no se acepta: manda el catálogo.
            'items' => [
                ['tipo' => 'servicio', 'id' => $servicio->id, 'cantidad' => 2, 'precio_unitario' => 1],
            ],
        ])->assertRedirect();

        $this->assertEquals(500, Venta::first()->total);
    }

    public function test_vender_membresia_crea_la_vigencia_y_activa_al_cliente(): void
    {
        $cajero = $this->cajero();
        $cliente = Cliente::factory()->create(['estado' => ClienteEstado::Inactivo]);
        $membresia = Membresia::factory()->create(['precio' => 650, 'duracion_dias' => 30, 'activa' => true]);

        $this->actingAs($cajero)->post(route('ventas.store'), [
            'cliente_id' => $cliente->id,
            'metodo_pago' => MetodoPago::Efectivo->value,
            'items' => [
                ['tipo' => 'membresia', 'id' => $membresia->id, 'cantidad' => 1],
            ],
        ])->assertRedirect();

        $vigencia = MembresiaCliente::first();
        $this->assertNotNull($vigencia);
        $this->assertSame($cliente->id, $vigencia->cliente_id);
        $this->assertSame(MembresiaEstado::Activa, $vigencia->estado);
        $this->assertSame(
            now()->startOfDay()->addDays(30)->toDateString(),
            $vigencia->fecha_fin->toDateString(),
        );
        $this->assertSame(ClienteEstado::Activo, $cliente->fresh()->estado);
    }

    public function test_no_se_puede_vender_membresia_sin_cliente(): void
    {
        $cajero = $this->cajero();
        $membresia = Membresia::factory()->create(['duracion_dias' => 30, 'activa' => true]);

        $this->actingAs($cajero)->post(route('ventas.store'), [
            'metodo_pago' => MetodoPago::Efectivo->value,
            'items' => [
                ['tipo' => 'membresia', 'id' => $membresia->id, 'cantidad' => 1],
            ],
        ])->assertSessionHasErrors('cliente_id');

        $this->assertSame(0, Venta::count());
        $this->assertSame(0, MembresiaCliente::count());
    }

    public function test_una_venta_requiere_al_menos_un_concepto(): void
    {
        $this->actingAs($this->cajero())->post(route('ventas.store'), [
            'metodo_pago' => MetodoPago::Efectivo->value,
        ])->assertSessionHasErrors('items');
    }

    public function test_el_cajero_solo_ve_sus_ventas_y_el_dueno_todas(): void
    {
        $cajero = $this->cajero();
        $otro = $this->cajero();
        $propia = Venta::factory()->create(['cajero_id' => $cajero->id]);
        $ajena = Venta::factory()->create(['cajero_id' => $otro->id]);

        // No puede ver la ajena.
        $this->actingAs($cajero)->get(route('ventas.show', $ajena))->assertForbidden();
        $this->actingAs($cajero)->get(route('ventas.show', $propia))->assertOk();

        // El dueño accede a cualquiera.
        $this->actingAs($this->superadmin())->get(route('ventas.show', $ajena))->assertOk();
    }

    public function test_cancelar_venta_reintegra_stock_y_anula_membresia(): void
    {
        $cajero = $this->cajero();
        $cliente = Cliente::factory()->create(['estado' => ClienteEstado::Inactivo]);
        $producto = Producto::factory()->create(['precio' => 100, 'stock' => 10, 'activo' => true]);
        $membresia = Membresia::factory()->create(['precio' => 650, 'duracion_dias' => 30, 'activa' => true]);

        $this->actingAs($cajero)->post(route('ventas.store'), [
            'cliente_id' => $cliente->id,
            'metodo_pago' => MetodoPago::Efectivo->value,
            'items' => [
                ['tipo' => 'membresia', 'id' => $membresia->id, 'cantidad' => 1],
                ['tipo' => 'producto', 'id' => $producto->id, 'cantidad' => 2],
            ],
        ])->assertRedirect();

        $venta = Venta::first();
        $this->assertSame(8, $producto->fresh()->stock);
        $this->assertSame(ClienteEstado::Activo, $cliente->fresh()->estado);

        $this->actingAs($cajero)->delete(route('ventas.destroy', $venta))
            ->assertRedirect(route('ventas.index'));

        $this->assertDatabaseMissing('ventas', ['id' => $venta->id]);
        $this->assertSame(10, $producto->fresh()->stock);
        $this->assertSame(MembresiaEstado::Cancelada, MembresiaCliente::first()->estado);
        $this->assertSame(ClienteEstado::Inactivo, $cliente->fresh()->estado);
    }
}
