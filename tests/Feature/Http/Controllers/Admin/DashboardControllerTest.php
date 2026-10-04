<?php

use App\Enums\ClubPosition;
use App\Models\Member;
use App\Models\User;

it('sends a district admin to the member roster', function () {
    $this->actingAs(User::factory()->districtAdmin()->create())
        ->get(route('admin.dashboard'))
        ->assertRedirect(route('admin.members.index'));
});

it('sends a club officer to their club roster', function () {
    $officer = Member::factory()->officer(ClubPosition::Secretary)->withLogin()->create();

    $this->actingAs($officer->user)
        ->get(route('admin.dashboard'))
        ->assertRedirect(route('admin.members.index'));
});

it('sends a regular member to their own profile', function () {
    $member = Member::factory()->withLogin()->create();

    $this->actingAs($member->user)
        ->get(route('admin.dashboard'))
        ->assertRedirect(route('admin.members.show', $member));
});
