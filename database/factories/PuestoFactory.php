<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Puesto>
 */
class PuestoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nombre = fake()->unique()->jobTitle();

        return [
            'nombre' => $nombre,
            'slug' => Str::slug($nombre).'-'.fake()->unique()->numberBetween(1, 100000),
            'descripcion' => fake()->optional()->sentence(),
        ];
    }
}
