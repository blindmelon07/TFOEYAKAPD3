<?php

namespace Database\Factories;

use App\Models\MembershipStep;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MembershipStep>
 */
class MembershipStepFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'icon' => fake()->randomElement(['group_add', 'fact_check', 'workspace_premium']),
            'title' => fake()->words(3, true),
            'description' => fake()->sentence(15),
            'requirement' => fake()->words(3, true),
            'sort_order' => 0,
        ];
    }
}
