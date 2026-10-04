<?php

namespace App\Services\Reports;

use App\Enums\MemberStatus;
use App\Models\Club;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * The club's masterlist: officers first, then members alphabetically.
 */
class MembershipRosterReport extends ClubReport
{
    public function key(): string
    {
        return 'membership-roster';
    }

    public function name(): string
    {
        return 'Membership Roster';
    }

    public function description(): string
    {
        return 'Masterlist of members with member numbers, positions, status and contact details.';
    }

    public function icon(): string
    {
        return 'list_alt';
    }

    public function filters(): array
    {
        return [[
            'name' => 'status',
            'label' => 'Status',
            'type' => 'select',
            'options' => [['value' => 'all', 'label' => 'All statuses'], ...MemberStatus::options()],
        ]];
    }

    public function resolveFilters(Request $request): array
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::in(['all', ...array_column(MemberStatus::cases(), 'value')])],
        ]);

        return ['status' => $validated['status'] ?? 'all'];
    }

    public function build(Club $club, array $filters): ReportData
    {
        $status = (string) $filters['status'];

        $members = $club->members()
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->rosterOrder()
            ->get();

        $rows = $members->values()->map(fn (Member $member, int $index): array => [
            'number' => $index + 1,
            'name' => $member->full_name,
            'member_number' => $member->member_number,
            'position' => $member->position->label(),
            'status' => $member->status->label(),
            'inducted_at' => $member->inducted_at?->format('Y-m-d'),
            'phone' => $member->phone,
            'email' => $member->email,
        ])->all();

        $statusLabel = $status === 'all' ? 'All statuses' : MemberStatus::from($status)->label().' members';

        return new ReportData(
            title: $this->name(),
            period: "{$statusLabel} as of ".now()->format('F j, Y'),
            summary: [
                ['label' => 'Members listed', 'value' => $members->count(), 'type' => 'number', 'tone' => null],
                ['label' => 'Officers', 'value' => $members->filter(fn (Member $member): bool => $member->position->isOfficer())->count(), 'type' => 'number', 'tone' => null],
                ['label' => 'Active', 'value' => $members->where('status', MemberStatus::Active)->count(), 'type' => 'number', 'tone' => null],
            ],
            charts: [],
            sections: [[
                'title' => 'Members',
                'columns' => [
                    ['key' => 'number', 'label' => '#', 'type' => 'number'],
                    ['key' => 'name', 'label' => 'Name', 'type' => 'text'],
                    ['key' => 'member_number', 'label' => 'Member no.', 'type' => 'text'],
                    ['key' => 'position', 'label' => 'Position', 'type' => 'text'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'text'],
                    ['key' => 'inducted_at', 'label' => 'Inducted', 'type' => 'date'],
                    ['key' => 'phone', 'label' => 'Mobile', 'type' => 'text'],
                    ['key' => 'email', 'label' => 'Email', 'type' => 'text'],
                ],
                'rows' => array_values($rows),
                'totals' => null,
                'empty' => 'No members match this filter.',
            ]],
        );
    }
}
