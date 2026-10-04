<?php

use App\Models\MembershipStep;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->districtAdmin()->create());
});

it('creates a step', function () {
    $this->post(route('admin.membership-steps.store'), [
        'icon' => 'group_add',
        'title' => 'Fraternal Sponsorship',
        'description' => 'Every applicant must be sponsored.',
        'requirement' => '1 Primary & 1 Co-Sponsor',
        'sort_order' => 10,
    ])->assertRedirect(route('admin.membership-steps.index'));

    $this->assertDatabaseHas('membership_steps', ['title' => 'Fraternal Sponsorship', 'requirement' => '1 Primary & 1 Co-Sponsor']);
});

it('requires every field', function () {
    $this->post(route('admin.membership-steps.store'), [])
        ->assertSessionHasErrors(['icon', 'title', 'description', 'requirement', 'sort_order']);
});

it('updates and deletes a step', function () {
    $step = MembershipStep::factory()->create();

    $this->put(route('admin.membership-steps.update', $step), [
        ...MembershipStep::factory()->raw(),
        'title' => 'Rite of Passage',
    ])->assertRedirect(route('admin.membership-steps.index'));

    expect($step->fresh()->title)->toBe('Rite of Passage');

    $this->delete(route('admin.membership-steps.destroy', $step));

    $this->assertModelMissing($step);
});
