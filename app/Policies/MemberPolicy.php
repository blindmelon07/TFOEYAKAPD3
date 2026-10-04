<?php

namespace App\Policies;

use App\Enums\ClubPosition;
use App\Models\Member;
use App\Models\User;

class MemberPolicy
{
    /**
     * Determine whether the user can browse the member roster.
     */
    public function viewAny(User $user): bool
    {
        return $user->isDistrictAdmin() || $user->isClubOfficer();
    }

    /**
     * Determine whether the user can view the member's profile and dues.
     */
    public function view(User $user, Member $member): bool
    {
        return $this->managesClubOf($user, $member) || $this->isSelf($user, $member);
    }

    /**
     * Determine whether the user can add members.
     */
    public function create(User $user): bool
    {
        return $user->isDistrictAdmin() || $user->isClubOfficer();
    }

    /**
     * Determine whether the user can edit the member's personal and contact details.
     */
    public function update(User $user, Member $member): bool
    {
        return $this->managesClubOf($user, $member) || $this->isSelf($user, $member);
    }

    /**
     * Determine whether the user can edit membership details: number, status,
     * induction date and login password.
     */
    public function updateMembership(User $user, Member $member): bool
    {
        return $this->managesClubOf($user, $member);
    }

    /**
     * Determine whether the user can appoint the member to a club office.
     */
    public function assignPosition(User $user, Member $member): bool
    {
        return $user->isDistrictAdmin()
            || ($user->member?->position === ClubPosition::President && $user->member->club_id === $member->club_id);
    }

    /**
     * Determine whether the user can move the member to another club.
     */
    public function changeClub(User $user): bool
    {
        return $user->isDistrictAdmin();
    }

    /**
     * Determine whether the user can record or remove the member's dues payments.
     */
    public function manageDues(User $user, Member $member): bool
    {
        return $user->isDistrictAdmin()
            || ($user->isOfficerOf($member->club_id) && $user->member?->position->handlesDues());
    }

    /**
     * Determine whether the user can delete the member.
     */
    public function delete(User $user): bool
    {
        return $user->isDistrictAdmin();
    }

    private function managesClubOf(User $user, Member $member): bool
    {
        return $user->isDistrictAdmin() || $user->isOfficerOf($member->club_id);
    }

    private function isSelf(User $user, Member $member): bool
    {
        return $member->user_id !== null && $member->user_id === $user->id;
    }
}
