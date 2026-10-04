<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ClubLetterheadRequest;
use App\Models\Club;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ClubLetterheadController extends Controller
{
    /**
     * Download the letterhead the club's documents currently print on, so it
     * can be edited in Word and uploaded again.
     */
    public function show(Club $club): BinaryFileResponse
    {
        Gate::authorize('manageDocuments', $club);

        $name = $club->hasOwnLetterhead()
            ? Str::slug($club->name).'-letterhead.docx'
            : 'district-letterhead.docx';

        return response()->download($club->letterheadTemplatePath(), $name);
    }

    /**
     * Replace the shared letterhead with the club's own .docx template.
     */
    public function store(ClubLetterheadRequest $request, Club $club): RedirectResponse
    {
        if ($club->letterhead_path) {
            Storage::disk('local')->delete($club->letterhead_path);
        }

        $path = $request->file('letterhead')?->store('letterheads', 'local');

        $club->forceFill(['letterhead_path' => $path ?: null])->save();

        Inertia::flash('success', "{$club->name} now prints on its own letterhead.");

        return to_route('admin.clubs.documents.index', $club);
    }

    /**
     * Remove the club's letterhead so it goes back to the shared one.
     */
    public function destroy(Club $club): RedirectResponse
    {
        Gate::authorize('manageLetterhead', $club);

        if ($club->letterhead_path) {
            Storage::disk('local')->delete($club->letterhead_path);
        }

        $club->forceFill(['letterhead_path' => null])->save();

        Inertia::flash('success', "{$club->name} is back on the district letterhead.");

        return to_route('admin.clubs.documents.index', $club);
    }
}
