<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Membresia>
 */
class MembresiaFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->randomElement(['Mensualidad', 'Trimestral', 'Semestral', 'Anual', 'Visita']),
            'descripcion' => fake()->optional()->sentence(),
            'precio' => fake()->randomFloat(2, 100, 5000),
            'duracion_dias' => fake()->randomElement([1, 30, 90, 180, 365]),
            'activa' => fake()->boolean(85),
        ];
    }
}
