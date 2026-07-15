<?php

namespace Tests\Feature;

use App\Enums\SeguimientoEstado;
use App\Enums\UserRole;
use App\Models\Cliente;
use App\Models\Dieta;
use App\Models\Puesto;
use App\Models\SeguimientoNutricion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeguimientoNutricionTest extends TestCase
{
    use RefreshDatabase;

    private function superadmin(): User
    {
        return User::factory()->create(['role' => UserRole::SuperAdmin]);
    }

    private function nutriologo(): User
    {
        $user = User::factory()->create(['role' => UserRole::Empleado]);
        $puesto = Puesto::firstOrCreate(['slug' => Puesto::NUTRIOLOGO], ['nombre' => 'Nutriólogo']);
        $user->puestos()->attach($puesto);

        return $user;
    }

    private function pacienteDe(User $nutri): Cliente
    {
        return Cliente::factory()->create(['nutriologo_id' => $nutri->id]);
    }

    private function dietaDe(User $nutri): Dieta
    {
        return Dieta::factory()->create(['nutriologo_id' => $nutri->id, 'activa' => true]);
    }

    public function test_un_empleado_sin_puesto_nutriologo_no_puede_ver_seguimientos(): void
    {
        $empleado = User::factory()->create(['role' => UserRole::Empleado]);

        $this->actingAs($empleado)->get(route('seguimientos-nutricion.index'))->assertForbidden();
    }

    public function test_el_nutriologo_puede_ver_su_listado(): void
    {
        $this->actingAs($this->nutriologo())
            ->get(route('seguimientos-nutricion.index'))
            ->assertOk()
            ->assertViewIs('seguimientos-nutricion.index');
    }

    public function test_el_nutriologo_puede_crear_un_seguimiento_para_su_paciente(): void
    {
        $nutri = $this->nutriologo();
        $paciente = $this->pacienteDe($nutri);
        $dieta = $this->dietaDe($nutri);

        $this->actingAs($nutri)->post(route('seguimientos-nutricion.store'), [
            'cliente_id' => $paciente->id,
            'dieta_id' => $dieta->id,
            'fecha_inicio' => now()->format('Y-m-d'),
            'peso_inicial' => 82.5,
            'peso_meta' => 75,
            'estado' => SeguimientoEstado::Activo->value,
        ])->assertRedirect();

        $this->assertDatabaseHas('seguimientos_nutricion', [
            'cliente_id' => $paciente->id,
            'dieta_id' => $dieta->id,
            'nutriologo_id' => $nutri->id,
        ]);
    }

    public function test_no_puede_crear_seguimiento_para_paciente_ajeno(): void
    {
        $nutri = $this->nutriologo();
        $otro = $this->nutriologo();
        $pacienteAjeno = $this->pacienteDe($otro);

        $this->actingAs($nutri)->post(route('seguimientos-nutricion.store'), [
            'cliente_id' => $pacienteAjeno->id,
            'fecha_inicio' => now()->format('Y-m-d'),
            'estado' => SeguimientoEstado::Activo->value,
        ])->assertSessionHasErrors('cliente_id');
    }

    public function test_no_puede_asignar_dieta_ajena(): void
    {
        $nutri = $this->nutriologo();
        $paciente = $this->pacienteDe($nutri);
        $otro = $this->nutriologo();
        $dietaAjena = $this->dietaDe($otro);

        $this->actingAs($nutri)->post(route('seguimientos-nutricion.store'), [
            'cliente_id' => $paciente->id,
            'dieta_id' => $dietaAjena->id,
            'fecha_inicio' => now()->format('Y-m-d'),
            'estado' => SeguimientoEstado::Activo->value,
        ])->assertSessionHasErrors('dieta_id');
    }

    public function test_el_nutriologo_solo_accede_a_sus_seguimientos(): void
    {
        $otro = $this->nutriologo();
        $ajeno = SeguimientoNutricion::factory()->create([
            'nutriologo_id' => $otro->id,
            'cliente_id' => $this->pacienteDe($otro)->id,
        ]);

        $yo = $this->nutriologo();

        $this->actingAs($yo)->get(route('seguimientos-nutricion.show', $ajeno))->assertForbidden();
    }

    public function test_puede_registrar_una_medicion_y_actualiza_el_peso_actual(): void
    {
        $nutri = $this->nutriologo();
        $seguimiento = SeguimientoNutricion::factory()->create([
            'nutriologo_id' => $nutri->id,
            'cliente_id' => $this->pacienteDe($nutri)->id,
            'peso_inicial' => 82.5,
        ]);

        $this->actingAs($nutri)->post(route('seguimientos-nutricion.mediciones.store', $seguimiento), [
            'fecha' => now()->format('Y-m-d'),
            'peso' => 80.4,
            'notas' => 'Buena semana',
        ])->assertRedirect(route('seguimientos-nutricion.show', $seguimiento));

        $this->assertDatabaseHas('mediciones_nutricion', [
            'seguimiento_nutricion_id' => $seguimiento->id,
            'peso' => 80.4,
        ]);
        $this->assertEquals(80.4, (float) $seguimiento->fresh()->peso_actual);
    }

    public function test_no_puede_registrar_medicion_en_seguimiento_ajeno(): void
    {
        $otro = $this->nutriologo();
        $ajeno = SeguimientoNutricion::factory()->create([
            'nutriologo_id' => $otro->id,
            'cliente_id' => $this->pacienteDe($otro)->id,
        ]);

        $this->actingAs($this->nutriologo())
            ->post(route('seguimientos-nutricion.mediciones.store', $ajeno), [
                'fecha' => now()->format('Y-m-d'),
                'peso' => 70,
            ])->assertForbidden();
    }

    public function test_las_pantallas_cargan(): void
    {
        $nutri = $this->nutriologo();
        $seguimiento = SeguimientoNutricion::factory()->create([
            'nutriologo_id' => $nutri->id,
            'cliente_id' => $this->pacienteDe($nutri)->id,
        ]);

        $this->actingAs($nutri)->get(route('seguimientos-nutricion.create'))->assertOk();
        $this->actingAs($nutri)->get(route('seguimientos-nutricion.show', $seguimiento))->assertOk();
        $this->actingAs($nutri)->get(route('seguimientos-nutricion.edit', $seguimiento))->assertOk();
    }

    public function test_el_nutriologo_puede_eliminar_su_seguimiento(): void
    {
        $nutri = $this->nutriologo();
        $seguimiento = SeguimientoNutricion::factory()->create([
            'nutriologo_id' => $nutri->id,
            'cliente_id' => $this->pacienteDe($nutri)->id,
        ]);

        $this->actingAs($nutri)
            ->delete(route('seguimientos-nutricion.destroy', $seguimiento))
            ->assertRedirect(route('seguimientos-nutricion.index'));

        $this->assertDatabaseMissing('seguimientos_nutricion', ['id' => $seguimiento->id]);
    }

    public function test_el_superadmin_ve_todos_los_seguimientos(): void
    {
        $nutri = $this->nutriologo();
        SeguimientoNutricion::factory()->create([
            'nutriologo_id' => $nutri->id,
            'cliente_id' => $this->pacienteDe($nutri)->id,
        ]);

        $this->actingAs($this->superadmin())
            ->get(route('seguimientos-nutricion.index'))
            ->assertOk();
    }
}
