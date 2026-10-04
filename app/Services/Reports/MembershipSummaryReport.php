<?php

namespace App\Services\Reports;

use App\Enums\ClubPosition;
use App\Enums\MemberStatus;
use App\Models\Club;
use App\Models\Member;
use Illuminate\Http\Request;

/**
 * The club at a glance: membership by status, officers in place, and how
 * many members were inducted each year.
 */
class MembershipSummaryReport extends ClubReport
{
    /**
     * How many recent years the induction chart covers.
     */
    private const int CHART_YEARS = 10;

    public function key(): string
    {
        return 'membership-summary';
    }

    public function name(): string
    {
        return 'Membership Summary';
    }

    public function description(): string
    {
        return 'Members by status, officers and vacancies, and new members inducted per year.';
    }

    public function icon(): string
    {
        return 'monitoring';
    }

    public function filters(): array
    {
        return [];
    }

    public function resolveFilters(Request $request): array
    {
        return [];
    }

    public function build(Club $club, array $filters): ReportData
    {
        $members = $club->members()->get();
        $total = $members->count();

        $statusRows = array_map(function (MemberStatus $status) use ($members, $total): array {
            $count = $members->where('status', $status)->count();

            return [
                'status' => $status->label(),
                'count' => $count,
                'share' => $total > 0 ? round($count / $total * 100, 1) : 0,
            ];
        }, MemberStatus::cases());

        $officerRows = array_map(function (ClubPosition $office) use ($members): array {
            /** @var Member|null $holder */
            $holder = $members->firstWhere('position', $office);

            return [
                'position' => $office->label(),
                'name' => $holder->full_name ?? 'Vacant',
                'member_number' => $holder?->member_number,
            ];
        }, ClubPosition::offices());

        $filledOffices = count(array_filter($officerRows, fn (array $row): bool => $row['name'] !== 'Vacant'));

        $inductedByYear = $members
            ->filter(fn (Member $member): bool => $member->inducted_at !== null)
            ->countBy(fn (Member $member): int => (int) $member->inducted_at?->year)
            ->sortKeysDesc();

        $inductionRows = array_values($inductedByYear
            ->map(fn (int $count, int $year): array => ['year' => (string) $year, 'count' => $count])
            ->all());

        $notRecorded = $members->whereNull('inducted_at')->count();

        if ($notRecorded > 0) {
            $inductionRows[] = ['year' => 'Not recorded', 'count' => $notRecorded];
        }

        $currentYear = now()->year;
        $chartPoints = [];

        for ($year = $currentYear - self::CHART_YEARS + 1; $year <= $currentYear; $year++) {
            $chartPoints[] = ['label' => (string) $year, 'value' => $inductedByYear->get($year, 0)];
        }

        return new ReportData(
            title: $this->name(),
            period: 'As of '.now()->format('F j, Y'),
            summary: [
                ['label' => 'Total members', 'value' => $total, 'type' => 'number', 'tone' => null],
                ['label' => 'Active', 'value' => $members->where('status', MemberStatus::Active)->count(), 'type' => 'number', 'tone' => null],
                ['label' => 'Offices filled', 'value' => "{$filledOffices} of ".count($officerRows), 'type' => 'text', 'tone' => $filledOffices === count($officerRows) ? 'good' : 'warning'],
                ['label' => "Inducted in {$currentYear}", 'value' => $inductedByYear->get($currentYear, 0), 'type' => 'number', 'tone' => null],
            ],
            charts: $total === 0 ? [] : [[
                'kind' => 'columns',
                'title' => 'New members inducted per year',
                'valueType' => 'number',
                'points' => $chartPoints,
            ]],
            sections: [
                [
                    'title' => 'Officers',
                    'columns' => [
                        ['key' => 'position', 'label' => 'Position', 'type' => 'text'],
                        ['key' => 'name', 'label' => 'Officer', 'type' => 'text'],
                        ['key' => 'member_number', 'label' => 'Member no.', 'type' => 'text'],
                    ],
                    'rows' => $officerRows,
                    'totals' => null,
                    'empty' => '',
                ],
                [
                    'title' => 'Members by status',
                    'columns' => [
                        ['key' => 'status', 'label' => 'Status', 'type' => 'text'],
                        ['key' => 'count', 'label' => 'Members', 'type' => 'number'],
                        ['key' => 'share', 'label' => 'Share', 'type' => 'percent'],
                    ],
                    'rows' => $statusRows,
                    'totals' => ['status' => 'Total', 'count' => $total, 'share' => $total > 0 ? 100.0 : 0],
                    'empty' => '',
                ],
                [
                    'title' => 'Members inducted per year',
                    'columns' => [
                        ['key' => 'year', 'label' => 'Year inducted', 'type' => 'text'],
                        ['key' => 'count', 'label' => 'Members', 'type' => 'number'],
                    ],
                    'rows' => $inductionRows,
                    'totals' => $inductionRows === [] ? null : ['year' => 'Total', 'count' => $total],
                    'empty' => 'No members yet.',
                ],
            ],
        );
    }
}
