<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MissionRequest;
use App\Models\Mission;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class MissionController extends Controller
{
    /**
     * List every mission.
     */
    public function index(): Response
    {
        return Inertia::render('admin/content/index', [
            'resource' => 'missions',
            'items' => Mission::query()->ordered()->get(),
        ]);
    }

    /**
     * Show the form for creating a mission.
     */
    public function create(): Response
    {
        return Inertia::render('admin/content/form', [
            'resource' => 'missions',
            'item' => null,
            'nextSortOrder' => ((int) Mission::query()->max('sort_order')) + 10,
        ]);
    }

    /**
     * Store a newly created mission.
     */
    public function store(MissionRequest $request): RedirectResponse
    {
        $attributes = $request->safe()->except(['image', 'remove_image']);

        if ($request->hasFile('image')) {
            $attributes['image_path'] = Mission::storeUploadedAsset($request->file('image'), 'missions', 'image');
        }

        Mission::query()->create($attributes);

        Inertia::flash('success', 'Mission added.');

        return to_route('admin.missions.index');
    }

    /**
     * Show the form for editing the given mission.
     */
    public function edit(Mission $mission): Response
    {
        return Inertia::render('admin/content/form', [
            'resource' => 'missions',
            'item' => $mission,
        ]);
    }

    /**
     * Update the given mission, replacing or removing its photo if requested.
     */
    public function update(MissionRequest $request, Mission $mission): RedirectResponse
    {
        $attributes = $request->safe()->except(['image', 'remove_image']);

        if ($request->hasFile('image')) {
            Mission::deleteUploadedAsset($mission->image_path);
            $attributes['image_path'] = Mission::storeUploadedAsset($request->file('image'), 'missions', 'image');
        } elseif ($request->boolean('remove_image')) {
            Mission::deleteUploadedAsset($mission->image_path);
            $attributes['image_path'] = null;
        }

        $mission->update($attributes);

        Inertia::flash('success', 'Mission updated.');

        return to_route('admin.missions.index');
    }

    /**
     * Delete the given mission and its uploaded photo.
     */
    public function destroy(Mission $mission): RedirectResponse
    {
        Mission::deleteUploadedAsset($mission->image_path);

        $mission->delete();

        Inertia::flash('success', 'Mission deleted.');

        return to_route('admin.missions.index');
    }
}
