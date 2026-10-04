<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ChapterStatRequest;
use App\Models\ChapterStat;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ChapterStatController extends Controller
{
    /**
     * List every chapter stat.
     */
    public function index(): Response
    {
        return Inertia::render('admin/content/index', [
            'resource' => 'stats',
            'items' => ChapterStat::query()->ordered()->get(),
        ]);
    }

    /**
     * Show the form for creating a chapter stat.
     */
    public function create(): Response
    {
        return Inertia::render('admin/content/form', [
            'resource' => 'stats',
            'item' => null,
            'nextSortOrder' => ((int) ChapterStat::query()->max('sort_order')) + 10,
        ]);
    }

    /**
     * Store a newly created chapter stat.
     */
    public function store(ChapterStatRequest $request): RedirectResponse
    {
        ChapterStat::query()->create($request->validated());

        Inertia::flash('success', 'Stat added.');

        return to_route('admin.stats.index');
    }

    /**
     * Show the form for editing the given chapter stat.
     */
    public function edit(ChapterStat $chapterStat): Response
    {
        return Inertia::render('admin/content/form', [
            'resource' => 'stats',
            'item' => $chapterStat,
        ]);
    }

    /**
     * Update the given chapter stat.
     */
    public function update(ChapterStatRequest $request, ChapterStat $chapterStat): RedirectResponse
    {
        $chapterStat->update($request->validated());

        Inertia::flash('success', 'Stat updated.');

        return to_route('admin.stats.index');
    }

    /**
     * Delete the given chapter stat.
     */
    public function destroy(ChapterStat $chapterStat): RedirectResponse
    {
        $chapterStat->delete();

        Inertia::flash('success', 'Stat deleted.');

        return to_route('admin.stats.index');
    }
}
