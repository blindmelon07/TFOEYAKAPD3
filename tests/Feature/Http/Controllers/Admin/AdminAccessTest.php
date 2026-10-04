<?php

use App\Enums\ClubPosition;
use App\Models\Member;

it('redirects guests to the login page', function (string $routeName) {
    $this->get(route($routeName))->assertRedirect(route('login'));
})->with([
    'admin.settings.edit',
    'admin.account.edit',
    'admin.stats.index',
    'admin.pillars.index',
    'admin.missions.index',
    'admin.fund-allocations.index',
    'admin.membership-steps.index',
]);

it('rejects content changes from guests', function () {
    $this->put(route('admin.settings.update'), ['hero_title' => 'Hacked'])
        ->assertRedirect(route('login'));

    $this->assertDatabaseMissing('settings', ['value' => 'Hacked']);
});

it('forbids members and club officers from landing page content', function (string $routeName) {
    $president = Member::factory()->officer(ClubPosition::President)->withLogin()->create();

    $this->actingAs($president->user)->get(route($routeName))->assertForbidden();
})->with([
    'admin.settings.edit',
    'admin.stats.index',
    'admin.pillars.index',
    'admin.missions.index',
    'admin.fund-allocations.index',
    'admin.membership-steps.index',
]);
