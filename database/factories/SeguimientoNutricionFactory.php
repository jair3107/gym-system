<?php

namespace Database\Factories;

use App\Enums\SeguimientoEstado;
use App\Enums\UserRole;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SeguimientoNutricion>
 */
class SeguimientoNutricionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nutriologo_id' => User::factory()->state(['role' => UserRole::Empleado]),
            'cliente_id' => Cliente::factory(),
            'dieta_id' => null,
            'fecha_inicio' => now()->subMonth()->format('Y-m-d'),
            'fecha_fin' => null,
            'peso_inicial' => fake()->randomFloat(2, 55, 110),
            'peso_meta' => fake()->randomFloat(2, 50, 100),
            'estado' => SeguimientoEstado::Activo,
            'notas' => fake()->optional()->sentence(),
        ];
    }
}
