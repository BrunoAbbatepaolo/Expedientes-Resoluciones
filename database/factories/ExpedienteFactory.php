<?php

namespace Database\Factories;

use App\Models\Oficina;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expediente>
 */
class ExpedienteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'num_exp' => fake()->unique()->numerify('####/####'),
            'folio' => (string) fake()->numberBetween(1, 999),
            'causante' => fake()->company(),
            'asunto' => fake()->sentence(4),
            'fecha_ingreso' => fake()->dateTimeBetween('-1 year', 'now'),
            'oficina_id' => Oficina::factory(),
        ];
    }
}
