<?php

use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('updates the name and email when the current password is correct', function () {
    $user = User::factory()->districtAdmin()->create();

    $this->actingAs($user)
        ->put(route('admin.account.update'), [
            'name' => 'Kuya Admin',
            'email' => 'kuya@district3.ph',
            'current_password' => 'password',
        ])->assertRedirect(route('admin.account.edit'));

    expect($user->fresh())
        ->name->toBe('Kuya Admin')
        ->email->toBe('kuya@district3.ph');
});

it('changes the password when a new one is confirmed', function () {
    $user = User::factory()->districtAdmin()->create();

    $this->actingAs($user)->put(route('admin.account.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'current_password' => 'password',
        'password' => 'new-secure-password',
        'password_confirmation' => 'new-secure-password',
    ]);

    expect(Hash::check('new-secure-password', $user->fresh()->password))->toBeTrue();
});

it('keeps the password when the new password is left blank', function () {
    $user = User::factory()->districtAdmin()->create();

    $this->actingAs($user)->put(route('admin.account.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'current_password' => 'password',
        'password' => '',
    ]);

    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
});

it('rejects changes when the current password is wrong', function () {
    $user = User::factory()->districtAdmin()->create(['name' => 'Original']);

    $this->actingAs($user)->put(route('admin.account.update'), [
        'name' => 'Changed',
        'email' => $user->email,
        'current_password' => 'wrong-password',
    ])->assertSessionHasErrors(['current_password' => 'The password is incorrect.']);

    expect($user->fresh()->name)->toBe('Original');
});

it('rejects an email already used by another administrator', function () {
    $other = User::factory()->districtAdmin()->create();
    $user = User::factory()->districtAdmin()->create();

    $this->actingAs($user)->put(route('admin.account.update'), [
        'name' => $user->name,
        'email' => $other->email,
        'current_password' => 'password',
    ])->assertSessionHasErrors(['email' => 'The email has already been taken.']);
});

it('lets a member change only their password, leaving name and email to their profile', function () {
    $member = Member::factory()->withLogin()->create();
    $user = $member->user;

    $this->actingAs($user)->put(route('admin.account.update'), [
        'name' => 'Someone Else',
        'email' => 'someone.else@example.com',
        'current_password' => 'password',
        'password' => 'new-secure-password',
        'password_confirmation' => 'new-secure-password',
    ])->assertSessionHasNoErrors();

    expect($user->fresh())
        ->email->toBe($member->email)
        ->name->not->toBe('Someone Else')
        ->and(Hash::check('new-secure-password', $user->fresh()->password))->toBeTrue();
});

it('requires a member to enter a new password', function () {
    $member = Member::factory()->withLogin()->create();

    $this->actingAs($member->user)->put(route('admin.account.update'), [
        'current_password' => 'password',
    ])->assertSessionHasErrors(['password' => 'The password field is required.']);
});
