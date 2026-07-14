<?php

namespace Database\Seeders;

use App\Enums\ClienteEstado;
use App\Enums\UserRole;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@gym.test'],
            ['name' => 'Admin', 'password' => Hash::make('password'), 'role' => UserRole::SuperAdmin],
        );

        $empleado = User::firstOrCreate(
            ['email' => 'empleado@gym.test'],
            ['name' => 'Empleado', 'password' => Hash::make('password'), 'role' => UserRole::Empleado],
        );

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
            ],
        );

        // Clientes de ejemplo, algunos asignados al empleado como entrenador.
        Cliente::factory()
            ->count(15)
            ->create(['entrenador_id' => $empleado->id]);
    }
}
