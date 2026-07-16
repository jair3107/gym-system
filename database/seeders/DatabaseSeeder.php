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
            ['nombre' => 'Recepcionista / Cajero', 'slug' => Puesto::CAJERO, 'descripcion' => 'Atención en recepción y venta de membresías, productos y servicios. Da de alta clientes.'],
            ['nombre' => 'Nutriólogo', 'slug' => Puesto::NUTRIOLOGO, 'descripcion' => 'Gestiona dietas y pacientes.'],
            ['nombre' => 'Entrenador', 'slug' => Puesto::ENTRENADOR, 'descripcion' => 'Gestiona rutinas y alumnos.'],
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

        // Datos demo de entrenamiento: una rutina de Larry y un seguimiento con progreso.
        $this->sembrarEntrenamientoDemo($larry, $clienteUser->cliente);
    }

    /**
     * Crea una rutina y un seguimiento de ejemplo para explorar el módulo de entrenamiento.
     */
    protected function sembrarEntrenamientoDemo(User $entrenador, Cliente $alumno): void
    {
        if ($entrenador->rutinas()->exists()) {
            return;
        }

        $rutina = $entrenador->rutinas()->create([
            'nombre' => 'Fuerza full-body 3 días',
            'objetivo' => 'Hipertrofia',
            'nivel' => 'Intermedio',
            'duracion_min' => 60,
            'descripcion' => 'Rutina base de ejemplo.',
            'activa' => true,
            'plan' => [
                'duraciones' => [
                    'calentamiento' => '10 min',
                    'principal' => '35 min',
                    'cardio' => '15 min',
                ],
                'dias' => [
                    'lunes' => [
                        'calentamiento' => 'Movilidad articular y bici suave',
                        'principal' => 'Sentadilla 4×8, Press banca 4×8, Remo 4×10',
                        'cardio' => 'Cinta 15 min ritmo moderado',
                    ],
                    'miercoles' => [
                        'calentamiento' => 'Movilidad de cadera y hombro',
                        'principal' => 'Peso muerto 4×6, Press militar 4×8, Dominadas 4×máx',
                    ],
                    'viernes' => [
                        'principal' => 'Prensa 4×12, Curl bíceps 3×12, Extensión tríceps 3×12',
                        'cardio' => 'Elíptica 15 min',
                    ],
                ],
            ],
        ]);

        $seguimiento = \App\Models\SeguimientoEntrenamiento::create([
            'entrenador_id' => $entrenador->id,
            'cliente_id' => $alumno->id,
            'rutina_id' => $rutina->id,
            'fecha_inicio' => now()->subWeeks(4),
            'peso_inicial' => 84.0,
            'peso_meta' => 80.0,
            'masa_inicial' => 34.0,
            'masa_meta' => 38.0,
            'estado' => \App\Enums\SeguimientoEstado::Activo,
            'notas' => 'Seguimiento de ejemplo.',
        ]);

        $seguimiento->mediciones()->createMany([
            ['fecha' => now()->subWeeks(3), 'peso' => 83.4, 'masa_muscular' => 34.6, 'notas' => 'Buena técnica'],
            ['fecha' => now()->subWeeks(2), 'peso' => 82.8, 'masa_muscular' => 35.3],
            ['fecha' => now()->subWeek(), 'peso' => 82.1, 'masa_muscular' => 36.0, 'notas' => 'Subiendo cargas'],
        ]);
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
