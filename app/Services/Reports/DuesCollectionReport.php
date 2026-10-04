<?php

namespace App\Services\Reports;

use App\Enums\DuesStatus;
use App\Enums\MemberStatus;
use App\Models\Club;
use App\Models\Member;
use Illuminate\Http\Request;

/**
 * Who has paid a year's dues, how much was collected against what was
 * expected, and what is still outstanding. Deceased members are not billed.
 */
class DuesCollectionReport extends ClubReport
{
    public function key(): string
    {
        return 'dues-collection';
    }

    public function name(): string
    {
        return 'Dues Collection';
    }

    public function description(): string
    {
        return 'Paid, partial and unpaid members for a year, with amounts collected and outstanding.';
    }

    public function icon(): string
    {
        return 'payments';
    }

    public function filters(): array
    {
        return [['name' => 'year', 'label' => 'Dues year', 'type' => 'year']];
    }

    public function resolveFilters(Request $request): array
    {
        $validated = $request->validate([
            'year' => ['nullable', 'integer', 'min:1979', 'max:'.(now()->year + 1)],
        ]);

        return ['year' => (int) ($validated['year'] ?? now()->year)];
    }

    public function build(Club $club, array $filters): ReportData
    {
        $year = (int) $filters['year'];
        $rate = $club->duesRateFor($year);
        $rateCents = $rate === null ? null : self::cents($rate);

        $members = $club->members()
            ->where('status', '!=', MemberStatus::Deceased)
            ->withDuesFor($year)
            ->rosterOrder()
            ->get();

        $rows = [];
        $counts = [DuesStatus::Paid->value => 0, DuesStatus::Partial->value => 0, DuesStatus::Unpaid->value => 0];
        $collectedCents = 0;
        $outstandingCents = 0;

        foreach ($members as $member) {
            /** @var Member $member */
            $paid = (string) ($member->getAttribute('dues_paid') ?? '0');
            $standing = DuesStatus::for($rate, $paid);
            $balance = DuesStatus::balance($rate, $paid);

            $counts[$standing->value]++;
            $collectedCents += self::cents($paid);
            $outstandingCents += self::cents($balance);

            $rows[] = [
                'name' => $member->full_name,
                'member_number' => $member->member_number,
                'position' => $member->position->label(),
                'paid' => self::cents($paid) / 100,
                'balance' => self::cents($balance) / 100,
                'standing' => ucfirst($standing->value),
            ];
        }

        $expectedCents = $rateCents === null ? null : $rateCents * $members->count();

        $summary = [
            ['label' => 'Dues per member', 'value' => $rate === null ? 'Not set' : $rateCents / 100, 'type' => $rate === null ? 'text' : 'money', 'tone' => null],
            ['label' => 'Members billed', 'value' => $members->count(), 'type' => 'number', 'tone' => null],
            ['label' => 'Collected', 'value' => $collectedCents / 100, 'type' => 'money', 'tone' => null],
        ];

        if ($expectedCents !== null) {
            $summary[] = ['label' => 'Expected', 'value' => $expectedCents / 100, 'type' => 'money', 'tone' => null];
            $summary[] = ['label' => 'Outstanding', 'value' => $outstandingCents / 100, 'type' => 'money', 'tone' => $outstandingCents > 0 ? 'critical' : 'good'];
            $summary[] = [
                'label' => 'Collection rate',
                'value' => $expectedCents > 0 ? round(min($collectedCents, $expectedCents) / $expectedCents * 100, 1) : 100.0,
                'type' => 'percent',
                'tone' => null,
            ];
        }

        $summary[] = ['label' => 'Paid', 'value' => $counts['paid'], 'type' => 'number', 'tone' => 'good'];
        $summary[] = ['label' => 'Partial', 'value' => $counts['partial'], 'type' => 'number', 'tone' => 'warning'];
        $summary[] = ['label' => 'Unpaid', 'value' => $counts['unpaid'], 'type' => 'number', 'tone' => 'critical'];

        return new ReportData(
            title: "{$this->name()} {$year}",
            period: "Dues year {$year}",
            summary: $summary,
            charts: $members->isEmpty() ? [] : [[
                'kind' => 'proportion',
                'title' => "Members by dues standing, {$year}",
                'valueType' => 'number',
                'points' => [
                    ['label' => 'Paid', 'value' => $counts['paid'], 'tone' => 'good'],
                    ['label' => 'Partial', 'value' => $counts['partial'], 'tone' => 'warning'],
                    ['label' => 'Unpaid', 'value' => $counts['unpaid'], 'tone' => 'critical'],
                ],
            ]],
            sections: [[
                'title' => 'Members',
                'columns' => [
                    ['key' => 'name', 'label' => 'Member', 'type' => 'text'],
                    ['key' => 'member_number', 'label' => 'Member no.', 'type' => 'text'],
                    ['key' => 'position', 'label' => 'Position', 'type' => 'text'],
                    ['key' => 'paid', 'label' => 'Paid', 'type' => 'money'],
                    ['key' => 'balance', 'label' => 'Balance', 'type' => 'money'],
                    ['key' => 'standing', 'label' => 'Standing', 'type' => 'text'],
                ],
                'rows' => $rows,
                'totals' => $rows === [] ? null : [
                    'name' => 'Total',
                    'paid' => $collectedCents / 100,
                    'balance' => $outstandingCents / 100,
                ],
                'empty' => 'This club has no members to bill.',
            ]],
            notice: $rate === null
                ? "No dues amount is set for {$year}, so any payment counts as paid and balances are not tracked. Set one under Dues Rates."
                : null,
        );
    }

    private static function cents(string $amount): int
    {
        return (int) round((float) $amount * 100);
    }
}
