<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Puesto;
use App\Models\Rutina;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RutinaTest extends TestCase
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

    public function test_un_empleado_sin_puesto_entrenador_no_puede_ver_rutinas(): void
    {
        $empleado = User::factory()->create(['role' => UserRole::Empleado]);

        $this->actingAs($empleado)->get(route('rutinas.index'))->assertForbidden();
    }

    public function test_el_entrenador_puede_ver_su_listado(): void
    {
        $this->actingAs($this->entrenador())
            ->get(route('rutinas.index'))
            ->assertOk()
            ->assertViewIs('rutinas.index');
    }

    public function test_el_entrenador_puede_crear_una_rutina_con_plan_semanal(): void
    {
        $entrenador = $this->entrenador();

        $this->actingAs($entrenador)->post(route('rutinas.store'), [
            'nombre' => 'Fuerza full-body',
            'objetivo' => 'Hipertrofia',
            'nivel' => 'Intermedio',
            'duracion_min' => 60,
            'activa' => '1',
            'plan' => [
                'dias' => [
                    'lunes' => [
                        'enfoque' => 'Pecho - Tríceps',
                        'ejercicios' => [
                            ['nombre' => 'Press banca', 'series' => '10, 8, 8, 6', 'apuntes' => 'Controlar bajada'],
                            ['nombre' => '', 'series' => '', 'apuntes' => ''], // fila vacía: se descarta
                        ],
                    ],
                ],
            ],
        ])->assertRedirect();

        $rutina = Rutina::where('nombre', 'Fuerza full-body')->first();
        $this->assertNotNull($rutina);
        $this->assertSame($entrenador->id, $rutina->entrenador_id);
        $this->assertSame('Pecho - Tríceps', $rutina->enfoque('lunes'));

        $ejercicios = $rutina->ejercicios('lunes');
        $this->assertCount(1, $ejercicios);
        $this->assertSame('Press banca', $ejercicios[0]['nombre']);
        $this->assertSame('10, 8, 8, 6', $ejercicios[0]['series']);
        $this->assertSame('Controlar bajada', $ejercicios[0]['apuntes']);
    }

    public function test_el_nombre_es_obligatorio(): void
    {
        $this->actingAs($this->entrenador())
            ->post(route('rutinas.store'), ['objetivo' => 'Sin nombre'])
            ->assertSessionHasErrors('nombre');
    }

    public function test_el_entrenador_solo_accede_a_sus_rutinas(): void
    {
        $otro = $this->entrenador();
        $rutinaAjena = Rutina::factory()->create(['entrenador_id' => $otro->id, 'nombre' => 'Rutina ajena']);

        $yo = $this->entrenador();

        // No aparece en su listado.
        $this->actingAs($yo)->get(route('rutinas.index'))->assertDontSee('Rutina ajena');

        // No puede verla directamente.
        $this->actingAs($yo)->get(route('rutinas.show', $rutinaAjena))->assertForbidden();
    }

    public function test_el_superadmin_ve_todas_las_rutinas(): void
    {
        $entrenador = $this->entrenador();
        Rutina::factory()->create(['entrenador_id' => $entrenador->id, 'nombre' => 'Rutina del entre']);

        $this->actingAs($this->superadmin())
            ->get(route('rutinas.index'))
            ->assertOk()
            ->assertSee('Rutina del entre');
    }

    public function test_las_pantallas_cargan(): void
    {
        $entrenador = $this->entrenador();
        $rutina = Rutina::factory()->create(['entrenador_id' => $entrenador->id]);

        $this->actingAs($entrenador)->get(route('rutinas.create'))->assertOk();
        $this->actingAs($entrenador)->get(route('rutinas.show', $rutina))->assertOk();
        $this->actingAs($entrenador)->get(route('rutinas.edit', $rutina))->assertOk();
    }

    public function test_el_entrenador_puede_actualizar_su_rutina(): void
    {
        $entrenador = $this->entrenador();
        $rutina = Rutina::factory()->create(['entrenador_id' => $entrenador->id]);

        $this->actingAs($entrenador)->put(route('rutinas.update', $rutina), [
            'nombre' => 'Rutina actualizada',
            'activa' => '0',
            'plan' => ['dias' => ['martes' => [
                'enfoque' => 'Piernas',
                'ejercicios' => [['nombre' => 'Sentadillas', 'series' => '3 x 8', 'apuntes' => '']],
            ]]],
        ])->assertRedirect(route('rutinas.show', $rutina));

        $rutina->refresh();
        $this->assertSame('Rutina actualizada', $rutina->nombre);
        $this->assertFalse($rutina->activa);
        $this->assertSame('Piernas', $rutina->enfoque('martes'));
        $this->assertSame('Sentadillas', $rutina->ejercicios('martes')[0]['nombre']);
    }

    public function test_el_entrenador_puede_eliminar_su_rutina(): void
    {
        $entrenador = $this->entrenador();
        $rutina = Rutina::factory()->create(['entrenador_id' => $entrenador->id]);

        $this->actingAs($entrenador)
            ->delete(route('rutinas.destroy', $rutina))
            ->assertRedirect(route('rutinas.index'));

        $this->assertDatabaseMissing('rutinas', ['id' => $rutina->id]);
    }
}
