<?php

namespace Database\Factories;

use App\Models\ChapterStat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChapterStat>
 */
class ChapterStatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'value' => fake()->numberBetween(10, 999).'+',
            'label' => fake()->words(3, true),
            'sort_order' => 0,
        ];
    }
}
