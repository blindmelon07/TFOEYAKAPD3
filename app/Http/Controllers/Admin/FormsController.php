<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FormsController extends Controller
{
    /**
     * Show every club's forms to district admins, or send an officer straight
     * to their own club's forms.
     */
    public function __invoke(Request $request): Response|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user->isDistrictAdmin()) {
            abort_unless($user->isClubOfficer() && $user->member, 403);

            return to_route('admin.clubs.documents.index', $user->member->club_id);
        }

        $clubs = Club::query()
            ->withCount('documents')
            ->withMax('documents', 'document_date')
            ->orderBy('name')
            ->get()
            ->map(fn (Club $club): array => [
                'id' => $club->id,
                'name' => $club->name,
                'location' => $club->location,
                'logo_url' => $club->logo_url,
                'documents_count' => $club->getAttribute('documents_count'),
                'latest_document_date' => $club->getAttribute('documents_max_document_date'),
                'has_own_letterhead' => $club->hasOwnLetterhead(),
            ]);

        return Inertia::render('admin/forms', [
            'clubs' => $clubs,
        ]);
    }
}
