<?php

use App\Models\Mission;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
    Storage::fake('public');
});

describe('store', function () {
    it('creates a mission with an uploaded photo', function () {
        $this->post(route('admin.missions.store'), [
            ...Mission::factory()->raw(['title' => 'Coastal Cleanup']),
            'image' => UploadedFile::fake()->image('cleanup.jpg'),
        ])->assertRedirect(route('admin.missions.index'));

        $mission = Mission::query()->sole();

        expect($mission->title)->toBe('Coastal Cleanup')
            ->and($mission->image_path)->toStartWith('missions/');
        Storage::disk('public')->assertExists($mission->image_path);
    });

    it('creates a mission without a photo', function () {
        $this->post(route('admin.missions.store'), Mission::factory()->raw())
            ->assertRedirect(route('admin.missions.index'));

        expect(Mission::query()->sole()->image_path)->toBeNull();
    });

    it('rejects a photo larger than 5 MB', function () {
        $this->post(route('admin.missions.store'), [
            ...Mission::factory()->raw(),
            'image' => UploadedFile::fake()->image('huge.jpg')->size(6000),
        ])->assertSessionHasErrors(['image' => 'The image field must not be greater than 5120 kilobytes.']);
    });
});

describe('update', function () {
    it('replaces the photo and deletes the old upload', function () {
        Storage::disk('public')->put('missions/old.jpg', 'old');
        $mission = Mission::factory()->create(['image_path' => 'missions/old.jpg']);

        $this->put(route('admin.missions.update', $mission), [
            ...Mission::factory()->raw(),
            'image' => UploadedFile::fake()->image('new.jpg'),
        ])->assertRedirect(route('admin.missions.index'));

        Storage::disk('public')->assertMissing('missions/old.jpg');
        Storage::disk('public')->assertExists($mission->fresh()->image_path);
    });

    it('keeps the current photo when no new one is uploaded', function () {
        $mission = Mission::factory()->create(['image_path' => 'missions/keep.jpg']);

        $this->put(route('admin.missions.update', $mission), Mission::factory()->raw());

        expect($mission->fresh()->image_path)->toBe('missions/keep.jpg');
    });

    it('removes the photo when requested', function () {
        Storage::disk('public')->put('missions/old.jpg', 'old');
        $mission = Mission::factory()->create(['image_path' => 'missions/old.jpg']);

        $this->put(route('admin.missions.update', $mission), [
            ...Mission::factory()->raw(),
            'remove_image' => '1',
        ]);

        expect($mission->fresh()->image_path)->toBeNull();
        Storage::disk('public')->assertMissing('missions/old.jpg');
    });
});

describe('destroy', function () {
    it('deletes the mission and its uploaded photo', function () {
        Storage::disk('public')->put('missions/old.jpg', 'old');
        $mission = Mission::factory()->create(['image_path' => 'missions/old.jpg']);

        $this->delete(route('admin.missions.destroy', $mission))
            ->assertRedirect(route('admin.missions.index'));

        $this->assertModelMissing($mission);
        Storage::disk('public')->assertMissing('missions/old.jpg');
    });

    it('does not try to delete an externally hosted photo', function () {
        $mission = Mission::factory()->create(['image_path' => 'https://example.com/photo.jpg']);

        $this->delete(route('admin.missions.destroy', $mission))
            ->assertRedirect(route('admin.missions.index'));

        $this->assertModelMissing($mission);
    });
});
