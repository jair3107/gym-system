<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rutina>
 */
class RutinaFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'entrenador_id' => User::factory()->state(['role' => UserRole::Empleado]),
            'nombre' => 'Rutina '.fake()->words(2, true),
            'objetivo' => fake()->randomElement(['Hipertrofia', 'Fuerza', 'Resistencia', 'Pérdida de grasa', null]),
            'nivel' => fake()->randomElement(['Principiante', 'Intermedio', 'Avanzado', null]),
            'duracion_min' => fake()->optional()->numberBetween(30, 120),
            'descripcion' => fake()->optional()->sentence(),
            'activa' => fake()->boolean(80),
            'plan' => [
                'dias' => [
                    'lunes' => [
                        'enfoque' => 'Pecho - Tríceps',
                        'ejercicios' => [
                            ['nombre' => 'Press banca', 'series' => '10, 8, 8, 6', 'apuntes' => ''],
                            ['nombre' => 'Aperturas con mancuernas', 'series' => '2 x 8', 'apuntes' => ''],
                        ],
                    ],
                ],
            ],
        ];
    }
}
