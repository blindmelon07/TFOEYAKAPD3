<?php

namespace Database\Factories;

use App\Models\Club;
use App\Models\DuesRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DuesRate>
 */
class DuesRateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'club_id' => Club::factory(),
            'year' => (int) now()->format('Y'),
            'amount' => '1200.00',
        ];
    }
}
