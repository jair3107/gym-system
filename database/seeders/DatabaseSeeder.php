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

        // Empleado demo: entrenador + nutriólogo (aparece en ambos desplegables de cliente).
        $empleado = User::firstOrCreate(
            ['email' => 'empleado@gym.test'],
            ['name' => 'Empleado', 'password' => Hash::make('password'), 'role' => UserRole::Empleado],
        );
        $empleado->empleado()->firstOrCreate([], [
            'telefono' => '555-0200',
            'fecha_contratacion' => now()->subYear(),
            'salario' => 15000,
            'estado' => EmpleadoEstado::Activo,
        ]);
        $empleado->puestos()->syncWithoutDetaching([
            $puestos[Puesto::ENTRENADOR]->id,
            $puestos[Puesto::NUTRIOLOGO]->id,
        ]);

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
                'entrenador_id' => $empleado->id,
                'nutriologo_id' => $empleado->id,
            ],
        );

        // Clientes de ejemplo, asignados al empleado demo como entrenador.
        Cliente::factory()
            ->count(15)
            ->create(['entrenador_id' => $empleado->id]);
    }
}
