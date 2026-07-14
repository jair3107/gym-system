<?php

namespace Tests\Feature;

use App\Enums\EmpleadoEstado;
use App\Enums\UserRole;
use App\Models\Empleado;
use App\Models\Puesto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmpleadoTest extends TestCase
{
    use RefreshDatabase;

    private function superadmin(): User
    {
        return User::factory()->create(['role' => UserRole::SuperAdmin]);
    }

    private function empleadoAdmin(): User
    {
        $user = User::factory()->create(['role' => UserRole::Empleado]);
        $user->puestos()->attach(Puesto::create(['nombre' => 'Administrador', 'slug' => Puesto::ADMIN]));

        return $user;
    }

    public function test_un_empleado_sin_puesto_admin_no_puede_gestionar_empleados(): void
    {
        $empleado = User::factory()->create(['role' => UserRole::Empleado]);

        $this->actingAs($empleado)->get(route('empleados.index'))->assertForbidden();
    }

    public function test_el_superadmin_puede_ver_el_listado(): void
    {
        Empleado::factory()->count(2)->create();

        $this->actingAs($this->superadmin())
            ->get(route('empleados.index'))
            ->assertOk()
            ->assertViewIs('empleados.index');
    }

    public function test_un_empleado_con_puesto_admin_puede_ver_el_listado(): void
    {
        $this->actingAs($this->empleadoAdmin())
            ->get(route('empleados.index'))
            ->assertOk();
    }

    public function test_el_superadmin_puede_registrar_un_empleado_con_puestos(): void
    {
        $entrenador = Puesto::create(['nombre' => 'Entrenador', 'slug' => Puesto::ENTRENADOR]);
        $cajero = Puesto::create(['nombre' => 'Cajero', 'slug' => Puesto::CAJERO]);

        $this->actingAs($this->superadmin())->post(route('empleados.store'), [
            'name' => 'Carla Ruiz',
            'email' => 'carla@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'fecha_contratacion' => now()->format('Y-m-d'),
            'salario' => 12000,
            'estado' => EmpleadoEstado::Activo->value,
            'puestos' => [$entrenador->id, $cajero->id],
        ]);

        $user = User::where('email', 'carla@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame(UserRole::Empleado, $user->role);
        $this->assertDatabaseHas('empleados', ['user_id' => $user->id, 'salario' => 12000]);
        $this->assertEqualsCanonicalizing(
            [$entrenador->id, $cajero->id],
            $user->puestos->pluck('id')->all(),
        );
    }

    public function test_las_pantallas_de_empleado_y_puestos_cargan(): void
    {
        $empleado = Empleado::factory()->create();
        $admin = $this->superadmin();

        $this->actingAs($admin)->get(route('empleados.create'))->assertOk();
        $this->actingAs($admin)->get(route('empleados.show', $empleado))->assertOk();
        $this->actingAs($admin)->get(route('empleados.edit', $empleado))->assertOk();
        $this->actingAs($admin)->get(route('puestos.index'))->assertOk();
        $this->actingAs($admin)->get(route('puestos.create'))->assertOk();
    }

    public function test_los_puestos_son_obligatorios(): void
    {
        $this->actingAs($this->superadmin())
            ->post(route('empleados.store'), [
                'name' => 'Sin Puesto',
                'email' => 'sinpuesto@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'fecha_contratacion' => now()->format('Y-m-d'),
                'estado' => EmpleadoEstado::Activo->value,
            ])
            ->assertSessionHasErrors('puestos');
    }

    public function test_eliminar_un_empleado_borra_su_cuenta_y_puestos(): void
    {
        $empleado = Empleado::factory()->create();
        $userId = $empleado->user_id;
        $empleado->user->puestos()->attach(Puesto::create(['nombre' => 'Entrenador', 'slug' => Puesto::ENTRENADOR]));

        $this->actingAs($this->superadmin())
            ->delete(route('empleados.destroy', $empleado))
            ->assertRedirect(route('empleados.index'));

        $this->assertDatabaseMissing('users', ['id' => $userId]);
        $this->assertDatabaseMissing('empleado_puesto', ['user_id' => $userId]);
    }
}
