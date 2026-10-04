<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAccountRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    /**
     * Show the administrator's account form.
     */
    public function edit(): Response
    {
        return Inertia::render('admin/account');
    }

    /**
     * Update the administrator's name, email, and optionally password.
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
