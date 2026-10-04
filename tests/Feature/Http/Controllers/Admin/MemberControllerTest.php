<?php

use App\Enums\ClubPosition;
use App\Enums\MemberStatus;
use App\Enums\UserRole;
use App\Models\Club;
use App\Models\DuesPayment;
use App\Models\DuesRate;
use App\Models\Member;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('public');
    $this->club = Club::factory()->create(['name' => 'Sorsogon City Eagles Club']);
});

/**
 * Get the minimum valid payload for creating a member.
 *
 * @return array<string, mixed>
 */
function newMemberPayload(array $overrides = []): array
{
    return [
        'first_name' => 'Juan',
        'last_name' => 'Dela Cruz',
        'status' => 'active',
        ...$overrides,
    ];
}

describe('index', function () {
    it('shows a district admin members from every club', function () {
        Member::factory()->for($this->club)->create();
        Member::factory()->for(Club::factory())->create();

        $this->actingAs(User::factory()->districtAdmin()->create())
            ->get(route('admin.members.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/members/index')
                ->has('members.data', 2)
                ->has('clubs', 2));
    });

    it('shows an officer only the members of their own club', function () {
        $secretary = Member::factory()->for($this->club)->officer(ClubPosition::Secretary)->withLogin()->create();
        Member::factory()->for($this->club)->create(['last_name' => 'Clubmate']);
        Member::factory()->for(Club::factory())->create(['last_name' => 'Outsider']);

        $this->actingAs($secretary->user)
            ->get(route('admin.members.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('members.data', 2)
                ->where('clubName', 'Sorsogon City Eagles Club')
                ->where('clubs', []));
    });

    it('lists officers first in order of rank', function () {
        Member::factory()->for($this->club)->create(['last_name' => 'Aaron']);
        Member::factory()->for($this->club)->officer(ClubPosition::Treasurer)->create(['last_name' => 'Treasurer']);
        Member::factory()->for($this->club)->officer(ClubPosition::President)->create(['last_name' => 'President']);

        $this->actingAs(User::factory()->districtAdmin()->create())
            ->get(route('admin.members.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('members.data.0.position', 'president')
                ->where('members.data.1.position', 'treasurer')
                ->where('members.data.2.position', 'member'));
    });

    it('filters to members who have not paid this year', function () {
        $paid = Member::factory()->for($this->club)->create();
        DuesPayment::factory()->for($paid)->create(['year' => now()->year]);
        $unpaid = Member::factory()->for($this->club)->create();

        $this->actingAs(User::factory()->districtAdmin()->create())
            ->get(route('admin.members.index', ['dues' => 'unpaid']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('members.data', 1)
                ->where('members.data.0.id', $unpaid->id)
                ->where('members.data.0.dues_status', 'unpaid'));
    });

    it('filters by dues standing against the club rate', function (string $filter, string $expectedLastName) {
        DuesRate::factory()->for($this->club)->create(['year' => now()->year, 'amount' => '1000.00']);

        $full = Member::factory()->for($this->club)->create(['last_name' => 'Full']);
        DuesPayment::factory()->for($full)->create(['year' => now()->year, 'amount' => '1000.00']);

        $partial = Member::factory()->for($this->club)->create(['last_name' => 'Partial']);
        DuesPayment::factory()->for($partial)->create(['year' => now()->year, 'amount' => '400.00']);

        Member::factory()->for($this->club)->create(['last_name' => 'Nothing']);

        $this->actingAs(User::factory()->districtAdmin()->create())
            ->get(route('admin.members.index', ['dues' => $filter]))
            ->assertInertia(fn (Assert $page) => $page
                ->has('members.data', 1)
                ->where('members.data.0.full_name', fn (string $name) => str_ends_with($name, $expectedLastName))
                ->where('members.data.0.dues_status', $filter));
    })->with([
        'paid' => ['paid', 'Full'],
        'partial' => ['partial', 'Partial'],
        'unpaid' => ['unpaid', 'Nothing'],
    ]);

    it('shows the balance owed by a member who paid part of the rate', function () {
        DuesRate::factory()->for($this->club)->create(['year' => now()->year, 'amount' => '1200.00']);
        $member = Member::factory()->for($this->club)->create();
        DuesPayment::factory()->for($member)->create(['year' => now()->year, 'amount' => '500.00']);
        DuesPayment::factory()->for($member)->create(['year' => now()->year, 'amount' => '200.00']);

        $this->actingAs(User::factory()->districtAdmin()->create())
            ->get(route('admin.members.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('members.data.0.dues_status', 'partial')
                ->where('members.data.0.dues_balance', '500.00'));
    });

    it('applies each club\'s own rate', function () {
        $otherClub = Club::factory()->create();
        DuesRate::factory()->for($this->club)->create(['year' => now()->year, 'amount' => '500.00']);
        DuesRate::factory()->for($otherClub)->create(['year' => now()->year, 'amount' => '2000.00']);

        $here = Member::factory()->for($this->club)->create(['last_name' => 'Here']);
        DuesPayment::factory()->for($here)->create(['year' => now()->year, 'amount' => '500.00']);
        $there = Member::factory()->for($otherClub)->create(['last_name' => 'There']);
        DuesPayment::factory()->for($there)->create(['year' => now()->year, 'amount' => '500.00']);

        $this->actingAs(User::factory()->districtAdmin()->create())
            ->get(route('admin.members.index', ['dues' => 'paid']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('members.data', 1)
                ->where('members.data.0.id', $here->id));
    });

    it('searches by name or member number', function () {
        Member::factory()->for($this->club)->create(['last_name' => 'Rizal', 'member_number' => 'TFOE-1']);
        Member::factory()->for($this->club)->create(['last_name' => 'Bonifacio', 'member_number' => 'TFOE-2']);

        $this->actingAs(User::factory()->districtAdmin()->create())
            ->get(route('admin.members.index', ['search' => 'TFOE-2']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('members.data', 1)
                ->where('members.data.0.full_name', fn (string $name) => str_contains($name, 'Bonifacio')));
    });

    it('forbids regular members from the roster', function () {
        $member = Member::factory()->for($this->club)->withLogin()->create();

        $this->actingAs($member->user)
            ->get(route('admin.members.index'))
            ->assertForbidden();
    });
});

describe('store', function () {
    it('adds the member to the officer\'s own club as a regular member', function () {
        $secretary = Member::factory()->for($this->club)->officer(ClubPosition::Secretary)->withLogin()->create();

        $this->actingAs($secretary->user)
            ->post(route('admin.members.store'), newMemberPayload([
                'club_id' => Club::factory()->create()->id,
                'position' => 'president',
            ]))
            ->assertRedirect();

        $member = Member::query()->where('last_name', 'Dela Cruz')->sole();

        expect($member->club_id)->toBe($this->club->id)
            ->and($member->position)->toBe(ClubPosition::Member);
    });

    it('lets the club president appoint an officer', function () {
        $president = Member::factory()->for($this->club)->officer(ClubPosition::President)->withLogin()->create();

        $this->actingAs($president->user)
            ->post(route('admin.members.store'), newMemberPayload(['position' => 'treasurer']));

        expect(Member::query()->where('last_name', 'Dela Cruz')->sole()->position)->toBe(ClubPosition::Treasurer);
    });

    it('requires a district admin to choose the club', function () {
        $this->actingAs(User::factory()->districtAdmin()->create())
            ->post(route('admin.members.store'), newMemberPayload(['position' => 'member']))
            ->assertSessionHasErrors(['club_id' => 'The club id field is required.']);
    });

    it('rejects a second holder of the same office in a club', function () {
        Member::factory()->for($this->club)->officer(ClubPosition::Treasurer)->create([
            'first_name' => 'Maria',
            'middle_name' => null,
            'last_name' => 'Santos',
        ]);

        $this->actingAs(User::factory()->districtAdmin()->create())
            ->post(route('admin.members.store'), newMemberPayload([
                'club_id' => $this->club->id,
                'position' => 'treasurer',
            ]))
            ->assertSessionHasErrors(['position' => 'Maria Santos is already the Club Treasurer of this club. Change their position first.']);
    });

    it('allows the same office in different clubs', function () {
        Member::factory()->for(Club::factory())->officer(ClubPosition::Treasurer)->create();

        $this->actingAs(User::factory()->districtAdmin()->create())
            ->post(route('admin.members.store'), newMemberPayload([
                'club_id' => $this->club->id,
                'position' => 'treasurer',
            ]))
            ->assertSessionHasNoErrors();
    });

    it('creates a member login when a password is set', function () {
        $this->actingAs(User::factory()->districtAdmin()->create())
            ->post(route('admin.members.store'), newMemberPayload([
                'club_id' => $this->club->id,
                'position' => 'member',
                'email' => 'juan@example.com',
                'password' => 'secret-password',
                'password_confirmation' => 'secret-password',
            ]));

        $member = Member::query()->where('email', 'juan@example.com')->sole();

        expect($member->user)
            ->email->toBe('juan@example.com')
            ->name->toBe('Juan Dela Cruz')
            ->role->toBe(UserRole::Member)
            ->and(Auth::validate(['email' => 'juan@example.com', 'password' => 'secret-password']))->toBeTrue();
    });

    it('requires an email to create a login', function () {
        $this->actingAs(User::factory()->districtAdmin()->create())
            ->post(route('admin.members.store'), newMemberPayload([
                'club_id' => $this->club->id,
                'position' => 'member',
                'password' => 'secret-password',
                'password_confirmation' => 'secret-password',
            ]))
            ->assertSessionHasErrors(['email' => 'An email is required to give this member a login.']);
    });

    it('rejects an email that another login already uses', function () {
        $admin = User::factory()->districtAdmin()->create();

        $this->actingAs($admin)
            ->post(route('admin.members.store'), newMemberPayload([
                'club_id' => $this->club->id,
                'position' => 'member',
                'email' => $admin->email,
            ]))
            ->assertSessionHasErrors(['email' => 'The email has already been taken.']);
    });

    it('stores an uploaded photo', function () {
        $this->actingAs(User::factory()->districtAdmin()->create())
            ->post(route('admin.members.store'), newMemberPayload([
                'club_id' => $this->club->id,
                'position' => 'member',
                'photo' => UploadedFile::fake()->image('juan.jpg'),
            ]));

        Storage::disk('public')->assertExists(Member::query()->sole()->photo_path);
    });

    it('forbids regular members from adding members', function () {
        $member = Member::factory()->for($this->club)->withLogin()->create();

        $this->actingAs($member->user)
            ->post(route('admin.members.store'), newMemberPayload())
            ->assertForbidden();
    });
});

describe('show', function () {
    it('lets a member view their own profile and dues', function () {
        $member = Member::factory()->for($this->club)->withLogin()->create();
        DuesPayment::factory()->for($member)->create(['year' => 2025, 'amount' => '1000.00']);

        $this->actingAs($member->user)
            ->get(route('admin.members.show', $member))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/members/show')
                ->where('payments.0.year', 2025)
                ->where('payments.0.amount', '1000.00')
                ->where('can.manageDues', false)
                ->where('can.delete', false));
    });

    it('summarises each year against the club rate and suggests the remaining balance', function () {
        $member = Member::factory()->for($this->club)->withLogin()->create([
            'inducted_at' => (now()->year - 1).'-06-01',
        ]);
        DuesRate::factory()->for($this->club)->create(['year' => now()->year, 'amount' => '1200.00']);
        DuesRate::factory()->for($this->club)->create(['year' => now()->year - 1, 'amount' => '1000.00']);
        DuesRate::factory()->for($this->club)->create(['year' => now()->year - 5, 'amount' => '800.00']);
        DuesPayment::factory()->for($member)->create(['year' => now()->year, 'amount' => '200.00']);

        $this->actingAs($member->user)
            ->get(route('admin.members.show', $member))
            ->assertInertia(fn (Assert $page) => $page
                ->has('duesSummary', 2)
                ->where('duesSummary.0.year', now()->year)
                ->where('duesSummary.0.rate', '1200.00')
                ->where('duesSummary.0.paid', '200.00')
                ->where('duesSummary.0.dues_balance', '1000.00')
                ->where('duesSummary.0.dues_status', 'partial')
                ->where('duesSummary.1.year', now()->year - 1)
                ->where('duesSummary.1.dues_status', 'unpaid')
                ->where('currentYearBalance', '1000.00'));
    });

    it('forbids a member from viewing someone else', function () {
        $member = Member::factory()->for($this->club)->withLogin()->create();
        $clubmate = Member::factory()->for($this->club)->create();

        $this->actingAs($member->user)
            ->get(route('admin.members.show', $clubmate))
            ->assertForbidden();
    });

    it('forbids an officer from viewing another club\'s member', function () {
        $president = Member::factory()->for($this->club)->officer(ClubPosition::President)->withLogin()->create();
        $outsider = Member::factory()->for(Club::factory())->create();

        $this->actingAs($president->user)
            ->get(route('admin.members.show', $outsider))
            ->assertForbidden();
    });
});

describe('update', function () {
    it('lets a member update their own contact details', function () {
        $member = Member::factory()->for($this->club)->withLogin()->create();

        $this->actingAs($member->user)
            ->put(route('admin.members.update', $member), [
                'first_name' => $member->first_name,
                'last_name' => $member->last_name,
                'email' => $member->email,
                'phone' => '09171234567',
            ])
            ->assertRedirect(route('admin.members.show', $member));

        expect($member->fresh()->phone)->toBe('09171234567');
    });

    it('ignores membership fields a member submits for themselves', function () {
        $member = Member::factory()->for($this->club)->withLogin()->create(['member_number' => 'TFOE-1']);

        $this->actingAs($member->user)
            ->put(route('admin.members.update', $member), [
                'first_name' => $member->first_name,
                'last_name' => $member->last_name,
                'email' => $member->email,
                'position' => 'president',
                'status' => 'active',
                'member_number' => 'TFOE-999',
                'club_id' => Club::factory()->create()->id,
            ]);

        expect($member->fresh())
            ->position->toBe(ClubPosition::Member)
            ->member_number->toBe('TFOE-1')
            ->club_id->toBe($this->club->id);
    });

    it('keeps the login email in step with the member email', function () {
        $member = Member::factory()->for($this->club)->withLogin()->create();

        $this->actingAs(User::factory()->districtAdmin()->create())
            ->put(route('admin.members.update', $member), [
                'club_id' => $this->club->id,
                'position' => 'member',
                'status' => 'active',
                'first_name' => 'Andres',
                'middle_name' => '',
                'last_name' => 'Bonifacio',
                'email' => 'andres@example.com',
            ]);

        expect($member->user->fresh())
            ->email->toBe('andres@example.com')
            ->name->toBe('Andres Bonifacio');
    });

    it('requires an email while the member has a login', function () {
        $member = Member::factory()->for($this->club)->withLogin()->create();

        $this->actingAs(User::factory()->districtAdmin()->create())
            ->put(route('admin.members.update', $member), newMemberPayload([
                'club_id' => $this->club->id,
                'position' => 'member',
                'email' => '',
            ]))
            ->assertSessionHasErrors(['email' => 'This member has a login, so an email is required.']);
    });

    it('lets an officer suspend a member of their club', function () {
        $president = Member::factory()->for($this->club)->officer(ClubPosition::President)->withLogin()->create();
        $member = Member::factory()->for($this->club)->create();

        $this->actingAs($president->user)
            ->put(route('admin.members.update', $member), [
                'first_name' => $member->first_name,
                'last_name' => $member->last_name,
                'position' => 'member',
                'status' => 'suspended',
            ]);

        expect($member->fresh()->status)->toBe(MemberStatus::Suspended);
    });

    it('forbids an officer from editing another club\'s member', function () {
        $president = Member::factory()->for($this->club)->officer(ClubPosition::President)->withLogin()->create();
        $outsider = Member::factory()->for(Club::factory())->create();

        $this->actingAs($president->user)
            ->put(route('admin.members.update', $outsider), newMemberPayload())
            ->assertForbidden();
    });
});

describe('destroy', function () {
    it('deletes the member, their photo and their login', function () {
        Storage::disk('public')->put('members/photo.jpg', 'photo');
        $member = Member::factory()->for($this->club)->withLogin()->create(['photo_path' => 'members/photo.jpg']);
        $user = $member->user;

        $this->actingAs(User::factory()->districtAdmin()->create())
            ->delete(route('admin.members.destroy', $member))
            ->assertRedirect(route('admin.members.index'));

        $this->assertModelMissing($member);
        $this->assertModelMissing($user);
        Storage::disk('public')->assertMissing('members/photo.jpg');
    });

    it('forbids officers from deleting members', function () {
        $president = Member::factory()->for($this->club)->officer(ClubPosition::President)->withLogin()->create();
        $member = Member::factory()->for($this->club)->create();

        $this->actingAs($president->user)
            ->delete(route('admin.members.destroy', $member))
            ->assertForbidden();

        $this->assertModelExists($member);
    });
});
