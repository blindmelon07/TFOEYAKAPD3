<?php

namespace App\Services\Reports;

use App\Models\Club;
use App\Models\DuesPayment;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

/**
 * Every dues payment recorded within a date range, with totals.
 */
class PaymentLedgerReport extends ClubReport
{
    /**
     * Ranges longer than this are charted by year instead of by month.
     */
    private const int MAX_CHART_MONTHS = 24;

    public function key(): string
    {
        return 'payment-ledger';
    }

    public function name(): string
    {
        return 'Payment Ledger';
    }

    public function description(): string
    {
        return 'Every dues payment received in a date range, with OR numbers and totals.';
    }

    public function icon(): string
    {
        return 'receipt_long';
    }

    public function filters(): array
    {
        return [
            ['name' => 'from', 'label' => 'From', 'type' => 'date'],
            ['name' => 'to', 'label' => 'To', 'type' => 'date'],
        ];
    }

    public function resolveFilters(Request $request): array
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        return [
            'from' => CarbonImmutable::parse($validated['from'] ?? now()->startOfYear())->format('Y-m-d'),
            'to' => CarbonImmutable::parse($validated['to'] ?? now())->format('Y-m-d'),
        ];
    }

    public function build(Club $club, array $filters): ReportData
    {
        $from = CarbonImmutable::parse((string) $filters['from'])->startOfDay();
        $to = CarbonImmutable::parse((string) $filters['to'])->endOfDay();

        $payments = DuesPayment::query()
            ->whereHas('member', fn ($query) => $query->where('club_id', $club->id))
            ->whereBetween('paid_at', [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->with(['member:id,first_name,middle_name,last_name,member_number', 'recorder:id,name'])
            ->orderBy('paid_at')
            ->orderBy('id')
            ->get();

        $totalCents = 0;
        $rows = [];

        foreach ($payments as $payment) {
            $cents = (int) round((float) $payment->amount * 100);
            $totalCents += $cents;

            $rows[] = [
                'paid_at' => $payment->paid_at->format('Y-m-d'),
                'member' => $payment->member->full_name,
                'member_number' => $payment->member->member_number,
                'year' => $payment->year,
                'amount' => $cents / 100,
                'reference' => $payment->reference,
                'recorded_by' => $payment->recorder?->name,
            ];
        }

        $period = $from->format('F j, Y').' – '.$to->format('F j, Y');
        $count = $payments->count();

        return new ReportData(
            title: $this->name(),
            period: $period,
            summary: [
                ['label' => 'Payments', 'value' => $count, 'type' => 'number', 'tone' => null],
                ['label' => 'Total received', 'value' => $totalCents / 100, 'type' => 'money', 'tone' => null],
                ['label' => 'Members who paid', 'value' => $payments->pluck('member_id')->unique()->count(), 'type' => 'number', 'tone' => null],
                ['label' => 'Average payment', 'value' => $count > 0 ? round($totalCents / $count) / 100 : 0, 'type' => 'money', 'tone' => null],
            ],
            charts: $count === 0 ? [] : [$this->chart($from, $to, $rows)],
            sections: [[
                'title' => 'Payments',
                'columns' => [
                    ['key' => 'paid_at', 'label' => 'Date paid', 'type' => 'date'],
                    ['key' => 'member', 'label' => 'Member', 'type' => 'text'],
                    ['key' => 'member_number', 'label' => 'Member no.', 'type' => 'text'],
                    ['key' => 'year', 'label' => 'Dues year', 'type' => 'text'],
                    ['key' => 'amount', 'label' => 'Amount', 'type' => 'money'],
                    ['key' => 'reference', 'label' => 'OR / Ref.', 'type' => 'text'],
                    ['key' => 'recorded_by', 'label' => 'Recorded by', 'type' => 'text'],
                ],
                'rows' => $rows,
                'totals' => $rows === [] ? null : ['paid_at' => 'Total', 'amount' => $totalCents / 100],
                'empty' => 'No payments were recorded in this period.',
            ]],
        );
    }

    /**
     * Total received per month (or per year for long ranges), including
     * periods with no payments so gaps are visible.
     *
     * @param  list<array<string, string|int|float|null>>  $rows
     * @return array{kind: 'columns', title: string, valueType: 'money', points: list<array{label: string, value: float}>}
     */
    private function chart(CarbonImmutable $from, CarbonImmutable $to, array $rows): array
    {
        $byYear = $from->diffInMonths($to) >= self::MAX_CHART_MONTHS;
        $format = $byYear ? 'Y' : 'Y-m';

        $totals = [];

        foreach (CarbonPeriod::create($from->startOfMonth(), $byYear ? '1 year' : '1 month', $to) as $date) {
            $totals[$date->format($format)] = 0;
        }

        foreach ($rows as $row) {
            $bucket = CarbonImmutable::parse((string) $row['paid_at'])->format($format);
            $totals[$bucket] = ($totals[$bucket] ?? 0) + (int) round((float) $row['amount'] * 100);
        }

        $points = [];

        foreach ($totals as $bucket => $cents) {
            $points[] = [
                'label' => $byYear ? (string) $bucket : CarbonImmutable::createFromFormat('Y-m', (string) $bucket)?->format('M Y') ?? (string) $bucket,
                'value' => $cents / 100,
            ];
        }

        return [
            'kind' => 'columns',
            'title' => $byYear ? 'Received per year' : 'Received per month',
            'valueType' => 'money',
            'points' => $points,
        ];
    }
}
