<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@gym.test',
            'password' => Hash::make('password'),
            'role' => UserRole::SuperAdmin,
        ]);

        User::create([
            'name' => 'Empleado',
            'email' => 'empleado@gym.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Empleado,
        ]);

        User::create([
            'name' => 'Cliente',
            'email' => 'cliente@gym.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Cliente,
        ]);
    }
}
