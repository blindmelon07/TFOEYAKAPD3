<?php

use App\Enums\ClubPosition;
use App\Models\Club;
use App\Models\ClubDocument;
use App\Models\Member;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('shows a district admin every club with its form count', function () {
    $club = Club::factory()->create(['name' => 'Bulan Eagles Club']);
    ClubDocument::factory()->for($club)->count(2)->create();
    ClubDocument::factory()->for($club)->create(['document_date' => '2026-09-30']);
    Club::factory()->create(['name' => 'Casiguran Eagles Club']);

    $this->actingAs(User::factory()->districtAdmin()->create())
        ->get(route('admin.forms'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/forms')
            ->has('clubs', 2)
            ->where('clubs.0.name', 'Bulan Eagles Club')
            ->where('clubs.0.documents_count', 3)
            ->where('clubs.1.documents_count', 0));
});

it('sends a club officer straight to their club\'s forms', function () {
    $secretary = Member::factory()->officer(ClubPosition::Secretary)->withLogin()->create();

    $this->actingAs($secretary->user)
        ->get(route('admin.forms'))
        ->assertRedirect(route('admin.clubs.documents.index', $secretary->club_id));
});

it('forbids regular members', function () {
    $member = Member::factory()->withLogin()->create();

    $this->actingAs($member->user)
        ->get(route('admin.forms'))
        ->assertForbidden();
});
