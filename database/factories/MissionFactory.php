<?php

namespace Database\Factories;

use App\Models\Mission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Mission>
 */
class MissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category' => fake()->words(2, true),
            'program' => fake()->words(3, true),
            'title' => fake()->sentence(4),
            'description' => fake()->sentence(15),
            'metric_label' => fake()->words(2, true),
            'metric_value' => fake()->numberBetween(10, 5000).' '.fake()->word(),
            'image_path' => null,
            'image_alt' => fake()->sentence(6),
            'sort_order' => 0,
        ];
    }
}
