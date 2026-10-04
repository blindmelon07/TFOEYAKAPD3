<?php

use App\Enums\ClubPosition;
use App\Models\Club;
use App\Models\Member;
use App\Models\User;
use App\Policies\ClubPolicy;

beforeEach(function () {
    $this->policy = new ClubPolicy;
    $this->club = Club::factory()->create();
});

it('lets a district admin view and set any club\'s dues rates', function () {
    $admin = User::factory()->districtAdmin()->create();

    expect($this->policy->viewDuesRates($admin, $this->club))->toBeTrue()
        ->and($this->policy->manageDuesRates($admin, $this->club))->toBeTrue();
});

it('lets every officer view their club\'s rates but only the president and treasurer set them', function (ClubPosition $position, bool $canManage) {
    $officer = Member::factory()->for($this->club)->officer($position)->withLogin()->create()->user;
    $otherClub = Club::factory()->create();

    expect($this->policy->viewDuesRates($officer, $this->club))->toBeTrue()
        ->and($this->policy->manageDuesRates($officer, $this->club))->toBe($canManage)
        ->and($this->policy->viewDuesRates($officer, $otherClub))->toBeFalse()
        ->and($this->policy->manageDuesRates($officer, $otherClub))->toBeFalse();
})->with([
    'president' => [ClubPosition::President, true],
    'vice president' => [ClubPosition::VicePresident, false],
    'secretary' => [ClubPosition::Secretary, false],
    'treasurer' => [ClubPosition::Treasurer, true],
    'auditor' => [ClubPosition::Auditor, false],
]);

it('hides the rates from regular members', function () {
    $member = Member::factory()->for($this->club)->withLogin()->create()->user;

    expect($this->policy->viewDuesRates($member, $this->club))->toBeFalse()
        ->and($this->policy->manageDuesRates($member, $this->club))->toBeFalse();
});

it('lets every officer manage documents but only the president change the letterhead', function (ClubPosition $position, bool $canChangeLetterhead) {
    $officer = Member::factory()->for($this->club)->officer($position)->withLogin()->create()->user;
    $otherClub = Club::factory()->create();

    expect($this->policy->manageDocuments($officer, $this->club))->toBeTrue()
        ->and($this->policy->manageLetterhead($officer, $this->club))->toBe($canChangeLetterhead)
        ->and($this->policy->manageDocuments($officer, $otherClub))->toBeFalse()
        ->and($this->policy->manageLetterhead($officer, $otherClub))->toBeFalse();
})->with([
    'president' => [ClubPosition::President, true],
    'vice president' => [ClubPosition::VicePresident, false],
    'secretary' => [ClubPosition::Secretary, false],
    'treasurer' => [ClubPosition::Treasurer, false],
    'auditor' => [ClubPosition::Auditor, false],
]);

it('keeps documents away from regular members', function () {
    $member = Member::factory()->for($this->club)->withLogin()->create()->user;

    expect($this->policy->manageDocuments($member, $this->club))->toBeFalse()
        ->and($this->policy->manageLetterhead($member, $this->club))->toBeFalse();
});
