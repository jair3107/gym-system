<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Servicio>
 */
class ServicioFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => 'Servicio '.fake()->unique()->words(2, true),
            'descripcion' => fake()->optional()->sentence(),
            'precio' => fake()->randomFloat(2, 50, 800),
            'activo' => fake()->boolean(85),
        ];
    }
}
