<?php

namespace App\Models;

use App\Enums\ClubPosition;
use App\Enums\DuesStatus;
use App\Enums\MemberStatus;
use App\Models\Concerns\ResolvesAssetUrls;
use Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $club_id
 * @property int|null $user_id
 * @property ClubPosition $position
 * @property MemberStatus $status
 * @property string|null $member_number
 * @property string $first_name
 * @property string|null $middle_name
 * @property string $last_name
 * @property string|null $photo_path
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $address
 * @property Carbon|null $birthday
 * @property Carbon|null $inducted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $full_name
 * @property-read string|null $photo_url
 * @property-read Club $club
 * @property-read User|null $user
 */
#[Fillable([
    'club_id', 'position', 'status', 'member_number', 'first_name', 'middle_name', 'last_name',
    'photo_path', 'email', 'phone', 'address', 'birthday', 'inducted_at',
])]
#[Appends(['full_name', 'photo_url'])]
class Member extends Model
{
    /** @use HasFactory<MemberFactory> */
    use HasFactory, ResolvesAssetUrls;

    /**
     * Total a member paid towards one year's dues. Binds the year.
     */
    private const string PAID_SQL = '(select coalesce(sum(dues_payments.amount), 0) from dues_payments where dues_payments.member_id = members.id and dues_payments.year = ?)';

    /**
     * The member's club's dues amount for one year, or null. Binds the year.
     */
    private const string RATE_SQL = '(select dues_rates.amount from dues_rates where dues_rates.club_id = members.club_id and dues_rates.year = ?)';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'position' => ClubPosition::class,
            'status' => MemberStatus::class,
            'birthday' => 'date:Y-m-d',
            'inducted_at' => 'date:Y-m-d',
        ];
    }

    /**
     * Get the club the member belongs to.
     *
     * @return BelongsTo<Club, $this>
     */
    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * Get the login account linked to the member, if any.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the member's recorded dues payments.
     *
     * @return HasMany<DuesPayment, $this>
     */
    public function duesPayments(): HasMany
    {
        return $this->hasMany(DuesPayment::class);
    }

    /**
     * Get the member's name as "First Middle Last".
     *
     * @return Attribute<string, never>
     */
    protected function fullName(): Attribute
    {
        return Attribute::get(fn (): string => collect([$this->first_name, $this->middle_name, $this->last_name])
            ->filter()
            ->implode(' '));
    }

    /**
     * Get the browser-usable URL of the member's photo.
     *
     * @return Attribute<string|null, never>
     */
    protected function photoUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => static::assetUrl($this->photo_path));
    }

    /**
     * Limit members to those the given user is allowed to see in listings.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function visibleTo(Builder $query, User $user): void
    {
        if ($user->isDistrictAdmin()) {
            return;
        }

        if ($user->isClubOfficer()) {
            $query->where('club_id', $user->member?->club_id);

            return;
        }

        $query->where('user_id', $user->id);
    }

    /**
     * Add the member's total paid (`dues_paid`) and their club's rate
     * (`dues_rate`) for the given year to each row.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function withDuesFor(Builder $query, int $year): void
    {
        $query->withSum(['duesPayments as dues_paid' => fn ($query) => $query->where('year', $year)], 'amount')
            ->addSelect(['dues_rate' => DuesRate::query()
                ->select('amount')
                ->whereColumn('dues_rates.club_id', 'members.club_id')
                ->where('year', $year)
                ->limit(1)]);
    }

    /**
     * Limit members to those with the given dues standing for the year.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function whereDuesStatus(Builder $query, int $year, DuesStatus $status): void
    {
        match ($status) {
            DuesStatus::Unpaid => $query->whereRaw(self::PAID_SQL.' = 0', [$year]),
            DuesStatus::Paid => $query->whereRaw(
                self::PAID_SQL.' > 0 and ('.self::RATE_SQL.' is null or '.self::PAID_SQL.' >= '.self::RATE_SQL.')',
                [$year, $year, $year, $year],
            ),
            DuesStatus::Partial => $query->whereRaw(
                self::PAID_SQL.' > 0 and '.self::RATE_SQL.' is not null and '.self::PAID_SQL.' < '.self::RATE_SQL,
                [$year, $year, $year, $year],
            ),
        };
    }

    /**
     * Order officers first (by rank), then members alphabetically.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function rosterOrder(Builder $query): void
    {
        $query->orderByRaw(
            'case position when ? then 0 when ? then 1 when ? then 2 when ? then 3 when ? then 4 else 5 end',
            [
                ClubPosition::President->value,
                ClubPosition::VicePresident->value,
                ClubPosition::Secretary->value,
                ClubPosition::Treasurer->value,
                ClubPosition::Auditor->value,
            ],
        )
            ->orderBy('last_name')
            ->orderBy('first_name');
    }
}
