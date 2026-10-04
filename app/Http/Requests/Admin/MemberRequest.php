<?php

namespace App\Http\Requests\Admin;

use App\Enums\ClubPosition;
use App\Enums\MemberStatus;
use App\Models\Club;
use App\Models\Member;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Validates member create/update requests. Which fields are accepted depends
 * on what the signed-in user may change, so members editing their own profile
 * can never touch their club, office, status or member number.
 */
class MemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $member = $this->member();

        return $member
            ? $this->actor()->can('update', $member)
            : $this->actor()->can('create', Member::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $member = $this->member();

        $rules = [
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => [
                $member?->user_id ? 'required' : 'nullable',
                'required_with:password',
                'email',
                'max:255',
                Rule::unique(Member::class)->ignore($member?->id),
                Rule::unique(User::class)->ignore($member?->user_id),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'birthday' => ['nullable', 'date', 'before:today'],
            'photo' => ['nullable', 'image', 'max:5120'],
            'remove_photo' => ['boolean'],
        ];

        if ($this->canChangeClub()) {
            $rules['club_id'] = ['required', Rule::exists(Club::class, 'id')];
        }

        if ($this->canUpdateMembership()) {
            $rules += [
                'member_number' => ['nullable', 'string', 'max:50', Rule::unique(Member::class)->ignore($member?->id)],
                'status' => ['required', Rule::enum(MemberStatus::class)],
                'inducted_at' => ['nullable', 'date', 'before_or_equal:today'],
                'password' => ['nullable', 'confirmed', Password::defaults()],
            ];
        }

        if ($this->canAssignPosition()) {
            $rules['position'] = ['required', Rule::enum(ClubPosition::class), $this->officeIsVacant()];
        }

        return $rules;
    }

    /**
     * Get the custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required_with' => 'An email is required to give this member a login.',
            'email.required' => 'This member has a login, so an email is required.',
        ];
    }

    /**
     * Get the member attributes the signed-in user is allowed to save.
     *
     * @return array<string, mixed>
     */
    public function memberAttributes(): array
    {
        $attributes = $this->safe()->except(['photo', 'remove_photo', 'password', 'password_confirmation']);

        if (! $this->member()) {
            $attributes['club_id'] ??= $this->actor()->member?->club_id;
            $attributes['position'] ??= ClubPosition::Member;
            $attributes['status'] ??= MemberStatus::Active;
        }

        return $attributes;
    }

    /**
     * Determine whether the user may move the member between clubs.
     */
    public function canChangeClub(): bool
    {
        return $this->actor()->can('changeClub', Member::class);
    }

    /**
     * Determine whether the user may edit membership details and the login password.
     */
    public function canUpdateMembership(): bool
    {
        $member = $this->member();

        return $member
            ? $this->actor()->can('updateMembership', $member)
            : $this->actor()->can('create', Member::class);
    }

    /**
     * Determine whether the user may appoint club officers.
     */
    public function canAssignPosition(): bool
    {
        $member = $this->member() ?? new Member(['club_id' => $this->targetClubId()]);

        return $this->actor()->can('assignPosition', $member);
    }

    /**
     * Ensure no other member of the target club already holds the chosen office.
     */
    private function officeIsVacant(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $position = ClubPosition::tryFrom((string) $value);

            if (! $position?->isOfficer() || ! $this->targetClubId()) {
                return;
            }

            $holder = Member::query()
                ->where('club_id', $this->targetClubId())
                ->where('position', $position)
                ->whereKeyNot($this->member()?->id)
                ->first();

            if ($holder) {
                $fail("{$holder->full_name} is already the {$position->label()} of this club. Change their position first.");
            }
        };
    }

    private function targetClubId(): ?int
    {
        if ($this->canChangeClub() && $this->filled('club_id')) {
            return $this->integer('club_id');
        }

        return $this->member()->club_id ?? $this->actor()->member?->club_id;
    }

    private function member(): ?Member
    {
        $member = $this->route('member');

        return $member instanceof Member ? $member : null;
    }

    private function actor(): User
    {
        /** @var User $user */
        $user = $this->user();

        return $user;
    }
}
