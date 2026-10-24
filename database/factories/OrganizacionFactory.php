<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Organizacion>
 */
class OrganizacionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->company(),
            'codigo_acceso' => strtoupper(Str::random(6)),
            'fecha_alta' => now()->toDateString(),
            'es_prueba' => true,
            'fecha_fin_prueba' => now()->addDays(30)->toDateString(),
            'habilitada' => true,
        ];
    }

    public function deshabilitada(): static
    {
        return $this->state(fn () => ['habilitada' => false]);
    }
}
