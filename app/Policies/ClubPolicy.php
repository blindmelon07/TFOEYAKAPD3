<?php

namespace App\Policies;

use App\Enums\ClubPosition;
use App\Models\Club;
use App\Models\User;

class ClubPolicy
{
    /**
     * Determine whether the user can manage clubs. Only district admins can.
     */
    public function viewAny(User $user): bool
    {
        return $user->isDistrictAdmin();
    }

    /**
     * Determine whether the user can create clubs.
     */
    public function create(User $user): bool
    {
        return $user->isDistrictAdmin();
    }

    /**
     * Determine whether the user can update clubs.
     */
    public function update(User $user): bool
    {
        return $user->isDistrictAdmin();
    }

    /**
     * Determine whether the user can delete clubs.
     */
    public function delete(User $user): bool
    {
        return $user->isDistrictAdmin();
    }

    /**
     * Determine whether the user can create, edit and download the club's documents.
     */
    public function manageDocuments(User $user, Club $club): bool
    {
        return $user->isDistrictAdmin() || $user->isOfficerOf($club->id);
    }

    /**
     * Determine whether the user can replace the club's letterhead template:
     * district admins and the club president.
     */
    public function manageLetterhead(User $user, Club $club): bool
    {
        return $user->isDistrictAdmin()
            || ($user->isOfficerOf($club->id) && $user->member?->position === ClubPosition::President);
    }

    /**
     * Determine whether the user can see the club's yearly dues amounts.
     */
    public function viewDuesRates(User $user, Club $club): bool
    {
        return $user->isDistrictAdmin() || $user->isOfficerOf($club->id);
    }

    /**
     * Determine whether the user can set the club's yearly dues amounts:
     * district admins, and the club's president and treasurer.
     */
    public function manageDuesRates(User $user, Club $club): bool
    {
        return $user->isDistrictAdmin()
            || ($user->isOfficerOf($club->id) && ($user->member?->position->handlesDues() ?? false));
    }
}
