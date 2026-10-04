<?php

namespace Database\Factories;

use App\Enums\ClubPosition;
use App\Enums\MemberStatus;
use App\Models\Club;
use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
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
            'position' => ClubPosition::Member,
            'status' => MemberStatus::Active,
            'member_number' => fake()->unique()->numerify('TFOE-#######'),
            'first_name' => fake()->firstName(),
            'middle_name' => fake()->optional()->lastName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('09#########'),
            'address' => fake()->address(),
            'birthday' => fake()->dateTimeBetween('-70 years', '-21 years')->format('Y-m-d'),
            'inducted_at' => fake()->dateTimeBetween('-20 years')->format('Y-m-d'),
        ];
    }

    /**
     * Appoint the member to the given club office.
     */
    public function officer(ClubPosition $position): static
    {
        return $this->state(fn (array $attributes) => [
            'position' => $position,
        ]);
    }

    /**
     * Give the member a login account using their email address.
     */
    public function withLogin(): static
    {
        return $this->afterCreating(function (Member $member): void {
            $user = User::factory()->create([
                'name' => $member->full_name,
                'email' => $member->email,
            ]);

            $member->user()->associate($user)->save();
        });
    }
}
