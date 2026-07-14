<?php

namespace Tests\Feature;

use App\Enums\ClienteEstado;
use App\Enums\UserRole;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteTest extends TestCase
{
    use RefreshDatabase;

    private function staff(): User
    {
        return User::factory()->create(['role' => UserRole::Empleado]);
    }

    public function test_un_cliente_no_puede_acceder_a_la_gestion(): void
    {
        $cliente = User::factory()->create(['role' => UserRole::Cliente]);

        $this->actingAs($cliente)->get(route('clientes.index'))->assertForbidden();
    }

    public function test_el_staff_ve_el_listado_de_clientes(): void
    {
        Cliente::factory()->count(3)->create();

        $this->actingAs($this->staff())
            ->get(route('clientes.index'))
            ->assertOk()
            ->assertViewIs('clientes.index');
    }

    public function test_el_staff_puede_registrar_un_cliente_con_su_cuenta(): void
    {
        $response = $this->actingAs($this->staff())->post(route('clientes.store'), [
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

        $this->actingAs($this->staff())
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

    public function test_el_staff_puede_actualizar_un_cliente(): void
    {
        $cliente = Cliente::factory()->create(['telefono' => '000']);

        $this->actingAs($this->staff())
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

        $this->actingAs($this->staff())
            ->delete(route('clientes.destroy', $cliente))
            ->assertRedirect(route('clientes.index'));

        $this->assertDatabaseMissing('users', ['id' => $userId]);
        $this->assertDatabaseMissing('clientes', ['id' => $cliente->id]);
    }
}
