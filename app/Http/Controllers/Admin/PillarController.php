<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PillarRequest;
use App\Models\Pillar;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PillarController extends Controller
{
    /**
     * List every pillar.
     */
    public function index(): Response
    {
        return Inertia::render('admin/content/index', [
            'resource' => 'pillars',
            'items' => Pillar::query()->ordered()->get(),
        ]);
    }

    /**
     * Show the form for creating a pillar.
     */
    public function create(): Response
    {
        return Inertia::render('admin/content/form', [
            'resource' => 'pillars',
            'item' => null,
            'nextSortOrder' => ((int) Pillar::query()->max('sort_order')) + 10,
        ]);
    }

    /**
     * Store a newly created pillar.
     */
    public function store(PillarRequest $request): RedirectResponse
    {
        Pillar::query()->create($request->validated());

        Inertia::flash('success', 'Pillar added.');

        return to_route('admin.pillars.index');
    }

    /**
     * Show the form for editing the given pillar.
     */
    public function edit(Pillar $pillar): Response
    {
        return Inertia::render('admin/content/form', [
            'resource' => 'pillars',
            'item' => $pillar,
        ]);
    }

    /**
     * Update the given pillar.
     */
    public function update(PillarRequest $request, Pillar $pillar): RedirectResponse
    {
        $pillar->update($request->validated());

        Inertia::flash('success', 'Pillar updated.');

        return to_route('admin.pillars.index');
    }

    /**
     * Delete the given pillar.
     */
    public function destroy(Pillar $pillar): RedirectResponse
    {
        $pillar->delete();

        Inertia::flash('success', 'Pillar deleted.');

        return to_route('admin.pillars.index');
    }
}
