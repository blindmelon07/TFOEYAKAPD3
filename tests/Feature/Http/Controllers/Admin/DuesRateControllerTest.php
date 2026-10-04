<?php

use App\Enums\ClubPosition;
use App\Models\Club;
use App\Models\DuesPayment;
use App\Models\DuesRate;
use App\Models\Member;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->club = Club::factory()->create();
});

it('lists the club\'s rates with how many members paid each year', function () {
    DuesRate::factory()->for($this->club)->create(['year' => 2026, 'amount' => '1000.00']);
    $paid = Member::factory()->for($this->club)->create();
    DuesPayment::factory()->for($paid)->create(['year' => 2026, 'amount' => '1000.00']);
    $partial = Member::factory()->for($this->club)->create();
    DuesPayment::factory()->for($partial)->create(['year' => 2026, 'amount' => '300.00']);
    Member::factory()->for($this->club)->create();

    $this->actingAs(User::factory()->districtAdmin()->create())
        ->get(route('admin.clubs.dues-rates.index', $this->club))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/clubs/dues-rates')
            ->where('rates.0.amount', '1000.00')
            ->where('rates.0.counts.paid', 1)
            ->where('rates.0.counts.partial', 1)
            ->where('rates.0.counts.unpaid', 1)
            ->where('canManage', true));
});

it('lets the club treasurer set a year\'s amount', function () {
    $treasurer = Member::factory()->for($this->club)->officer(ClubPosition::Treasurer)->withLogin()->create();

    $this->actingAs($treasurer->user)
        ->post(route('admin.clubs.dues-rates.store', $this->club), ['year' => 2026, 'amount' => '1200'])
        ->assertRedirect(route('admin.clubs.dues-rates.index', $this->club));

    expect($this->club->duesRateFor(2026))->toBe('1200.00');
});

it('replaces the amount when the same year is saved again', function () {
    DuesRate::factory()->for($this->club)->create(['year' => 2026, 'amount' => '1000.00']);

    $this->actingAs(User::factory()->districtAdmin()->create())
        ->post(route('admin.clubs.dues-rates.store', $this->club), ['year' => 2026, 'amount' => '1500']);

    expect($this->club->duesRates()->count())->toBe(1)
        ->and($this->club->duesRateFor(2026))->toBe('1500.00');
});

it('rejects a negative amount', function () {
    $this->actingAs(User::factory()->districtAdmin()->create())
        ->post(route('admin.clubs.dues-rates.store', $this->club), ['year' => 2026, 'amount' => '-1'])
        ->assertSessionHasErrors(['amount' => 'The amount field must be at least 0.']);
});

it('lets other officers view but not change the rates', function () {
    $secretary = Member::factory()->for($this->club)->officer(ClubPosition::Secretary)->withLogin()->create();

    $this->actingAs($secretary->user)
        ->get(route('admin.clubs.dues-rates.index', $this->club))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('canManage', false));

    $this->actingAs($secretary->user)
        ->post(route('admin.clubs.dues-rates.store', $this->club), ['year' => 2026, 'amount' => '1'])
        ->assertForbidden();
});

it('forbids officers of another club and regular members', function () {
    $outsider = Member::factory()->officer(ClubPosition::President)->withLogin()->create();
    $member = Member::factory()->for($this->club)->withLogin()->create();

    $this->actingAs($outsider->user)
        ->get(route('admin.clubs.dues-rates.index', $this->club))
        ->assertForbidden();

    $this->actingAs($member->user)
        ->get(route('admin.clubs.dues-rates.index', $this->club))
        ->assertForbidden();
});

it('removes a rate', function () {
    $rate = DuesRate::factory()->for($this->club)->create();
    $president = Member::factory()->for($this->club)->officer(ClubPosition::President)->withLogin()->create();

    $this->actingAs($president->user)
        ->delete(route('admin.clubs.dues-rates.destroy', [$this->club, $rate]))
        ->assertRedirect(route('admin.clubs.dues-rates.index', $this->club));

    $this->assertModelMissing($rate);
});

it('returns 404 when removing another club\'s rate through this club', function () {
    $otherRate = DuesRate::factory()->create();
    $president = Member::factory()->for($this->club)->officer(ClubPosition::President)->withLogin()->create();

    $this->actingAs($president->user)
        ->delete(route('admin.clubs.dues-rates.destroy', [$this->club, $otherRate]))
        ->assertNotFound();

    $this->assertModelExists($otherRate);
});
