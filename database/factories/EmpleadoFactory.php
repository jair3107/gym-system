<?php

namespace Database\Factories;

use App\Enums\EmpleadoEstado;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Empleado>
 */
class EmpleadoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => UserRole::Empleado]),
            'telefono' => fake()->phoneNumber(),
            'fecha_contratacion' => fake()->dateTimeBetween('-3 years', 'now')->format('Y-m-d'),
            'salario' => fake()->randomFloat(2, 8000, 30000),
            'direccion' => fake()->address(),
            'foto' => null,
            'estado' => EmpleadoEstado::Activo,
            'notas' => fake()->optional()->sentence(),
        ];
    }
}
