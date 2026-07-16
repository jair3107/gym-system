<?php

namespace Database\Factories;

use App\Enums\SeguimientoEstado;
use App\Enums\UserRole;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SeguimientoEntrenamiento>
 */
class SeguimientoEntrenamientoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'entrenador_id' => User::factory()->state(['role' => UserRole::Empleado]),
            'cliente_id' => Cliente::factory(),
            'rutina_id' => null,
            'fecha_inicio' => now()->subMonth()->format('Y-m-d'),
            'fecha_fin' => null,
            'peso_inicial' => fake()->randomFloat(2, 55, 110),
            'peso_meta' => fake()->randomFloat(2, 50, 100),
            'masa_inicial' => fake()->randomFloat(2, 25, 45),
            'masa_meta' => fake()->randomFloat(2, 30, 50),
            'estado' => SeguimientoEstado::Activo,
            'notas' => fake()->optional()->sentence(),
        ];
    }
}
