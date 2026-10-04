<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ClubPosition;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ClubRequest;
use App\Models\Club;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ClubController extends Controller
{
    /**
     * List every club with its officers and member count.
     */
    public function index(): Response
    {
        Gate::authorize('viewAny', Club::class);

        $clubs = Club::query()
            ->withCount('members')
            ->with(['members' => fn ($query) => $query
                ->whereIn('position', ClubPosition::offices())
                ->rosterOrder()])
            ->orderBy('name')
            ->get()
            ->map(fn (Club $club): array => [
                'id' => $club->id,
                'name' => $club->name,
                'location' => $club->location,
                'charter_number' => $club->charter_number,
                'logo_url' => $club->logo_url,
                'members_count' => $club->getAttribute('members_count'),
                'officers' => $club->members->map(fn (Member $officer): array => [
                    'id' => $officer->id,
                    'full_name' => $officer->full_name,
                    'photo_url' => $officer->photo_url,
                    'position' => $officer->position->value,
                    'position_label' => $officer->position->label(),
                ]),
            ]);

        return Inertia::render('admin/clubs/index', [
            'clubs' => $clubs,
            'offices' => array_map(
                fn (ClubPosition $office): array => ['value' => $office->value, 'label' => $office->label()],
                ClubPosition::offices(),
            ),
        ]);
    }

    /**
     * Show the form for creating a club.
     */
    public function create(): Response
    {
        Gate::authorize('create', Club::class);

        return Inertia::render('admin/clubs/form', ['club' => null]);
    }

    /**
     * Store a newly created club.
     */
    public function store(ClubRequest $request): RedirectResponse
    {
        $club = new Club($request->safe()->except(['logo', 'remove_logo']));

        if ($request->hasFile('logo')) {
            $club->logo_path = Club::storeUploadedAsset($request->file('logo'), 'clubs', 'logo');
        }

        $club->save();

        Inertia::flash('success', "{$club->name} created.");

        return to_route('admin.clubs.index');
    }

    /**
     * Show the form for editing the club.
     */
    public function edit(Club $club): Response
    {
        Gate::authorize('update', $club);

        return Inertia::render('admin/clubs/form', [
            'club' => [
                ...$club->only(['id', 'name', 'location', 'charter_number']),
                'logo_url' => $club->logo_url,
            ],
        ]);
    }

    /**
     * Update the club.
     */
    public function update(ClubRequest $request, Club $club): RedirectResponse
    {
        $club->fill($request->safe()->except(['logo', 'remove_logo']));

        if ($request->hasFile('logo')) {
            Club::deleteUploadedAsset($club->logo_path);
            $club->logo_path = Club::storeUploadedAsset($request->file('logo'), 'clubs', 'logo');
        } elseif ($request->boolean('remove_logo')) {
            Club::deleteUploadedAsset($club->logo_path);
            $club->logo_path = null;
        }

        $club->save();

        Inertia::flash('success', "{$club->name} updated.");

        return to_route('admin.clubs.index');
    }

    /**
     * Delete the club if it has no members left.
     */
    public function destroy(Club $club): RedirectResponse
    {
        Gate::authorize('delete', $club);

        if ($club->members()->exists()) {
            return back()->withErrors([
                'club' => "{$club->name} still has members. Move or remove them before deleting the club.",
            ]);
        }

        Club::deleteUploadedAsset($club->logo_path);
        $club->delete();

        Inertia::flash('success', "{$club->name} deleted.");

        return to_route('admin.clubs.index');
    }
}
