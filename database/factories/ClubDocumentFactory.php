<?php

namespace Database\Factories;

use App\Models\Club;
use App\Models\ClubDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClubDocument>
 */
class ClubDocumentFactory extends Factory
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
            'title' => fake()->sentence(3),
            'document_date' => now()->format('Y-m-d'),
            'body' => fake()->paragraphs(2, true),
        ];
    }
}
