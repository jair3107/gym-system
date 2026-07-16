<?php

namespace Tests\Feature;

use App\Enums\ClienteEstado;
use App\Enums\SeguimientoEstado;
use App\Enums\UserRole;
use App\Models\Cliente;
use App\Models\Rutina;
use App\Models\Puesto;
use App\Models\SeguimientoEntrenamiento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeguimientoEntrenamientoTest extends TestCase
{
    use RefreshDatabase;

    private function superadmin(): User
    {
        return User::factory()->create(['role' => UserRole::SuperAdmin]);
    }

    private function entrenador(): User
    {
        $user = User::factory()->create(['role' => UserRole::Empleado]);
        $puesto = Puesto::firstOrCreate(['slug' => Puesto::ENTRENADOR], ['nombre' => 'Entrenador']);
        $user->puestos()->attach($puesto);

        return $user;
    }

    private function alumnoDe(User $entre): Cliente
    {
        return Cliente::factory()->create(['entrenador_id' => $entre->id]);
    }

    private function rutinaDe(User $entre): Rutina
    {
        return Rutina::factory()->create(['entrenador_id' => $entre->id, 'activa' => true]);
    }

    public function test_un_empleado_sin_puesto_entrenador_no_puede_ver_seguimientos(): void
    {
        $empleado = User::factory()->create(['role' => UserRole::Empleado]);

        $this->actingAs($empleado)->get(route('seguimientos-entrenamiento.index'))->assertForbidden();
    }

    public function test_el_entrenador_puede_ver_su_listado(): void
    {
        $this->actingAs($this->entrenador())
            ->get(route('seguimientos-entrenamiento.index'))
            ->assertOk()
            ->assertViewIs('seguimientos-entrenamiento.index');
    }

    public function test_el_entrenador_puede_crear_un_seguimiento_para_su_alumno(): void
    {
        $entre = $this->entrenador();
        $alumno = $this->alumnoDe($entre);
        $rutina = $this->rutinaDe($entre);

        $this->actingAs($entre)->post(route('seguimientos-entrenamiento.store'), [
            'cliente_id' => $alumno->id,
            'rutina_id' => $rutina->id,
            'fecha_inicio' => now()->format('Y-m-d'),
            'peso_inicial' => 82.5,
            'peso_meta' => 78,
            'masa_inicial' => 34,
            'masa_meta' => 38,
            'estado' => SeguimientoEstado::Activo->value,
        ])->assertRedirect();

        $this->assertDatabaseHas('seguimientos_entrenamiento', [
            'cliente_id' => $alumno->id,
            'rutina_id' => $rutina->id,
            'entrenador_id' => $entre->id,
        ]);
    }

    public function test_no_puede_crear_seguimiento_para_alumno_ajeno(): void
    {
        $entre = $this->entrenador();
        $otro = $this->entrenador();
        $alumnoAjeno = $this->alumnoDe($otro);

        $this->actingAs($entre)->post(route('seguimientos-entrenamiento.store'), [
            'cliente_id' => $alumnoAjeno->id,
            'fecha_inicio' => now()->format('Y-m-d'),
            'estado' => SeguimientoEstado::Activo->value,
        ])->assertSessionHasErrors('cliente_id');
    }

    public function test_no_puede_asignar_rutina_ajena(): void
    {
        $entre = $this->entrenador();
        $alumno = $this->alumnoDe($entre);
        $otro = $this->entrenador();
        $rutinaAjena = $this->rutinaDe($otro);

        $this->actingAs($entre)->post(route('seguimientos-entrenamiento.store'), [
            'cliente_id' => $alumno->id,
            'rutina_id' => $rutinaAjena->id,
            'fecha_inicio' => now()->format('Y-m-d'),
            'estado' => SeguimientoEstado::Activo->value,
        ])->assertSessionHasErrors('rutina_id');
    }

    public function test_el_entrenador_solo_accede_a_sus_seguimientos(): void
    {
        $otro = $this->entrenador();
        $ajeno = SeguimientoEntrenamiento::factory()->create([
            'entrenador_id' => $otro->id,
            'cliente_id' => $this->alumnoDe($otro)->id,
        ]);

        $yo = $this->entrenador();

        $this->actingAs($yo)->get(route('seguimientos-entrenamiento.show', $ajeno))->assertForbidden();
    }

    public function test_puede_registrar_una_medicion_y_actualiza_peso_y_masa(): void
    {
        $entre = $this->entrenador();
        $seguimiento = SeguimientoEntrenamiento::factory()->create([
            'entrenador_id' => $entre->id,
            'cliente_id' => $this->alumnoDe($entre)->id,
            'peso_inicial' => 82.5,
            'masa_inicial' => 34.0,
        ]);

        $this->actingAs($entre)->post(route('seguimientos-entrenamiento.mediciones.store', $seguimiento), [
            'fecha' => now()->format('Y-m-d'),
            'peso' => 80.4,
            'masa_muscular' => 35.2,
            'notas' => 'Buena semana',
        ])->assertRedirect(route('seguimientos-entrenamiento.show', $seguimiento));

        $this->assertDatabaseHas('mediciones_entrenamiento', [
            'seguimiento_entrenamiento_id' => $seguimiento->id,
            'peso' => 80.4,
            'masa_muscular' => 35.2,
        ]);
        $this->assertEquals(80.4, (float) $seguimiento->fresh()->peso_actual);
        $this->assertEquals(35.2, (float) $seguimiento->fresh()->masa_actual);
    }

    public function test_no_puede_registrar_medicion_en_seguimiento_ajeno(): void
    {
        $otro = $this->entrenador();
        $ajeno = SeguimientoEntrenamiento::factory()->create([
            'entrenador_id' => $otro->id,
            'cliente_id' => $this->alumnoDe($otro)->id,
        ]);

        $this->actingAs($this->entrenador())
            ->post(route('seguimientos-entrenamiento.mediciones.store', $ajeno), [
                'fecha' => now()->format('Y-m-d'),
                'peso' => 70,
            ])->assertForbidden();
    }

    public function test_las_pantallas_cargan(): void
    {
        $entre = $this->entrenador();
        $seguimiento = SeguimientoEntrenamiento::factory()->create([
            'entrenador_id' => $entre->id,
            'cliente_id' => $this->alumnoDe($entre)->id,
        ]);

        $this->actingAs($entre)->get(route('seguimientos-entrenamiento.create'))->assertOk();
        $this->actingAs($entre)->get(route('seguimientos-entrenamiento.show', $seguimiento))->assertOk();
        $this->actingAs($entre)->get(route('seguimientos-entrenamiento.edit', $seguimiento))->assertOk();
    }

    public function test_el_entrenador_puede_eliminar_su_seguimiento(): void
    {
        $entre = $this->entrenador();
        $seguimiento = SeguimientoEntrenamiento::factory()->create([
            'entrenador_id' => $entre->id,
            'cliente_id' => $this->alumnoDe($entre)->id,
        ]);

        $this->actingAs($entre)
            ->delete(route('seguimientos-entrenamiento.destroy', $seguimiento))
            ->assertRedirect(route('seguimientos-entrenamiento.index'));

        $this->assertDatabaseMissing('seguimientos_entrenamiento', ['id' => $seguimiento->id]);
    }

    public function test_el_superadmin_ve_todos_los_seguimientos(): void
    {
        $entre = $this->entrenador();
        SeguimientoEntrenamiento::factory()->create([
            'entrenador_id' => $entre->id,
            'cliente_id' => $this->alumnoDe($entre)->id,
        ]);

        $this->actingAs($this->superadmin())
            ->get(route('seguimientos-entrenamiento.index'))
            ->assertOk();
    }

    public function test_el_entrenador_puede_asignar_una_rutina_desde_la_ficha(): void
    {
        $entre = $this->entrenador();
        $alumno = $this->alumnoDe($entre);
        $rutina = $this->rutinaDe($entre);

        $this->actingAs($entre)
            ->post(route('seguimientos-entrenamiento.asignar', $alumno), ['rutina_id' => $rutina->id])
            ->assertRedirect();

        $this->assertDatabaseHas('seguimientos_entrenamiento', [
            'cliente_id' => $alumno->id,
            'rutina_id' => $rutina->id,
            'entrenador_id' => $entre->id,
        ]);
    }

    public function test_no_puede_asignar_una_rutina_a_un_alumno_ajeno(): void
    {
        $entre = $this->entrenador();
        $otro = $this->entrenador();
        $ajeno = $this->alumnoDe($otro);
        $rutina = $this->rutinaDe($entre);

        $this->actingAs($entre)
            ->post(route('seguimientos-entrenamiento.asignar', $ajeno), ['rutina_id' => $rutina->id])
            ->assertForbidden();
    }

    public function test_no_puede_asignar_una_rutina_ajena(): void
    {
        $entre = $this->entrenador();
        $alumno = $this->alumnoDe($entre);
        $otro = $this->entrenador();
        $rutinaAjena = $this->rutinaDe($otro);

        $this->actingAs($entre)
            ->post(route('seguimientos-entrenamiento.asignar', $alumno), ['rutina_id' => $rutinaAjena->id])
            ->assertSessionHasErrors('rutina_id');
    }

    public function test_la_ficha_del_alumno_muestra_el_panel_de_entrenamiento(): void
    {
        $entre = $this->entrenador();
        $alumno = Cliente::factory()->create(['entrenador_id' => $entre->id, 'estado' => ClienteEstado::Activo]);

        $this->actingAs($entre)
            ->get(route('clientes.show', $alumno))
            ->assertOk()
            ->assertSee('Asignar una de mis rutinas');
    }
}
