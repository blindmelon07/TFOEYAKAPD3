<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FundAllocationRequest;
use App\Models\FundAllocation;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class FundAllocationController extends Controller
{
    /**
     * List every fund allocation.
     */
    public function index(): Response
    {
        return Inertia::render('admin/content/index', [
            'resource' => 'fund-allocations',
            'items' => FundAllocation::query()->ordered()->get(),
        ]);
    }

    /**
     * Show the form for creating a fund allocation.
     */
    public function create(): Response
    {
        return Inertia::render('admin/content/form', [
            'resource' => 'fund-allocations',
            'item' => null,
            'nextSortOrder' => ((int) FundAllocation::query()->max('sort_order')) + 10,
        ]);
    }

    /**
     * Store a newly created fund allocation.
     */
    public function store(FundAllocationRequest $request): RedirectResponse
    {
        FundAllocation::query()->create($request->validated());

        Inertia::flash('success', 'Fund allocation added.');

        return to_route('admin.fund-allocations.index');
    }

    /**
     * Show the form for editing the given fund allocation.
     */
    public function edit(FundAllocation $fundAllocation): Response
    {
        return Inertia::render('admin/content/form', [
            'resource' => 'fund-allocations',
            'item' => $fundAllocation,
        ]);
    }

    /**
     * Update the given fund allocation.
     */
    public function update(FundAllocationRequest $request, FundAllocation $fundAllocation): RedirectResponse
    {
        $fundAllocation->update($request->validated());

        Inertia::flash('success', 'Fund allocation updated.');

        return to_route('admin.fund-allocations.index');
    }

    /**
     * Delete the given fund allocation.
     */
    public function destroy(FundAllocation $fundAllocation): RedirectResponse
    {
        $fundAllocation->delete();

        Inertia::flash('success', 'Fund allocation deleted.');

        return to_route('admin.fund-allocations.index');
    }
}
