<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Producto>
 */
class ProductoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => 'Producto '.fake()->unique()->words(2, true),
            'descripcion' => fake()->optional()->sentence(),
            'precio' => fake()->randomFloat(2, 20, 2000),
            'stock' => fake()->numberBetween(0, 50),
            'activo' => fake()->boolean(85),
        ];
    }
}
