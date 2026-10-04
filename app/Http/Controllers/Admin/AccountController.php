<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAccountRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    /**
     * Show the signed-in user's account form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('admin/account', [
            'identityManagedByProfile' => $request->user()?->member !== null,
        ]);
    }

    /**
     * Update the user's password, and for non-members their name and email.
     */
    public function update(UpdateAccountRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->fill($request->safe()->only(['name', 'email']));

        if ($request->filled('password')) {
            $user->password = $request->validated('password');
        }

        $user->save();

        Inertia::flash('success', 'Account updated.');

        return to_route('admin.account.edit');
    }
}
