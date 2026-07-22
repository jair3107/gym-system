<?php

namespace Tests\Feature;

use App\Enums\ClienteEstado;
use App\Enums\MembresiaEstado;
use App\Enums\UserRole;
use App\Models\Cliente;
use App\Models\Membresia;
use App\Models\MembresiaCliente;
use App\Models\Producto;
use App\Models\Puesto;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CatalogoVentasTest extends TestCase
{
    use RefreshDatabase;

    private function cajero(): User
    {
        $user = User::factory()->create(['role' => UserRole::Empleado]);
        $puesto = Puesto::firstOrCreate(['slug' => Puesto::CAJERO], ['nombre' => 'Recepcionista / Cajero']);
        $user->puestos()->attach($puesto);

        return $user;
    }

    public function test_el_cajero_puede_crear_una_membresia(): void
    {
        $this->actingAs($this->cajero())->post(route('membresias.store'), [
            'nombre' => 'Mensualidad',
            'precio' => 650,
            'duracion_dias' => 30,
            'activa' => '1',
        ])->assertRedirect(route('membresias.index'));

        $this->assertDatabaseHas('membresias', ['nombre' => 'Mensualidad', 'duracion_dias' => 30]);
    }

    public function test_la_membresia_requiere_nombre_precio_y_duracion(): void
    {
        $this->actingAs($this->cajero())
            ->post(route('membresias.store'), ['nombre' => ''])
            ->assertSessionHasErrors(['nombre', 'precio', 'duracion_dias']);
    }

    public function test_el_cajero_puede_crear_un_producto_con_stock(): void
    {
        $this->actingAs($this->cajero())->post(route('productos.store'), [
            'nombre' => 'Proteína',
            'precio' => 720,
            'stock' => 25,
            'activo' => '1',
        ])->assertRedirect(route('productos.index'));

        $this->assertDatabaseHas('productos', ['nombre' => 'Proteína', 'stock' => 25]);
    }

    public function test_el_cajero_puede_crear_un_servicio(): void
    {
        $this->actingAs($this->cajero())->post(route('servicios.store'), [
            'nombre' => 'Evaluación física',
            'precio' => 300,
            'activo' => '1',
        ])->assertRedirect(route('servicios.index'));

        $this->assertDatabaseHas('servicios', ['nombre' => 'Evaluación física']);
    }

    public function test_un_empleado_sin_puesto_cajero_no_puede_crear_catalogo(): void
    {
        $empleado = User::factory()->create(['role' => UserRole::Empleado]);

        $this->actingAs($empleado)->post(route('productos.store'), [
            'nombre' => 'X', 'precio' => 1, 'stock' => 1,
        ])->assertForbidden();
    }

    public function test_el_comando_procesar_vence_membresias_y_desactiva_clientes(): void
    {
        Notification::fake();
        $cliente = Cliente::factory()->create(['estado' => ClienteEstado::Activo]);
        $membresia = Membresia::factory()->create(['duracion_dias' => 30]);

        // Vigencia ya vencida, sin auto-renovación.
        MembresiaCliente::create([
            'cliente_id' => $cliente->id,
            'membresia_id' => $membresia->id,
            'fecha_inicio' => now()->subDays(40)->toDateString(),
            'fecha_fin' => now()->subDays(10)->toDateString(),
            'estado' => MembresiaEstado::Activa,
            'auto_renovacion' => false,
        ]);

        $this->artisan('membresias:procesar')->assertSuccessful();

        $this->assertSame(MembresiaEstado::Vencida, MembresiaCliente::first()->estado);
        $this->assertSame(ClienteEstado::Inactivo, $cliente->fresh()->estado);
    }

    public function test_el_comando_procesar_respeta_membresias_vigentes(): void
    {
        Notification::fake();
        $cliente = Cliente::factory()->create(['estado' => ClienteEstado::Activo]);
        $membresia = Membresia::factory()->create(['duracion_dias' => 30]);

        MembresiaCliente::create([
            'cliente_id' => $cliente->id,
            'membresia_id' => $membresia->id,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addDays(15)->toDateString(),
            'estado' => MembresiaEstado::Activa,
            'auto_renovacion' => false,
        ]);

        $this->artisan('membresias:procesar')->assertSuccessful();

        $this->assertSame(MembresiaEstado::Activa, MembresiaCliente::first()->estado);
        $this->assertSame(ClienteEstado::Activo, $cliente->fresh()->estado);
    }
}
