<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MembershipStepRequest;
use App\Models\MembershipStep;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class MembershipStepController extends Controller
{
    /**
     * List every membership step.
     */
    public function index(): Response
    {
        return Inertia::render('admin/content/index', [
            'resource' => 'membership-steps',
            'items' => MembershipStep::query()->ordered()->get(),
        ]);
    }

    /**
     * Show the form for creating a membership step.
     */
    public function create(): Response
    {
        return Inertia::render('admin/content/form', [
            'resource' => 'membership-steps',
            'item' => null,
            'nextSortOrder' => ((int) MembershipStep::query()->max('sort_order')) + 10,
        ]);
    }

    /**
     * Store a newly created membership step.
     */
    public function store(MembershipStepRequest $request): RedirectResponse
    {
        MembershipStep::query()->create($request->validated());

        Inertia::flash('success', 'Membership step added.');

        return to_route('admin.membership-steps.index');
    }

    /**
     * Show the form for editing the given membership step.
     */
    public function edit(MembershipStep $membershipStep): Response
    {
        return Inertia::render('admin/content/form', [
            'resource' => 'membership-steps',
            'item' => $membershipStep,
        ]);
    }

    /**
     * Update the given membership step.
     */
    public function update(MembershipStepRequest $request, MembershipStep $membershipStep): RedirectResponse
    {
        $membershipStep->update($request->validated());

        Inertia::flash('success', 'Membership step updated.');

        return to_route('admin.membership-steps.index');
    }

    /**
     * Delete the given membership step.
     */
    public function destroy(MembershipStep $membershipStep): RedirectResponse
    {
        $membershipStep->delete();

        Inertia::flash('success', 'Membership step deleted.');

        return to_route('admin.membership-steps.index');
    }
}
