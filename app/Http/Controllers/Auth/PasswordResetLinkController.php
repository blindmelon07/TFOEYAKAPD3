<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    /**
     * Show the "forgot password" form.
     */
    public function create(): Response
    {
        return Inertia::render('auth/forgot-password');
    }

    /**
     * Email a password reset link.
     *
     * The response is identical whether or not the email belongs to an
     * account, so the form cannot be used to discover who is a member.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_THROTTLED) {
            throw ValidationException::withMessages([
                'email' => __($status),
            ]);
        }

        Inertia::flash('success', 'If that email belongs to an account, a reset link is on its way. Check your inbox.');

        return back();
    }
}
