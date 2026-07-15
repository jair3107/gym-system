<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Dieta>
 */
class DietaFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nutriologo_id' => User::factory()->state(['role' => UserRole::Empleado]),
            'nombre' => 'Dieta '.fake()->words(2, true),
            'objetivo' => fake()->randomElement(['Pérdida de grasa', 'Aumento de masa', 'Mantenimiento', null]),
            'calorias_objetivo' => fake()->optional()->numberBetween(1200, 3500),
            'descripcion' => fake()->optional()->sentence(),
            'activa' => fake()->boolean(80),
            'plan' => [
                'horarios' => [
                    'desayuno' => '8:00 - 9:00',
                    'colacion_1' => '7:00',
                ],
                'dias' => [
                    'lunes' => [
                        'desayuno' => '2 huevos revueltos y vaso de leche',
                        'colacion_1' => '5 almendras, 1 gelatina',
                    ],
                ],
            ],
        ];
    }
}
