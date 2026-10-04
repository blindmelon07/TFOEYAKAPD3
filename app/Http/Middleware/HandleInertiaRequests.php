<?php

namespace App\Http\Middleware;

use App\Models\Club;
use App\Models\Member;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => fn (): array => $this->authProps($request->user()),
            'siteLogo' => fn (): ?string => Setting::publicValues()['logo'],
        ];
    }

    /**
     * Get the signed-in user's identity and what the admin navigation should offer them.
     *
     * @return array{user: array<string, mixed>|null, memberId: int|null, officerClubId: int|null, can: array<string, bool>}
     */
    private function authProps(?User $user): array
    {
        return [
            'user' => $user ? [
                ...$user->only('id', 'name', 'email'),
                'role' => $user->role->value,
                'position' => $user->member?->position->label(),
            ] : null,
            'memberId' => $user?->member?->id,
            'officerClubId' => $user?->isClubOfficer() ? $user->member?->club_id : null,
            'can' => [
                'manageSite' => $user?->can('manage-site') ?? false,
                'manageClubs' => $user?->can('viewAny', Club::class) ?? false,
                'viewMembers' => $user?->can('viewAny', Member::class) ?? false,
                'useForms' => $user !== null && ($user->isDistrictAdmin() || $user->isClubOfficer()),
            ],
        ];
    }
}
