<?php

use App\Models\ChapterStat;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('creates a stat', function () {
    $this->post(route('admin.stats.store'), ['value' => '500+', 'label' => 'Members', 'sort_order' => 10])
        ->assertRedirect(route('admin.stats.index'));

    $this->assertDatabaseHas('chapter_stats', ['value' => '500+', 'label' => 'Members']);
});

it('requires a value and a label', function () {
    $this->post(route('admin.stats.store'), ['sort_order' => 10])
        ->assertSessionHasErrors(['value', 'label']);
});

it('updates and deletes a stat', function () {
    $stat = ChapterStat::factory()->create();

    $this->put(route('admin.stats.update', $stat), ['value' => '600+', 'label' => 'Members', 'sort_order' => 10])
        ->assertRedirect(route('admin.stats.index'));

    expect($stat->fresh()->value)->toBe('600+');

    $this->delete(route('admin.stats.destroy', $stat));

    $this->assertModelMissing($stat);
});
