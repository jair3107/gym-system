<?php

namespace Database\Factories;

use App\Enums\MetodoPago;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Venta>
 */
class VentaFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cajero_id' => User::factory()->state(['role' => UserRole::Empleado]),
            'cliente_id' => null,
            'total' => fake()->randomFloat(2, 100, 3000),
            'metodo_pago' => fake()->randomElement(MetodoPago::cases())->value,
            'notas' => fake()->optional()->sentence(),
        ];
    }
}
