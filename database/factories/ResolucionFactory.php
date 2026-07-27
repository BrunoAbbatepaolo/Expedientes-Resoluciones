<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Resolucion>
 */
class ResolucionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'numero_exp' => fake()->unique()->numerify('####/440-####'),
            'numero_resolucion' => fake()->numerify('##/####'),
            'plantilla' => '<p>'.fake()->paragraph().'</p>',
            'fecha' => fake()->dateTimeBetween('-1 year', 'now'),
            'cod_barrio' => fake()->numberBetween(1, 50),
            'cod_casa' => fake()->numberBetween(1, 200),
        ];
    }
}
