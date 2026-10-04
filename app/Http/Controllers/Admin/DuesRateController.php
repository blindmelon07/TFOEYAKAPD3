<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DuesStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DuesRateRequest;
use App\Models\Club;
use App\Models\DuesRate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DuesRateController extends Controller
{
    /**
     * List the club's yearly dues amounts with how many members have paid each.
     */
    public function index(Request $request, Club $club): Response
    {
        Gate::authorize('viewDuesRates', $club);

        $rates = $club->duesRates()
            ->orderByDesc('year')
            ->get()
            ->map(fn (DuesRate $rate): array => [
                'id' => $rate->id,
                'year' => $rate->year,
                'amount' => $rate->amount,
                'counts' => collect(DuesStatus::cases())->mapWithKeys(fn (DuesStatus $status): array => [
                    $status->value => $club->members()->whereDuesStatus($rate->year, $status)->count(),
                ]),
            ]);

        return Inertia::render('admin/clubs/dues-rates', [
            'club' => $club->only(['id', 'name']),
            'rates' => $rates,
            'currentYear' => now()->year,
            'canManage' => $request->user()?->can('manageDuesRates', $club) ?? false,
        ]);
    }

    /**
     * Set the club's dues amount for a year, replacing any existing amount.
     */
    public function store(DuesRateRequest $request, Club $club): RedirectResponse
    {
        $rate = $club->duesRates()->updateOrCreate(
            ['year' => $request->integer('year')],
            ['amount' => $request->validated('amount')],
        );

        Inertia::flash('success', "{$rate->year} dues set to ₱".number_format((float) $rate->amount, 2).'.');

        return to_route('admin.clubs.dues-rates.index', $club);
    }

    /**
     * Remove a year's dues amount, so any payment counts as paid for that year.
     */
    public function destroy(Club $club, DuesRate $duesRate): RedirectResponse
    {
        Gate::authorize('manageDuesRates', $club);

        $duesRate->delete();

        Inertia::flash('success', "{$duesRate->year} dues amount removed.");

        return to_route('admin.clubs.dues-rates.index', $club);
    }
}
