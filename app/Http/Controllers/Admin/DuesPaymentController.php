<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DuesPaymentRequest;
use App\Models\DuesPayment;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class DuesPaymentController extends Controller
{
    /**
     * Record a dues payment for the member.
     */
    public function store(DuesPaymentRequest $request, Member $member): RedirectResponse
    {
        $payment = new DuesPayment($request->validated());
        $payment->recorded_by = $request->user()?->id;

        $member->duesPayments()->save($payment);

        Inertia::flash('success', "{$payment->year} dues payment recorded.");

        return to_route('admin.members.show', $member);
    }

    /**
     * Delete a dues payment recorded in error.
     */
    public function destroy(DuesPayment $duesPayment): RedirectResponse
    {
        Gate::authorize('delete', $duesPayment);

        $duesPayment->delete();

        Inertia::flash('success', 'Payment removed.');

        return to_route('admin.members.show', $duesPayment->member_id);
    }
}
