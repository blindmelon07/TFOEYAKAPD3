<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Send the user to the most useful page for their role.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        return match (true) {
            $user->isDistrictAdmin(), $user->isClubOfficer() => to_route('admin.members.index'),
            $user->member !== null => to_route('admin.members.show', $user->member),
            default => to_route('admin.account.edit'),
        };
    }
}
