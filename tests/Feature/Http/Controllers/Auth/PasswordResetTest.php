<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Notification::fake();
});

it('renders the forgot password page', function () {
    $this->get(route('password.request'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('auth/forgot-password'));
});

it('emails a reset link to an existing account', function () {
    $user = User::factory()->create();

    $this->post(route('password.email'), ['email' => $user->email])
        ->assertSessionHasNoErrors();

    Notification::assertSentTo($user, ResetPassword::class);
});

it('responds the same way for an unknown email without sending anything', function () {
    $this->post(route('password.email'), ['email' => 'nobody@example.com'])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    Notification::assertNothingSent();
});

it('renders the reset form with the token and email', function () {
    $this->get(route('password.reset', ['token' => 'abc123', 'email' => 'juan@example.com']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('auth/reset-password')
            ->where('token', 'abc123')
            ->where('email', 'juan@example.com'));
});

it('resets the password with a valid token and sends the user to sign in', function () {
    $user = User::factory()->create();
    $token = Password::createToken($user);

    $this->post(route('password.store'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'brand-new-password',
        'password_confirmation' => 'brand-new-password',
    ])->assertRedirect(route('login'));

    expect(Hash::check('brand-new-password', $user->fresh()->password))->toBeTrue();
});

it('rejects an invalid token', function () {
    $user = User::factory()->create();

    $this->post(route('password.store'), [
        'token' => 'not-a-real-token',
        'email' => $user->email,
        'password' => 'brand-new-password',
        'password_confirmation' => 'brand-new-password',
    ])->assertSessionHasErrors(['email' => 'This password reset token is invalid.']);

    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
});
