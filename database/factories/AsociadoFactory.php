<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Asociado>
 */
class AsociadoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'telefono' => null,
            'domicilio' => null,
            'activo' => true,
        ];
    }

    public function conGoogle(): static
    {
        return $this->state(fn () => ['google_id' => (string) fake()->unique()->numberBetween(10 ** 15, 10 ** 16)]);
    }
}
