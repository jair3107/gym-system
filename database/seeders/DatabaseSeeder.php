<?php

namespace Database\Seeders;

use App\Enums\ClienteEstado;
use App\Enums\EmpleadoEstado;
use App\Enums\UserRole;
use App\Models\Cliente;
use App\Models\Puesto;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Catálogo de puestos base.
        $puestos = collect([
            ['nombre' => 'Administrador', 'slug' => Puesto::ADMIN, 'descripcion' => 'Puede gestionar empleados.'],
            ['nombre' => 'Cajero', 'slug' => Puesto::CAJERO, 'descripcion' => 'Vende membresías, productos y servicios.'],
            ['nombre' => 'Nutriólogo', 'slug' => Puesto::NUTRIOLOGO, 'descripcion' => 'Gestiona dietas y pacientes.'],
            ['nombre' => 'Entrenador', 'slug' => Puesto::ENTRENADOR, 'descripcion' => 'Gestiona rutinas y alumnos.'],
            ['nombre' => 'Recepcionista', 'slug' => 'recepcionista', 'descripcion' => 'Atención en recepción.'],
        ])->mapWithKeys(fn ($p) => [
            $p['slug'] => Puesto::firstOrCreate(['slug' => $p['slug']], $p),
        ]);

        User::firstOrCreate(
            ['email' => 'admin@gym.test'],
            ['name' => 'Admin', 'password' => Hash::make('password'), 'role' => UserRole::SuperAdmin],
        );

        // Plantilla: dos entrenadores y dos nutriólogos.
        $larry = $this->crearEmpleado('Larry Langosta', 'larry@gym.test', Puesto::ENTRENADOR, 14000, $puestos);
        $arnold = $this->crearEmpleado('Arnold Schwarzenegger', 'arnold@gym.test', Puesto::ENTRENADOR, 18000, $puestos);
        $sonia = $this->crearEmpleado('Sonia Zanahoria', 'sonia@gym.test', Puesto::NUTRIOLOGO, 15000, $puestos);
        $benito = $this->crearEmpleado('Benito Brócoli', 'benito@gym.test', Puesto::NUTRIOLOGO, 13500, $puestos);

        // Reemplazo del empleado demo anterior: reasigna sus clientes y lo elimina.
        $this->reemplazarEmpleadoLegacy($larry, $sonia);

        // Cliente de demo con perfil completo.
        $clienteUser = User::firstOrCreate(
            ['email' => 'cliente@gym.test'],
            ['name' => 'Cliente', 'password' => Hash::make('password'), 'role' => UserRole::Cliente],
        );

        Cliente::firstOrCreate(
            ['user_id' => $clienteUser->id],
            [
                'telefono' => '555-0100',
                'fecha_ingreso' => now()->subMonths(3),
                'estado' => ClienteEstado::Activo,
                'entrenador_id' => $larry->id,
                'nutriologo_id' => $sonia->id,
            ],
        );

        // Clientes de ejemplo, repartidos entre los entrenadores y nutriólogos.
        if (Cliente::count() <= 1) {
            Cliente::factory()
                ->count(16)
                ->sequence(
                    ['entrenador_id' => $larry->id, 'nutriologo_id' => $sonia->id],
                    ['entrenador_id' => $arnold->id, 'nutriologo_id' => $benito->id],
                )
                ->create();
        }

        // Datos demo de nutrición: una dieta de Sonia y un seguimiento con progreso.
        $this->sembrarNutricionDemo($sonia, $clienteUser->cliente);
    }

    /**
     * Crea una dieta y un seguimiento de ejemplo para poder explorar el módulo.
     */
    protected function sembrarNutricionDemo(User $nutriologo, Cliente $paciente): void
    {
        if ($nutriologo->dietas()->exists()) {
            return;
        }

        $dieta = $nutriologo->dietas()->create([
            'nombre' => 'Dieta hipocalórica 1500 kcal',
            'objetivo' => 'Pérdida de grasa',
            'calorias_objetivo' => 1500,
            'descripcion' => 'Plan base de ejemplo.',
            'activa' => true,
            'plan' => [
                'horarios' => [
                    'colacion_1' => '7:00, 7:30',
                    'desayuno' => '8:00 - 9:00',
                    'comida' => '14:00 - 15:00',
                    'cena' => '20:00 - 21:00',
                ],
                'dias' => [
                    'lunes' => [
                        'colacion_1' => '5 almendras, 1 gelatina',
                        'desayuno' => '2 huevos revueltos y vaso de leche',
                        'comida' => 'Pechuga a la plancha con ensalada',
                        'cena' => 'Yogur griego con fruta',
                    ],
                    'miercoles' => [
                        'desayuno' => 'Avena con plátano',
                        'comida' => 'Salmón con verduras al vapor',
                    ],
                ],
            ],
        ]);

        $seguimiento = \App\Models\SeguimientoNutricion::create([
            'nutriologo_id' => $nutriologo->id,
            'cliente_id' => $paciente->id,
            'dieta_id' => $dieta->id,
            'fecha_inicio' => now()->subWeeks(4),
            'peso_inicial' => 84.0,
            'peso_meta' => 76.0,
            'estado' => \App\Enums\SeguimientoEstado::Activo,
            'notas' => 'Seguimiento de ejemplo.',
        ]);

        $seguimiento->mediciones()->createMany([
            ['fecha' => now()->subWeeks(3), 'peso' => 82.6, 'notas' => 'Buena adherencia'],
            ['fecha' => now()->subWeeks(2), 'peso' => 81.4],
            ['fecha' => now()->subWeek(), 'peso' => 80.2, 'notas' => 'Sin antojos'],
        ]);
    }

    /**
     * Crea (o recupera) un empleado con su perfil y un puesto asignado.
     */
    protected function crearEmpleado(string $nombre, string $email, string $puestoSlug, int $salario, $puestos): User
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => $nombre, 'password' => Hash::make('password'), 'role' => UserRole::Empleado],
        );

        $user->empleado()->firstOrCreate([], [
            'telefono' => '555-0'.rand(100, 999),
            'fecha_contratacion' => now()->subMonths(rand(3, 24)),
            'salario' => $salario,
            'estado' => EmpleadoEstado::Activo,
        ]);

        $user->puestos()->syncWithoutDetaching([$puestos[$puestoSlug]->id]);

        return $user;
    }

    /**
     * Migra los clientes del antiguo empleado demo a los nuevos trabajadores
     * y elimina esa cuenta genérica si aún existe.
     */
    protected function reemplazarEmpleadoLegacy(User $entrenador, User $nutriologo): void
    {
        $legacy = User::where('email', 'empleado@gym.test')->first();

        if (! $legacy) {
            return;
        }

        Cliente::where('entrenador_id', $legacy->id)->update(['entrenador_id' => $entrenador->id]);
        Cliente::where('nutriologo_id', $legacy->id)->update(['nutriologo_id' => $nutriologo->id]);

        // Al borrar el user caen su perfil de empleado y los puestos por FK.
        $legacy->delete();
    }
}
