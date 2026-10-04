<?php

namespace Database\Factories;

use App\Models\DuesPayment;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DuesPayment>
 */
class DuesPaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'member_id' => Member::factory(),
            'year' => (int) now()->format('Y'),
            'amount' => fake()->randomElement(['500.00', '1000.00', '1200.00']),
            'paid_at' => now()->format('Y-m-d'),
            'reference' => fake()->numerify('OR-#####'),
            'notes' => null,
        ];
    }
}
