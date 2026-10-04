<?php

namespace Database\Factories;

use App\Models\Pillar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pillar>
 */
class PillarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'icon' => fake()->randomElement(['diversity_3', 'volunteer_activism', 'flag', 'balance']),
            'tag' => 'Pillar '.fake()->randomElement(['I', 'II', 'III', 'IV']).' • '.fake()->word(),
            'title' => fake()->words(2, true),
            'description' => fake()->sentence(15),
            'sort_order' => 0,
        ];
    }
}
