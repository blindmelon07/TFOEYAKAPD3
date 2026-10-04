<?php

use App\Enums\MemberStatus;
use App\Models\Member;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

it('renders the login page with the current site logo', function () {
    Setting::store(['logo' => 'site/new-seal.png']);

    $this->get(route('login'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('auth/login')
            ->where('siteLogo', Storage::disk('public')->url('site/new-seal.png')));
});

it('signs an administrator in and sends them to the admin panel', function () {
    $user = User::factory()->create();

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($user);
});

it('rejects an incorrect password', function () {
    $user = User::factory()->create();

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'wrong-password'])
        ->assertSessionHasErrors(['email' => 'These credentials do not match our records.']);

    $this->assertGuest();
});

it('locks the email out after five failed attempts', function () {
    $user = User::factory()->create();

    foreach (range(1, 5) as $attempt) {
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'wrong-password']);
    }

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
        ->assertSessionHasErrors('email');

    $this->assertGuest();
    expect(session('errors')->first('email'))->toStartWith('Too many login attempts.');
});

it('redirects a signed-in administrator away from the login page', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('login'))
        ->assertRedirect(route('admin.dashboard'));
});

it('signs the administrator out', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('admin.logout'))
        ->assertRedirect(route('home'));

    $this->assertGuest();
});

it('refuses to sign in a member whose membership is suspended or deceased', function (MemberStatus $status) {
    $member = Member::factory()->withLogin()->create(['status' => $status]);

    $this->post(route('login.store'), ['email' => $member->email, 'password' => 'password'])
        ->assertSessionHasErrors(['email' => "Your membership is {$status->value}. Please contact your club officers."]);

    $this->assertGuest();
})->with([MemberStatus::Suspended, MemberStatus::Deceased]);

it('signs in an inactive member', function () {
    $member = Member::factory()->withLogin()->create(['status' => MemberStatus::Inactive]);

    $this->post(route('login.store'), ['email' => $member->email, 'password' => 'password']);

    $this->assertAuthenticatedAs($member->user);
});
