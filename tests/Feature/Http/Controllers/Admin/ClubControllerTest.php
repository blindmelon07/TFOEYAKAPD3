<?php

use App\Enums\ClubPosition;
use App\Models\Club;
use App\Models\Member;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

it('lists clubs with their officers and member counts', function () {
    $club = Club::factory()->create();
    Member::factory()->for($club)->officer(ClubPosition::President)->create(['first_name' => 'Jose', 'middle_name' => null, 'last_name' => 'Rizal']);
    Member::factory()->for($club)->count(2)->create();

    $this->actingAs(User::factory()->districtAdmin()->create())
        ->get(route('admin.clubs.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/clubs/index')
            ->where('clubs.0.members_count', 3)
            ->has('clubs.0.officers', 1)
            ->where('clubs.0.officers.0.full_name', 'Jose Rizal')
            ->where('clubs.0.officers.0.position', 'president')
            ->where('clubs.0.officers.0.position_label', 'Club President')
            ->has('offices', 5)
            ->where('offices.0', ['value' => 'president', 'label' => 'Club President'])
            ->where('offices.4', ['value' => 'auditor', 'label' => 'Club Auditor']));
});

it('creates a club', function () {
    $this->actingAs(User::factory()->districtAdmin()->create())
        ->post(route('admin.clubs.store'), ['name' => 'Bulan Eagles Club', 'location' => 'Bulan'])
        ->assertRedirect(route('admin.clubs.index'));

    $this->assertDatabaseHas('clubs', ['name' => 'Bulan Eagles Club', 'location' => 'Bulan']);
});

it('rejects a duplicate club name', function () {
    Club::factory()->create(['name' => 'Bulan Eagles Club']);

    $this->actingAs(User::factory()->districtAdmin()->create())
        ->post(route('admin.clubs.store'), ['name' => 'Bulan Eagles Club'])
        ->assertSessionHasErrors(['name' => 'The name has already been taken.']);
});

it('updates a club', function () {
    $club = Club::factory()->create();

    $this->actingAs(User::factory()->districtAdmin()->create())
        ->put(route('admin.clubs.update', $club), ['name' => 'Renamed Eagles Club'])
        ->assertRedirect(route('admin.clubs.index'));

    expect($club->fresh()->name)->toBe('Renamed Eagles Club');
});

it('deletes an empty club', function () {
    $club = Club::factory()->create();

    $this->actingAs(User::factory()->districtAdmin()->create())
        ->delete(route('admin.clubs.destroy', $club))
        ->assertRedirect(route('admin.clubs.index'));

    $this->assertModelMissing($club);
});

it('refuses to delete a club that still has members', function () {
    $club = Club::factory()->create(['name' => 'Bulan Eagles Club']);
    Member::factory()->for($club)->create();

    $this->actingAs(User::factory()->districtAdmin()->create())
        ->delete(route('admin.clubs.destroy', $club))
        ->assertSessionHasErrors(['club' => 'Bulan Eagles Club still has members. Move or remove them before deleting the club.']);

    $this->assertModelExists($club);
});

it('forbids club officers from managing clubs', function () {
    $president = Member::factory()->officer(ClubPosition::President)->withLogin()->create();

    $this->actingAs($president->user)
        ->get(route('admin.clubs.index'))
        ->assertForbidden();

    $this->actingAs($president->user)
        ->post(route('admin.clubs.store'), ['name' => 'Rogue Club'])
        ->assertForbidden();
});

describe('logo', function () {
    beforeEach(function () {
        Storage::fake('public');
        $this->actingAs(User::factory()->districtAdmin()->create());
    });

    it('stores an uploaded logo when creating a club', function () {
        $this->post(route('admin.clubs.store'), [
            'name' => 'Bulan Eagles Club',
            'logo' => UploadedFile::fake()->image('seal.png'),
        ])->assertRedirect(route('admin.clubs.index'));

        $club = Club::query()->sole();

        expect($club->logo_path)->toStartWith('clubs/');
        Storage::disk('public')->assertExists($club->logo_path);

        $this->get(route('admin.clubs.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('clubs.0.logo_url', Storage::disk('public')->url($club->logo_path)));
    });

    it('replaces the logo and deletes the old file', function () {
        Storage::disk('public')->put('clubs/old.png', 'old');
        $club = Club::factory()->create(['logo_path' => 'clubs/old.png']);

        $this->put(route('admin.clubs.update', $club), [
            'name' => $club->name,
            'logo' => UploadedFile::fake()->image('new.png'),
        ]);

        Storage::disk('public')->assertMissing('clubs/old.png');
        Storage::disk('public')->assertExists($club->fresh()->logo_path);
    });

    it('removes the logo when requested', function () {
        Storage::disk('public')->put('clubs/old.png', 'old');
        $club = Club::factory()->create(['logo_path' => 'clubs/old.png']);

        $this->put(route('admin.clubs.update', $club), [
            'name' => $club->name,
            'remove_logo' => '1',
        ]);

        expect($club->fresh()->logo_path)->toBeNull();
        Storage::disk('public')->assertMissing('clubs/old.png');
    });

    it('deletes the logo file along with the club', function () {
        Storage::disk('public')->put('clubs/old.png', 'old');
        $club = Club::factory()->create(['logo_path' => 'clubs/old.png']);

        $this->delete(route('admin.clubs.destroy', $club));

        Storage::disk('public')->assertMissing('clubs/old.png');
    });

    it('rejects a logo that is not an image', function () {
        $this->post(route('admin.clubs.store'), [
            'name' => 'Bulan Eagles Club',
            'logo' => UploadedFile::fake()->create('logo.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors(['logo' => 'The logo field must be an image.']);
    });
});
