<?php

namespace Tests\Feature;

use App\Enums\ClienteEstado;
use App\Enums\UserRole;
use App\Models\Cliente;
use App\Models\Puesto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Empleado que SÍ puede gestionar clientes (recepción/cajero).
     */
    private function gerente(): User
    {
        $user = User::factory()->create(['role' => UserRole::Empleado]);
        $user->puestos()->attach(Puesto::firstOrCreate(['slug' => Puesto::CAJERO], ['nombre' => 'Recepcionista / Cajero']));

        return $user;
    }

    private function entrenador(): User
    {
        $user = User::factory()->create(['role' => UserRole::Empleado]);
        $user->puestos()->attach(Puesto::firstOrCreate(['slug' => Puesto::ENTRENADOR], ['nombre' => 'Entrenador']));

        return $user;
    }

    private function nutriologo(): User
    {
        $user = User::factory()->create(['role' => UserRole::Empleado]);
        $user->puestos()->attach(Puesto::firstOrCreate(['slug' => Puesto::NUTRIOLOGO], ['nombre' => 'Nutriólogo']));

        return $user;
    }

    public function test_un_cliente_no_puede_acceder_a_la_gestion(): void
    {
        $cliente = User::factory()->create(['role' => UserRole::Cliente]);

        $this->actingAs($cliente)->get(route('clientes.index'))->assertForbidden();
    }

    public function test_un_empleado_sin_permiso_no_puede_dar_de_alta_clientes(): void
    {
        $entrenador = $this->entrenador();

        $this->actingAs($entrenador)->get(route('clientes.create'))->assertForbidden();

        $this->actingAs($entrenador)->post(route('clientes.store'), [
            'name' => 'X',
            'email' => 'x@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'fecha_ingreso' => now()->format('Y-m-d'),
            'estado' => ClienteEstado::Activo->value,
        ])->assertForbidden();
    }

    public function test_el_gerente_ve_el_listado_de_clientes(): void
    {
        Cliente::factory()->count(3)->create();

        $this->actingAs($this->gerente())
            ->get(route('clientes.index'))
            ->assertOk()
            ->assertViewIs('clientes.index');
    }

    public function test_los_formularios_de_cliente_cargan(): void
    {
        $cliente = Cliente::factory()->create();
        $gerente = $this->gerente();

        $this->actingAs($gerente)->get(route('clientes.create'))->assertOk();
        $this->actingAs($gerente)->get(route('clientes.show', $cliente))->assertOk();
        $this->actingAs($gerente)->get(route('clientes.edit', $cliente))->assertOk();
    }

    public function test_el_gerente_puede_registrar_un_cliente_con_su_cuenta(): void
    {
        $response = $this->actingAs($this->gerente())->post(route('clientes.store'), [
            'name' => 'Ana Pérez',
            'email' => 'ana@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'telefono' => '555-1234',
            'fecha_ingreso' => now()->format('Y-m-d'),
            'estado' => ClienteEstado::Activo->value,
        ]);

        $user = User::where('email', 'ana@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame(UserRole::Cliente, $user->role);
        $this->assertDatabaseHas('clientes', ['user_id' => $user->id, 'telefono' => '555-1234']);

        $response->assertRedirect(route('clientes.show', $user->cliente));
    }

    public function test_el_email_del_cliente_debe_ser_unico(): void
    {
        User::factory()->create(['email' => 'dup@example.com']);

        $this->actingAs($this->gerente())
            ->post(route('clientes.store'), [
                'name' => 'Dup',
                'email' => 'dup@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'fecha_ingreso' => now()->format('Y-m-d'),
                'estado' => ClienteEstado::Activo->value,
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_el_gerente_puede_actualizar_un_cliente(): void
    {
        $cliente = Cliente::factory()->create(['telefono' => '000']);

        $this->actingAs($this->gerente())
            ->put(route('clientes.update', $cliente), [
                'name' => $cliente->user->name,
                'email' => $cliente->user->email,
                'password' => '',
                'telefono' => '999-8888',
                'fecha_ingreso' => $cliente->fecha_ingreso->format('Y-m-d'),
                'estado' => ClienteEstado::Suspendido->value,
            ])
            ->assertRedirect(route('clientes.show', $cliente));

        $this->assertDatabaseHas('clientes', [
            'id' => $cliente->id,
            'telefono' => '999-8888',
            'estado' => ClienteEstado::Suspendido->value,
        ]);
    }

    public function test_eliminar_un_cliente_borra_su_cuenta(): void
    {
        $cliente = Cliente::factory()->create();
        $userId = $cliente->user_id;

        $this->actingAs($this->gerente())
            ->delete(route('clientes.destroy', $cliente))
            ->assertRedirect(route('clientes.index'));

        $this->assertDatabaseMissing('users', ['id' => $userId]);
        $this->assertDatabaseMissing('clientes', ['id' => $cliente->id]);
    }

    public function test_no_se_puede_asignar_como_entrenador_a_alguien_sin_ese_puesto(): void
    {
        // Empleado con puesto nutriólogo (no entrenador).
        $empleado = User::factory()->create(['role' => UserRole::Empleado]);
        $empleado->puestos()->attach(Puesto::create(['nombre' => 'Nutriólogo', 'slug' => Puesto::NUTRIOLOGO]));

        $this->actingAs($this->gerente())
            ->post(route('clientes.store'), [
                'name' => 'Test',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'fecha_ingreso' => now()->format('Y-m-d'),
                'estado' => ClienteEstado::Activo->value,
                'entrenador_id' => $empleado->id,
            ])
            ->assertSessionHasErrors('entrenador_id');
    }

    public function test_se_puede_asignar_un_entrenador_con_el_puesto_correcto(): void
    {
        $entrenador = $this->entrenador();

        $this->actingAs($this->gerente())
            ->post(route('clientes.store'), [
                'name' => 'Test',
                'email' => 'test2@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'fecha_ingreso' => now()->format('Y-m-d'),
                'estado' => ClienteEstado::Activo->value,
                'entrenador_id' => $entrenador->id,
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('clientes', ['entrenador_id' => $entrenador->id]);
    }

    public function test_el_entrenador_solo_ve_sus_alumnos_y_los_sin_asignar(): void
    {
        $e1 = $this->entrenador();
        $e2 = $this->entrenador();

        $mio = Cliente::factory()->create(['entrenador_id' => $e1->id]);
        $ajeno = Cliente::factory()->create(['entrenador_id' => $e2->id]);
        $libre = Cliente::factory()->create(['entrenador_id' => null]);

        $this->actingAs($e1)
            ->get(route('clientes.index'))
            ->assertSee($mio->user->email)
            ->assertSee($libre->user->email)
            ->assertDontSee($ajeno->user->email);
    }

    public function test_el_nutriologo_solo_ve_sus_pacientes_y_los_sin_asignar(): void
    {
        $n1 = $this->nutriologo();
        $n2 = $this->nutriologo();

        $mio = Cliente::factory()->create(['nutriologo_id' => $n1->id]);
        $ajeno = Cliente::factory()->create(['nutriologo_id' => $n2->id]);
        $libre = Cliente::factory()->create(['nutriologo_id' => null]);

        $this->actingAs($n1)
            ->get(route('clientes.index'))
            ->assertSee($mio->user->email)
            ->assertSee($libre->user->email)
            ->assertDontSee($ajeno->user->email);
    }

    public function test_el_entrenador_no_puede_ver_la_ficha_de_un_alumno_ajeno(): void
    {
        $e1 = $this->entrenador();
        $e2 = $this->entrenador();
        $ajeno = Cliente::factory()->create(['entrenador_id' => $e2->id]);

        $this->actingAs($e1)->get(route('clientes.show', $ajeno))->assertForbidden();
    }

    public function test_el_entrenador_puede_autoasignarse_un_cliente_sin_entrenador(): void
    {
        $entrenador = $this->entrenador();
        $cliente = Cliente::factory()->create(['entrenador_id' => null]);

        $this->actingAs($entrenador)
            ->post(route('clientes.autoasignar', $cliente))
            ->assertRedirect();

        $this->assertDatabaseHas('clientes', [
            'id' => $cliente->id,
            'entrenador_id' => $entrenador->id,
        ]);
    }

    public function test_autoasignar_no_pisa_una_asignacion_existente(): void
    {
        $e1 = $this->entrenador();
        $e2 = $this->entrenador();
        $cliente = Cliente::factory()->create(['entrenador_id' => $e1->id]);

        $this->actingAs($e2)->post(route('clientes.autoasignar', $cliente))->assertRedirect();

        $this->assertDatabaseHas('clientes', [
            'id' => $cliente->id,
            'entrenador_id' => $e1->id,
        ]);
    }
}
