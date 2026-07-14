<?php

namespace Database\Factories;

use App\Enums\ClienteEstado;
use App\Enums\Genero;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cliente>
 */
class ClienteFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => UserRole::Cliente]),
            'telefono' => fake()->phoneNumber(),
            'fecha_nacimiento' => fake()->dateTimeBetween('-60 years', '-16 years')->format('Y-m-d'),
            'genero' => fake()->randomElement(Genero::cases()),
            'direccion' => fake()->address(),
            'foto' => null,
            'contacto_emergencia_nombre' => fake()->name(),
            'contacto_emergencia_telefono' => fake()->phoneNumber(),
            'fecha_ingreso' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'estado' => fake()->randomElement(ClienteEstado::cases()),
            'entrenador_id' => null,
            'nutriologo_id' => null,
            'notas' => fake()->optional()->sentence(),
        ];
    }
}
