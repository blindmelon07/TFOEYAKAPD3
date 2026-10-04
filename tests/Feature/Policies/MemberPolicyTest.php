<?php

use App\Enums\ClubPosition;
use App\Models\Club;
use App\Models\Member;
use App\Models\User;
use App\Policies\MemberPolicy;

/**
 * Build a signed-in user who holds the given position in the given club.
 */
function memberLogin(Club $club, ClubPosition $position): User
{
    return Member::factory()->for($club)->officer($position)->withLogin()->create()->user;
}

beforeEach(function () {
    $this->policy = new MemberPolicy;
    $this->club = Club::factory()->create();
    $this->otherClub = Club::factory()->create();
    $this->target = Member::factory()->for($this->club)->create();
    $this->outsider = Member::factory()->for($this->otherClub)->create();
});

it('grants a district admin every ability', function () {
    $admin = User::factory()->districtAdmin()->create();

    expect($this->policy->viewAny($admin))->toBeTrue()
        ->and($this->policy->view($admin, $this->outsider))->toBeTrue()
        ->and($this->policy->update($admin, $this->outsider))->toBeTrue()
        ->and($this->policy->updateMembership($admin, $this->outsider))->toBeTrue()
        ->and($this->policy->assignPosition($admin, $this->outsider))->toBeTrue()
        ->and($this->policy->changeClub($admin))->toBeTrue()
        ->and($this->policy->manageDues($admin, $this->outsider))->toBeTrue()
        ->and($this->policy->delete($admin))->toBeTrue();
});

it('lets every officer manage members of their own club only', function (ClubPosition $position) {
    $officer = memberLogin($this->club, $position);

    expect($this->policy->viewAny($officer))->toBeTrue()
        ->and($this->policy->create($officer))->toBeTrue()
        ->and($this->policy->view($officer, $this->target))->toBeTrue()
        ->and($this->policy->update($officer, $this->target))->toBeTrue()
        ->and($this->policy->updateMembership($officer, $this->target))->toBeTrue()
        ->and($this->policy->view($officer, $this->outsider))->toBeFalse()
        ->and($this->policy->update($officer, $this->outsider))->toBeFalse()
        ->and($this->policy->changeClub($officer))->toBeFalse()
        ->and($this->policy->delete($officer))->toBeFalse();
})->with(ClubPosition::offices());

it('lets only the president assign positions within the club', function (ClubPosition $position, bool $canAssign) {
    $officer = memberLogin($this->club, $position);

    expect($this->policy->assignPosition($officer, $this->target))->toBe($canAssign)
        ->and($this->policy->assignPosition($officer, $this->outsider))->toBeFalse();
})->with([
    'president' => [ClubPosition::President, true],
    'vice president' => [ClubPosition::VicePresident, false],
    'secretary' => [ClubPosition::Secretary, false],
    'treasurer' => [ClubPosition::Treasurer, false],
    'auditor' => [ClubPosition::Auditor, false],
]);

it('lets only the president and treasurer handle dues within the club', function (ClubPosition $position, bool $canManageDues) {
    $officer = memberLogin($this->club, $position);

    expect($this->policy->manageDues($officer, $this->target))->toBe($canManageDues)
        ->and($this->policy->manageDues($officer, $this->outsider))->toBeFalse();
})->with([
    'president' => [ClubPosition::President, true],
    'vice president' => [ClubPosition::VicePresident, false],
    'secretary' => [ClubPosition::Secretary, false],
    'treasurer' => [ClubPosition::Treasurer, true],
    'auditor' => [ClubPosition::Auditor, false],
]);

it('limits a regular member to their own profile and contact details', function () {
    $self = Member::factory()->for($this->club)->withLogin()->create();
    $user = $self->user;

    expect($this->policy->viewAny($user))->toBeFalse()
        ->and($this->policy->create($user))->toBeFalse()
        ->and($this->policy->view($user, $self))->toBeTrue()
        ->and($this->policy->update($user, $self))->toBeTrue()
        ->and($this->policy->updateMembership($user, $self))->toBeFalse()
        ->and($this->policy->assignPosition($user, $self))->toBeFalse()
        ->and($this->policy->manageDues($user, $self))->toBeFalse()
        ->and($this->policy->view($user, $this->target))->toBeFalse()
        ->and($this->policy->update($user, $this->target))->toBeFalse();
});
