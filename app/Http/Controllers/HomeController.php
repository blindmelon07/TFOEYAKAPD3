<?php

namespace App\Http\Controllers;

use App\Models\ChapterStat;
use App\Models\FundAllocation;
use App\Models\MembershipStep;
use App\Models\Mission;
use App\Models\Pillar;
use App\Models\Setting;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Render the public landing page from admin-managed content.
     */
    public function __invoke(): Response
    {
        return Inertia::render('welcome', [
            'settings' => Setting::publicValues(),
            'stats' => ChapterStat::query()->ordered()->get(['id', 'value', 'label']),
            'pillars' => Pillar::query()->ordered()->get(['id', 'icon', 'tag', 'title', 'description']),
            'missions' => Mission::query()->ordered()->get(),
            'fundAllocations' => FundAllocation::query()->ordered()->get(['id', 'label', 'percentage', 'color']),
            'membershipSteps' => MembershipStep::query()->ordered()->get(['id', 'icon', 'title', 'description', 'requirement']),
        ]);
    }
}
