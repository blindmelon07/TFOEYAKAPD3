<?php

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
    Storage::fake('public');
});

it('renders the settings form with current values', function () {
    Setting::store(['hero_title' => 'District III']);

    $this->get(route('admin.settings.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/settings')
            ->where('settings.hero_title', 'District III')
            ->where('imageUrls.logo', Setting::defaults()['logo']));
});

it('shows a newly uploaded logo in the admin sidebar', function () {
    $this->put(route('admin.settings.update'), ['logo' => UploadedFile::fake()->image('seal.png')]);

    $this->get(route('admin.pillars.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('siteLogo', Storage::disk('public')->url(Setting::values()['logo'])));
});

it('saves text settings and shows them on the landing page', function () {
    $this->put(route('admin.settings.update'), [
        'hero_title' => 'District III',
        'contact_email' => 'hello@district3.ph',
    ])->assertRedirect(route('admin.settings.edit'));

    $this->assertDatabaseHas('settings', ['key' => 'hero_title', 'value' => 'District III']);

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('settings.hero_title', 'District III')
            ->where('settings.contact_email', 'hello@district3.ph'));
});

it('stores a cleared field as empty so the landing page hides it', function () {
    $this->put(route('admin.settings.update'), ['hero_motto' => '']);

    expect(Setting::values()['hero_motto'])->toBeNull();
});

it('leaves settings that were not submitted unchanged', function () {
    Setting::store(['hero_tagline' => 'Unity']);

    $this->put(route('admin.settings.update'), ['hero_title' => 'District III']);

    expect(Setting::values()['hero_tagline'])->toBe('Unity');
});

it('uploads a new logo and deletes the previously uploaded one', function () {
    $this->put(route('admin.settings.update'), ['logo' => UploadedFile::fake()->image('first.png')]);
    $firstPath = Setting::values()['logo'];

    $this->put(route('admin.settings.update'), ['logo' => UploadedFile::fake()->image('second.png')]);
    $secondPath = Setting::values()['logo'];

    Storage::disk('public')->assertMissing($firstPath);
    Storage::disk('public')->assertExists($secondPath);
    expect($secondPath)->toStartWith('site/');
});

it('keeps the bundled default image when a new one is uploaded', function () {
    $this->put(route('admin.settings.update'), ['hero_background' => UploadedFile::fake()->image('eagle.jpg')]);

    expect(file_exists(public_path(Setting::defaults()['hero_background'])))->toBeTrue();
});

it('rejects a logo that is not an image', function () {
    $this->put(route('admin.settings.update'), [
        'logo' => UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf'),
    ])->assertSessionHasErrors(['logo' => 'The logo field must be an image.']);
});

it('rejects an invalid contact email', function () {
    $this->put(route('admin.settings.update'), ['contact_email' => 'not-an-email'])
        ->assertSessionHasErrors(['contact_email' => 'The contact email field must be a valid email address.']);
});
