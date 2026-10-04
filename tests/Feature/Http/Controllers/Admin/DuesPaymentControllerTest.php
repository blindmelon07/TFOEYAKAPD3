<?php

use App\Enums\ClubPosition;
use App\Models\Club;
use App\Models\DuesPayment;
use App\Models\Member;

beforeEach(function () {
    $this->club = Club::factory()->create();
    $this->member = Member::factory()->for($this->club)->create();
});

it('lets the club treasurer record a payment', function () {
    $treasurer = Member::factory()->for($this->club)->officer(ClubPosition::Treasurer)->withLogin()->create();

    $this->actingAs($treasurer->user)
        ->post(route('admin.members.dues.store', $this->member), [
            'year' => 2026,
            'amount' => '1200.50',
            'paid_at' => '2026-03-15',
            'reference' => 'OR-0042',
        ])
        ->assertRedirect(route('admin.members.show', $this->member));

    $payment = $this->member->duesPayments()->sole();

    expect($payment)
        ->year->toBe(2026)
        ->amount->toBe('1200.50')
        ->reference->toBe('OR-0042')
        ->recorded_by->toBe($treasurer->user->id);
});

it('forbids officers without dues duties from recording payments', function () {
    $secretary = Member::factory()->for($this->club)->officer(ClubPosition::Secretary)->withLogin()->create();

    $this->actingAs($secretary->user)
        ->post(route('admin.members.dues.store', $this->member), [
            'year' => 2026,
            'amount' => '1000',
            'paid_at' => '2026-03-15',
        ])
        ->assertForbidden();

    expect($this->member->duesPayments()->count())->toBe(0);
});

it('rejects a payment dated in the future', function () {
    $treasurer = Member::factory()->for($this->club)->officer(ClubPosition::Treasurer)->withLogin()->create();

    $this->actingAs($treasurer->user)
        ->post(route('admin.members.dues.store', $this->member), [
            'year' => now()->year,
            'amount' => '1000',
            'paid_at' => now()->addDay()->format('Y-m-d'),
        ])
        ->assertSessionHasErrors(['paid_at' => 'The paid at field must be a date before or equal to today.']);
});

it('rejects a negative amount', function () {
    $treasurer = Member::factory()->for($this->club)->officer(ClubPosition::Treasurer)->withLogin()->create();

    $this->actingAs($treasurer->user)
        ->post(route('admin.members.dues.store', $this->member), [
            'year' => now()->year,
            'amount' => '-5',
            'paid_at' => now()->format('Y-m-d'),
        ])
        ->assertSessionHasErrors(['amount' => 'The amount field must be at least 0.']);
});

it('lets the club president remove a payment', function () {
    $president = Member::factory()->for($this->club)->officer(ClubPosition::President)->withLogin()->create();
    $payment = DuesPayment::factory()->for($this->member)->create();

    $this->actingAs($president->user)
        ->delete(route('admin.dues.destroy', $payment))
        ->assertRedirect(route('admin.members.show', $this->member));

    $this->assertModelMissing($payment);
});

it('forbids a treasurer of another club from removing a payment', function () {
    $otherTreasurer = Member::factory()->for(Club::factory())->officer(ClubPosition::Treasurer)->withLogin()->create();
    $payment = DuesPayment::factory()->for($this->member)->create();

    $this->actingAs($otherTreasurer->user)
        ->delete(route('admin.dues.destroy', $payment))
        ->assertForbidden();

    $this->assertModelExists($payment);
});
