<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ClubPosition;
use App\Enums\DuesStatus;
use App\Enums\MemberStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MemberRequest;
use App\Models\Club;
use App\Models\DuesPayment;
use App\Models\Member;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class MemberController extends Controller
{
    /**
     * List the members the user is allowed to manage.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Member::class);

        /** @var User $user */
        $user = $request->user();
        $currentYear = now()->year;

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'club' => ['nullable', 'integer'],
            'status' => ['nullable', Rule::enum(MemberStatus::class)],
            'dues' => ['nullable', Rule::enum(DuesStatus::class)],
        ]);

        $members = Member::query()
            ->visibleTo($user)
            ->with('club:id,name')
            ->withDuesFor($currentYear)
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where(fn ($query) => $query
                ->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('member_number', 'like', "%{$search}%")))
            ->when($user->isDistrictAdmin() ? ($filters['club'] ?? null) : null, fn ($query, int $clubId) => $query->where('club_id', $clubId))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['dues'] ?? null, fn ($query, string $dues) => $query->whereDuesStatus($currentYear, DuesStatus::from($dues)))
            ->rosterOrder()
            ->paginate(25)
            ->withQueryString()
            ->through(fn (Member $member): array => [
                'id' => $member->id,
                'full_name' => $member->full_name,
                'photo_url' => $member->photo_url,
                'member_number' => $member->member_number,
                'club' => $member->club->name,
                'position' => $member->position->value,
                'position_label' => $member->position->label(),
                'status' => $member->status->value,
                ...$this->duesStanding($member->getAttribute('dues_rate'), $member->getAttribute('dues_paid')),
                'has_login' => $member->user_id !== null,
            ]);

        return Inertia::render('admin/members/index', [
            'members' => $members,
            'filters' => $filters,
            'clubs' => $user->isDistrictAdmin() ? Club::query()->orderBy('name')->get(['id', 'name']) : [],
            'clubName' => $user->isDistrictAdmin() ? null : $user->member?->club->name,
            'statuses' => MemberStatus::options(),
            'currentYear' => $currentYear,
            'canCreate' => $user->can('create', Member::class),
        ]);
    }

    /**
     * Show the form for adding a member.
     */
    public function create(Request $request): Response
    {
        Gate::authorize('create', Member::class);

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('admin/members/form', [
            ...$this->formOptions($user, new Member(['club_id' => $user->member?->club_id])),
            'member' => null,
        ]);
    }

    /**
     * Store a newly added member.
     */
    public function store(MemberRequest $request): RedirectResponse
    {
        $member = DB::transaction(function () use ($request): Member {
            $member = new Member($request->memberAttributes());

            if ($request->hasFile('photo')) {
                $member->photo_path = Member::storeUploadedAsset($request->file('photo'), 'members', 'photo');
            }

            $member->save();
            $this->syncLogin($member, $request->validated('password'));

            return $member;
        });

        Inertia::flash('success', "{$member->full_name} added.");

        return to_route('admin.members.show', $member);
    }

    /**
     * Show the member's profile and dues history.
     */
    public function show(Request $request, Member $member): Response
    {
        Gate::authorize('view', $member);

        /** @var User $user */
        $user = $request->user();

        $member->load(['club:id,name', 'user:id,email']);

        $paymentRecords = $member->duesPayments()
            ->with('recorder:id,name')
            ->orderByDesc('year')
            ->orderByDesc('paid_at')
            ->get();

        $payments = $paymentRecords->map(fn (DuesPayment $payment): array => [
            'id' => $payment->id,
            'year' => $payment->year,
            'amount' => $payment->amount,
            'paid_at' => $payment->paid_at->format('Y-m-d'),
            'reference' => $payment->reference,
            'notes' => $payment->notes,
            'recorded_by' => $payment->recorder?->name,
        ]);

        $duesSummary = $this->duesSummary($member, $paymentRecords);

        return Inertia::render('admin/members/show', [
            'member' => [
                ...$member->only([
                    'id', 'full_name', 'photo_url', 'member_number', 'email', 'phone', 'address',
                ]),
                'birthday' => $member->birthday?->format('Y-m-d'),
                'inducted_at' => $member->inducted_at?->format('Y-m-d'),
                'club' => $member->club->name,
                'position' => $member->position->value,
                'position_label' => $member->position->label(),
                'status' => $member->status->value,
                'status_label' => $member->status->label(),
                'login_email' => $member->user?->email,
            ],
            'payments' => $payments,
            'duesSummary' => $duesSummary,
            'currentYearBalance' => $duesSummary->firstWhere('year', now()->year)['dues_balance'] ?? null,
            'currentYear' => now()->year,
            'can' => [
                'update' => $user->can('update', $member),
                'manageDues' => $user->can('manageDues', $member),
                'delete' => $user->can('delete', Member::class),
            ],
        ]);
    }

    /**
     * Show the form for editing the member.
     */
    public function edit(Request $request, Member $member): Response
    {
        Gate::authorize('update', $member);

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('admin/members/form', [
            ...$this->formOptions($user, $member),
            'member' => [
                ...$member->only([
                    'id', 'club_id', 'member_number', 'first_name', 'middle_name', 'last_name',
                    'email', 'phone', 'address', 'photo_url',
                ]),
                'birthday' => $member->birthday?->format('Y-m-d'),
                'inducted_at' => $member->inducted_at?->format('Y-m-d'),
                'position' => $member->position->value,
                'status' => $member->status->value,
                'has_login' => $member->user_id !== null,
            ],
        ]);
    }

    /**
     * Update the member.
     */
    public function update(MemberRequest $request, Member $member): RedirectResponse
    {
        DB::transaction(function () use ($request, $member): void {
            $member->fill($request->memberAttributes());

            if ($request->hasFile('photo')) {
                Member::deleteUploadedAsset($member->photo_path);
                $member->photo_path = Member::storeUploadedAsset($request->file('photo'), 'members', 'photo');
            } elseif ($request->boolean('remove_photo')) {
                Member::deleteUploadedAsset($member->photo_path);
                $member->photo_path = null;
            }

            $member->save();
            $this->syncLogin($member, $request->validated('password'));
        });

        Inertia::flash('success', 'Member details saved.');

        return to_route('admin.members.show', $member);
    }

    /**
     * Delete the member, their photo and their login.
     */
    public function destroy(Member $member): RedirectResponse
    {
        Gate::authorize('delete', Member::class);

        DB::transaction(function () use ($member): void {
            $user = $member->user;

            Member::deleteUploadedAsset($member->photo_path);
            $member->delete();

            if ($user?->role === UserRole::Member) {
                $user->delete();
            }
        });

        Inertia::flash('success', "{$member->full_name} removed.");

        return to_route('admin.members.index');
    }

    /**
     * Summarise each year the member owed or paid dues: the club's rate, the
     * total paid, the balance and the resulting standing. Years with a rate
     * before the member was inducted, or in the future, are left out.
     *
     * @param  Collection<int, DuesPayment>  $payments
     * @return Collection<int, array{year: int, rate: string|null, paid: string, dues_status: string, dues_balance: string}>
     */
    private function duesSummary(Member $member, Collection $payments): Collection
    {
        /** @var Collection<int, string> $rates */
        $rates = $member->club->duesRates()->pluck('amount', 'year');

        $paidByYear = $payments
            ->groupBy('year')
            ->map(fn (Collection $yearPayments): string => number_format(
                $yearPayments->sum(fn (DuesPayment $payment): int => (int) round((float) $payment->amount * 100)) / 100,
                2,
                '.',
                '',
            ));

        $firstYear = $member->inducted_at?->year;

        return $rates->keys()
            ->filter(fn (int $year): bool => $year <= now()->year && ($firstYear === null || $year >= $firstYear))
            ->merge($paidByYear->keys())
            ->unique()
            ->sortDesc()
            ->values()
            ->map(fn (int $year): array => [
                'year' => $year,
                'rate' => $rates->get($year),
                'paid' => $paidByYear->get($year, '0.00'),
                ...$this->duesStanding($rates->get($year), $paidByYear->get($year)),
            ]);
    }

    /**
     * Get the dues status and remaining balance for a rate and amount paid.
     *
     * @return array{dues_status: string, dues_balance: string}
     */
    private function duesStanding(?string $rate, ?string $paid): array
    {
        return [
            'dues_status' => DuesStatus::for($rate, $paid ?? '0')->value,
            'dues_balance' => DuesStatus::balance($rate, $paid ?? '0'),
        ];
    }

    /**
     * Keep the member's login account in step with their record, creating
     * one when a password is first set.
     */
    private function syncLogin(Member $member, ?string $password): void
    {
        $user = $member->user;

        if (! $user && blank($password)) {
            return;
        }

        $user ??= (new User)->forceFill(['role' => UserRole::Member]);

        $user->fill(['name' => $member->full_name, 'email' => $member->email]);

        if (filled($password)) {
            $user->password = $password;
        }

        $user->save();

        if ($member->user_id !== $user->id) {
            $member->user()->associate($user)->save();
        }
    }

    /**
     * Get the select options and field permissions for the member form.
     *
     * @return array<string, mixed>
     */
    private function formOptions(User $user, Member $member): array
    {
        $canUpdateMembership = $member->exists
            ? $user->can('updateMembership', $member)
            : $user->can('create', Member::class);

        return [
            'clubs' => $user->can('changeClub', Member::class)
                ? Club::query()->orderBy('name')->get(['id', 'name'])
                : [],
            'clubName' => $member->club_id ? Club::query()->whereKey($member->club_id)->value('name') : null,
            'positions' => ClubPosition::options(),
            'statuses' => MemberStatus::options(),
            'can' => [
                'changeClub' => $user->can('changeClub', Member::class),
                'updateMembership' => $canUpdateMembership,
                'assignPosition' => $user->can('assignPosition', $member),
            ],
        ];
    }
}
