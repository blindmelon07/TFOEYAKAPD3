<?php

namespace Database\Factories;

use App\Models\FundAllocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FundAllocation>
 */
class FundAllocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'label' => fake()->word(),
            'percentage' => fake()->numberBetween(5, 50),
            'color' => fake()->randomElement(FundAllocation::COLORS),
            'sort_order' => 0,
        ];
    }
}
