<?php

namespace Database\Seeders;

use App\Enums\ClienteEstado;
use App\Enums\EmpleadoEstado;
use App\Enums\MembresiaEstado;
use App\Enums\MetodoPago;
use App\Enums\TipoTarjeta;
use App\Enums\UserRole;
use App\Models\Cliente;
use App\Models\Membresia;
use App\Models\Producto;
use App\Models\Puesto;
use App\Models\Servicio;
use App\Models\User;
use App\Models\Venta;
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
        $carla = $this->crearEmpleado('Carla Caja', 'carla@gym.test', Puesto::CAJERO, 12000, $puestos);

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

        // Catálogo de caja y una venta de ejemplo (membresía + producto).
        $this->sembrarCajaDemo($carla, $clienteUser->cliente);
    }

    /**
     * Siembra el catálogo de caja (membresías, productos, servicios) y una venta
     * de ejemplo que deja al cliente demo con membresía vigente.
     */
    protected function sembrarCajaDemo(User $cajero, Cliente $cliente): void
    {
        // Catálogo de membresías.
        $membresias = collect([
            ['nombre' => 'Visita (pase de día)', 'precio' => 120, 'duracion_dias' => 1, 'descripcion' => 'Acceso por un día.'],
            ['nombre' => 'Mensualidad', 'precio' => 650, 'duracion_dias' => 30, 'descripcion' => 'Acceso ilimitado por 30 días.'],
            ['nombre' => 'Trimestral', 'precio' => 1750, 'duracion_dias' => 90, 'descripcion' => '3 meses con descuento.'],
            ['nombre' => 'Anual', 'precio' => 6000, 'duracion_dias' => 365, 'descripcion' => '12 meses al mejor precio.'],
        ])->mapWithKeys(fn ($m) => [
            $m['nombre'] => Membresia::firstOrCreate(['nombre' => $m['nombre']], $m + ['activa' => true]),
        ]);

        // Catálogo de productos con inventario.
        collect([
            ['nombre' => 'Proteína de suero 1kg', 'precio' => 720, 'stock' => 25],
            ['nombre' => 'Creatina 300g', 'precio' => 480, 'stock' => 18],
            ['nombre' => 'Bebida isotónica', 'precio' => 35, 'stock' => 60],
            ['nombre' => 'Shaker AURUM', 'precio' => 150, 'stock' => 40],
            ['nombre' => 'Toalla deportiva', 'precio' => 220, 'stock' => 15],
        ])->each(fn ($p) => Producto::firstOrCreate(['nombre' => $p['nombre']], $p + ['activo' => true]));

        // Catálogo de servicios.
        collect([
            ['nombre' => 'Evaluación física', 'precio' => 300],
            ['nombre' => 'Sesión con entrenador', 'precio' => 400],
            ['nombre' => 'Sesión con nutriólogo', 'precio' => 350],
            ['nombre' => 'Acceso a regadera', 'precio' => 40],
        ])->each(fn ($s) => Servicio::firstOrCreate(['nombre' => $s['nombre']], $s + ['activo' => true]));

        // Beneficios incluidos por plan (idempotente).
        $this->incluirBeneficio($membresias['Anual'], Producto::class, 'Proteína de suero 1kg');
        $this->incluirBeneficio($membresias['Anual'], Servicio::class, 'Evaluación física');
        $this->incluirBeneficio($membresias['Anual'], Servicio::class, 'Sesión con nutriólogo');
        $this->incluirBeneficio($membresias['Trimestral'], Producto::class, 'Shaker AURUM');
        $this->incluirBeneficio($membresias['Trimestral'], Servicio::class, 'Evaluación física');

        // Venta de ejemplo: una mensualidad + una proteína.
        if (Venta::count() > 0) {
            return;
        }

        $mensualidad = $membresias['Mensualidad'];
        $proteina = Producto::where('nombre', 'Proteína de suero 1kg')->first();

        $venta = Venta::create([
            'cajero_id' => $cajero->id,
            'cliente_id' => $cliente->id,
            'total' => $mensualidad->precio + $proteina->precio,
            'metodo_pago' => MetodoPago::Efectivo,
            'notas' => 'Venta de ejemplo.',
        ]);

        $venta->items()->createMany([
            [
                'vendible_type' => 'membresia',
                'vendible_id' => $mensualidad->id,
                'descripcion' => $mensualidad->nombre,
                'precio_unitario' => $mensualidad->precio,
                'cantidad' => 1,
                'subtotal' => $mensualidad->precio,
            ],
            [
                'vendible_type' => 'producto',
                'vendible_id' => $proteina->id,
                'descripcion' => $proteina->nombre,
                'precio_unitario' => $proteina->precio,
                'cantidad' => 1,
                'subtotal' => $proteina->precio,
            ],
        ]);

        $proteina->decrement('stock');

        // Tarjeta primaria del cliente demo (solo datos no sensibles).
        $tarjeta = $cliente->tarjetas()->firstOrCreate(
            ['ultimos4' => '4242'],
            [
                'titular' => $cliente->user->name,
                'marca' => 'visa',
                'tipo' => TipoTarjeta::Debito,
                'mes_exp' => 12,
                'anio_exp' => (int) now()->addYears(3)->format('Y'),
                'primaria' => true,
            ],
        );

        $inicio = now()->startOfDay();
        $cliente->membresiasCliente()->create([
            'membresia_id' => $mensualidad->id,
            'venta_id' => $venta->id,
            'cajero_id' => $cajero->id,
            'fecha_inicio' => $inicio->toDateString(),
            'fecha_fin' => $inicio->copy()->addDays($mensualidad->duracion_dias)->toDateString(),
            'estado' => MembresiaEstado::Activa,
            'auto_renovacion' => true,
            'metodo_pago' => MetodoPago::Tarjeta,
            'tarjeta_id' => $tarjeta->id,
        ]);

        $cliente->update(['estado' => ClienteEstado::Activo]);
    }

    /**
     * Incluye (idempotente) un producto/servicio como beneficio de un plan.
     *
     * @param  class-string  $modeloClase
     */
    protected function incluirBeneficio(Membresia $membresia, string $modeloClase, string $nombre): void
    {
        $modelo = $modeloClase::where('nombre', $nombre)->first();

        if (! $modelo) {
            return;
        }

        $tipo = $modeloClase === Producto::class ? 'producto' : 'servicio';

        $membresia->beneficios()->firstOrCreate([
            'beneficiable_type' => $tipo,
            'beneficiable_id' => $modelo->id,
        ]);
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
            'nombre' => 'Hipertrofia 4 días (Weider)',
            'objetivo' => 'Hipertrofia',
            'nivel' => 'Intermedio',
            'duracion_min' => 75,
            'descripcion' => 'Rutina dividida por grupo muscular.',
            'activa' => true,
            'plan' => [
                'dias' => [
                    'lunes' => [
                        'enfoque' => 'Pecho - Tríceps',
                        'ejercicios' => [
                            ['nombre' => 'Press banca', 'series' => '10, 8, 8, 6', 'apuntes' => ''],
                            ['nombre' => 'Press banca inclinado', 'series' => '8, 8, 6', 'apuntes' => ''],
                            ['nombre' => 'Aperturas con mancuernas', 'series' => '2 x 8', 'apuntes' => ''],
                            ['nombre' => 'Extensión tríceps en polea', 'series' => '10, 8, 8, 6', 'apuntes' => ''],
                            ['nombre' => 'Fondos', 'series' => '10, 10, 8', 'apuntes' => ''],
                        ],
                    ],
                    'martes' => [
                        'enfoque' => 'Espalda - Bíceps',
                        'ejercicios' => [
                            ['nombre' => 'Dominadas', 'series' => '3 x 8', 'apuntes' => ''],
                            ['nombre' => 'Remo horizontal con mancuerna', 'series' => '3 x 8', 'apuntes' => ''],
                            ['nombre' => 'Polea al pecho', 'series' => '10, 10, 8', 'apuntes' => ''],
                            ['nombre' => 'Curl bíceps con barra recta', 'series' => '8, 8, 6', 'apuntes' => ''],
                            ['nombre' => 'Curl concentrado', 'series' => '2 x 10', 'apuntes' => ''],
                        ],
                    ],
                    'jueves' => [
                        'enfoque' => 'Hombros - Antebrazos',
                        'ejercicios' => [
                            ['nombre' => 'Press frontal con barra', 'series' => '3 x 10', 'apuntes' => ''],
                            ['nombre' => 'Press Arnold', 'series' => '4 x 10', 'apuntes' => ''],
                            ['nombre' => 'Elevaciones laterales', 'series' => '2 x 10', 'apuntes' => ''],
                            ['nombre' => 'Curl antebrazo con barra', 'series' => '4 x 10', 'apuntes' => ''],
                        ],
                    ],
                    'viernes' => [
                        'enfoque' => 'Piernas',
                        'ejercicios' => [
                            ['nombre' => 'Sentadillas', 'series' => '3 x 8', 'apuntes' => ''],
                            ['nombre' => 'Extensiones de cuádriceps', 'series' => '3 x 8', 'apuntes' => ''],
                            ['nombre' => 'Curl femoral en máquina', 'series' => '3 x 8', 'apuntes' => ''],
                            ['nombre' => 'Gemelos de pie', 'series' => '3 x 10', 'apuntes' => ''],
                        ],
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
