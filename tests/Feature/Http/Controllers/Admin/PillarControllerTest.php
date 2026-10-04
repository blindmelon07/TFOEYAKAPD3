<?php

use App\Models\Pillar;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

describe('index', function () {
    it('lists pillars in position order', function () {
        Pillar::factory()->create(['title' => 'Second', 'sort_order' => 20]);
        Pillar::factory()->create(['title' => 'First', 'sort_order' => 10]);

        $this->get(route('admin.pillars.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/content/index')
                ->where('resource', 'pillars')
                ->where('items.0.title', 'First')
                ->where('items.1.title', 'Second'));
    });
});

describe('create', function () {
    it('suggests a position after the last pillar', function () {
        Pillar::factory()->create(['sort_order' => 30]);

        $this->get(route('admin.pillars.create'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/content/form')
                ->where('item', null)
                ->where('nextSortOrder', 40));
    });
});

describe('store', function () {
    it('creates a pillar', function () {
        $this->post(route('admin.pillars.store'), [
            'icon' => 'diversity_3',
            'tag' => 'Pillar I • Kapatiran',
            'title' => 'Brotherhood',
            'description' => 'Shoulder to shoulder.',
            'sort_order' => 10,
        ])->assertRedirect(route('admin.pillars.index'));

        $this->assertDatabaseHas('pillars', ['title' => 'Brotherhood', 'icon' => 'diversity_3', 'sort_order' => 10]);
    });

    it('requires every field', function () {
        $this->post(route('admin.pillars.store'), [])
            ->assertSessionHasErrors(['icon', 'tag', 'title', 'description', 'sort_order']);
    });

    it('rejects an icon name that is not a Material Symbol identifier', function () {
        $this->post(route('admin.pillars.store'), [
            ...Pillar::factory()->raw(),
            'icon' => '<script>',
        ])->assertSessionHasErrors(['icon' => 'The icon field format is invalid.']);
    });
});

describe('edit', function () {
    it('renders the form with the pillar', function () {
        $pillar = Pillar::factory()->create(['title' => 'Patriotism']);

        $this->get(route('admin.pillars.edit', $pillar))
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/content/form')
                ->where('item.title', 'Patriotism'));
    });
});

describe('update', function () {
    it('updates the pillar', function () {
        $pillar = Pillar::factory()->create();

        $this->put(route('admin.pillars.update', $pillar), [
            ...Pillar::factory()->raw(),
            'title' => 'Integrity & Honor',
        ])->assertRedirect(route('admin.pillars.index'));

        expect($pillar->fresh()->title)->toBe('Integrity & Honor');
    });
});

describe('destroy', function () {
    it('deletes the pillar', function () {
        $pillar = Pillar::factory()->create();

        $this->delete(route('admin.pillars.destroy', $pillar))
            ->assertRedirect(route('admin.pillars.index'));

        $this->assertModelMissing($pillar);
    });
});
