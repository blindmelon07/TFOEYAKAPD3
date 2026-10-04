<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\User;
use App\Services\Reports\ClubReport;
use App\Services\Reports\ReportRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportsController extends Controller
{
    /**
     * Show district admins every club's reports, or send an officer straight
     * to their own club's reports.
     */
    public function __invoke(Request $request, ReportRegistry $reports): Response|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user->isDistrictAdmin()) {
            abort_unless($user->isClubOfficer() && $user->member, 403);

            return to_route('admin.clubs.reports.index', $user->member->club_id);
        }

        return Inertia::render('admin/reports', [
            'clubs' => Club::query()
                ->withCount('members')
                ->orderBy('name')
                ->get()
                ->map(fn (Club $club): array => [
                    'id' => $club->id,
                    'name' => $club->name,
                    'location' => $club->location,
                    'logo_url' => $club->logo_url,
                    'members_count' => $club->getAttribute('members_count'),
                ]),
            'reports' => array_map(fn (ClubReport $report): array => $report->card(), $reports->all()),
        ]);
    }
}
