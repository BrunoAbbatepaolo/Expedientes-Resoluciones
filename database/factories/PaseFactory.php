<?php

namespace Database\Factories;

use App\Models\Expediente;
use App\Models\Oficina;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pase>
 */
class PaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'expediente_id' => Expediente::factory(),
            'oficina_id' => Oficina::factory(),
            'oficina_origen_id' => Oficina::factory(),
            'fecha' => fake()->dateTimeBetween('-1 year', 'now'),
            'hora' => fake()->time(),
            'observacion' => fake()->optional()->sentence(),
            'user_id' => User::factory(),
            'importado' => false,
            'firmado' => false,
        ];
    }
}
