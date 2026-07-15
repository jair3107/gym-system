<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Dieta;
use App\Models\Puesto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DietaTest extends TestCase
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

    public function test_un_empleado_sin_puesto_nutriologo_no_puede_ver_dietas(): void
    {
        $empleado = User::factory()->create(['role' => UserRole::Empleado]);

        $this->actingAs($empleado)->get(route('dietas.index'))->assertForbidden();
    }

    public function test_el_nutriologo_puede_ver_su_listado(): void
    {
        $this->actingAs($this->nutriologo())
            ->get(route('dietas.index'))
            ->assertOk()
            ->assertViewIs('dietas.index');
    }

    public function test_el_nutriologo_puede_crear_una_dieta_con_plan_semanal(): void
    {
        $nutri = $this->nutriologo();

        $this->actingAs($nutri)->post(route('dietas.store'), [
            'nombre' => 'Dieta hipocalórica',
            'objetivo' => 'Pérdida de grasa',
            'calorias_objetivo' => 1500,
            'activa' => '1',
            'plan' => [
                'horarios' => ['desayuno' => '8:00 - 9:00', 'colacion_1' => '7:00, 7:30'],
                'dias' => [
                    'lunes' => [
                        'desayuno' => '2 huevos revueltos y vaso de leche',
                        'colacion_1' => '5 almendras, 1 gelatina',
                    ],
                ],
            ],
        ])->assertRedirect();

        $dieta = Dieta::where('nombre', 'Dieta hipocalórica')->first();
        $this->assertNotNull($dieta);
        $this->assertSame($nutri->id, $dieta->nutriologo_id);
        $this->assertSame('8:00 - 9:00', $dieta->horario('desayuno'));
        $this->assertSame('2 huevos revueltos y vaso de leche', $dieta->contenido('lunes', 'desayuno'));
        $this->assertSame('5 almendras, 1 gelatina', $dieta->contenido('lunes', 'colacion_1'));
    }

    public function test_el_nombre_es_obligatorio(): void
    {
        $this->actingAs($this->nutriologo())
            ->post(route('dietas.store'), ['objetivo' => 'Sin nombre'])
            ->assertSessionHasErrors('nombre');
    }

    public function test_el_nutriologo_solo_accede_a_sus_dietas(): void
    {
        $otro = $this->nutriologo();
        $dietaAjena = Dieta::factory()->create(['nutriologo_id' => $otro->id, 'nombre' => 'Dieta ajena']);

        $yo = $this->nutriologo();

        // No aparece en su listado.
        $this->actingAs($yo)->get(route('dietas.index'))->assertDontSee('Dieta ajena');

        // No puede verla directamente.
        $this->actingAs($yo)->get(route('dietas.show', $dietaAjena))->assertForbidden();
    }

    public function test_el_superadmin_ve_todas_las_dietas(): void
    {
        $nutri = $this->nutriologo();
        Dieta::factory()->create(['nutriologo_id' => $nutri->id, 'nombre' => 'Dieta del nutri']);

        $this->actingAs($this->superadmin())
            ->get(route('dietas.index'))
            ->assertOk()
            ->assertSee('Dieta del nutri');
    }

    public function test_las_pantallas_cargan(): void
    {
        $nutri = $this->nutriologo();
        $dieta = Dieta::factory()->create(['nutriologo_id' => $nutri->id]);

        $this->actingAs($nutri)->get(route('dietas.create'))->assertOk();
        $this->actingAs($nutri)->get(route('dietas.show', $dieta))->assertOk();
        $this->actingAs($nutri)->get(route('dietas.edit', $dieta))->assertOk();
    }

    public function test_el_nutriologo_puede_actualizar_su_dieta(): void
    {
        $nutri = $this->nutriologo();
        $dieta = Dieta::factory()->create(['nutriologo_id' => $nutri->id]);

        $this->actingAs($nutri)->put(route('dietas.update', $dieta), [
            'nombre' => 'Dieta actualizada',
            'activa' => '0',
            'plan' => ['dias' => ['martes' => ['comida' => 'Pechuga y ensalada']]],
        ])->assertRedirect(route('dietas.show', $dieta));

        $dieta->refresh();
        $this->assertSame('Dieta actualizada', $dieta->nombre);
        $this->assertFalse($dieta->activa);
        $this->assertSame('Pechuga y ensalada', $dieta->contenido('martes', 'comida'));
    }

    public function test_el_nutriologo_puede_eliminar_su_dieta(): void
    {
        $nutri = $this->nutriologo();
        $dieta = Dieta::factory()->create(['nutriologo_id' => $nutri->id]);

        $this->actingAs($nutri)
            ->delete(route('dietas.destroy', $dieta))
            ->assertRedirect(route('dietas.index'));

        $this->assertDatabaseMissing('dietas', ['id' => $dieta->id]);
    }
}
