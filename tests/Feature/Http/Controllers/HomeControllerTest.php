<?php

use App\Models\ChapterStat;
use App\Models\FundAllocation;
use App\Models\MembershipStep;
use App\Models\Mission;
use App\Models\Pillar;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

it('renders the landing page from admin-managed content', function () {
    Setting::store(['hero_title' => 'SORSOGON DISTRICT III']);
    ChapterStat::factory()->create(['value' => '42', 'label' => 'Chapters']);
    Pillar::factory()->create(['title' => 'Brotherhood']);
    Mission::factory()->create(['title' => 'Medical Mission']);
    FundAllocation::factory()->create(['label' => 'Health', 'percentage' => 60]);
    MembershipStep::factory()->create(['title' => 'Sponsorship']);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('welcome')
            ->where('settings.hero_title', 'SORSOGON DISTRICT III')
            ->where('stats.0.value', '42')
            ->where('pillars.0.title', 'Brotherhood')
            ->where('missions.0.title', 'Medical Mission')
            ->where('fundAllocations.0.percentage', 60)
            ->where('membershipSteps.0.title', 'Sponsorship'));
});

it('falls back to the default copy for settings that were never saved', function () {
    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('settings.hero_title', Setting::defaults()['hero_title'])
            ->where('settings.logo', Setting::defaults()['logo']));
});

it('lists content in admin-defined position order', function () {
    Pillar::factory()->create(['title' => 'Second', 'sort_order' => 20]);
    Pillar::factory()->create(['title' => 'First', 'sort_order' => 10]);

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('pillars.0.title', 'First')
            ->where('pillars.1.title', 'Second'));
});

it('serves uploaded images from the public disk and leaves absolute URLs untouched', function () {
    Mission::factory()->create(['image_path' => 'missions/photo.jpg', 'sort_order' => 10]);
    Mission::factory()->create(['image_path' => 'https://example.com/photo.jpg', 'sort_order' => 20]);

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('missions.0.image_url', Storage::disk('public')->url('missions/photo.jpg'))
            ->where('missions.1.image_url', 'https://example.com/photo.jpg'));
});
