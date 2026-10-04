<?php

use App\Models\FundAllocation;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->districtAdmin()->create());
});

it('creates an allocation', function () {
    $this->post(route('admin.fund-allocations.store'), [
        'label' => 'Health',
        'percentage' => 45,
        'color' => 'primary',
        'sort_order' => 10,
    ])->assertRedirect(route('admin.fund-allocations.index'));

    $this->assertDatabaseHas('fund_allocations', ['label' => 'Health', 'percentage' => 45, 'color' => 'primary']);
});

it('rejects a percentage above 100', function () {
    $this->post(route('admin.fund-allocations.store'), [
        ...FundAllocation::factory()->raw(),
        'percentage' => 101,
    ])->assertSessionHasErrors(['percentage' => 'The percentage field must not be greater than 100.']);
});

it('rejects a colour outside the palette', function () {
    $this->post(route('admin.fund-allocations.store'), [
        ...FundAllocation::factory()->raw(),
        'color' => 'hotpink',
    ])->assertSessionHasErrors(['color' => 'The selected color is invalid.']);
});

it('updates and deletes an allocation', function () {
    $allocation = FundAllocation::factory()->create();

    $this->put(route('admin.fund-allocations.update', $allocation), [
        ...FundAllocation::factory()->raw(),
        'percentage' => 30,
    ])->assertRedirect(route('admin.fund-allocations.index'));

    expect($allocation->fresh()->percentage)->toBe(30);

    $this->delete(route('admin.fund-allocations.destroy', $allocation));

    $this->assertModelMissing($allocation);
});
