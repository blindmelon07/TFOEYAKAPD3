<?php

namespace App\Services\Reports;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The club reports available in the admin panel, in display order.
 */
class ReportRegistry
{
    /**
     * @var array<string, ClubReport>
     */
    private array $reports = [];

    public function __construct(
        DuesCollectionReport $duesCollection,
        PaymentLedgerReport $paymentLedger,
        MembershipRosterReport $membershipRoster,
        MembershipSummaryReport $membershipSummary,
    ) {
        foreach ([$duesCollection, $paymentLedger, $membershipRoster, $membershipSummary] as $report) {
            $this->reports[$report->key()] = $report;
        }
    }

    /**
     * @return list<ClubReport>
     */
    public function all(): array
    {
        return array_values($this->reports);
    }

    /**
     * @return list<string>
     */
    public function keys(): array
    {
        return array_keys($this->reports);
    }

    public function find(string $key): ClubReport
    {
        return $this->reports[$key] ?? throw new NotFoundHttpException("Unknown report [{$key}].");
    }
}
