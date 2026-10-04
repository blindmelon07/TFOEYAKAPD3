<?php

namespace App\Enums;

enum DuesStatus: string
{
    case Paid = 'paid';
    case Partial = 'partial';
    case Unpaid = 'unpaid';

    /**
     * Work out a member's standing for one year from the club's rate and what they paid.
     *
     * Without a rate set for the year, any payment counts as paid in full.
     */
    public static function for(?string $rate, string $paid): self
    {
        $paidCents = self::toCents($paid);

        if ($paidCents === 0) {
            return self::Unpaid;
        }

        if ($rate === null || $paidCents >= self::toCents($rate)) {
            return self::Paid;
        }

        return self::Partial;
    }

    /**
     * Get the amount still owed for the year, never below zero.
     */
    public static function balance(?string $rate, string $paid): string
    {
        if ($rate === null) {
            return '0.00';
        }

        $owedCents = max(0, self::toCents($rate) - self::toCents($paid));

        return number_format($owedCents / 100, 2, '.', '');
    }

    private static function toCents(string $amount): int
    {
        return (int) round((float) $amount * 100);
    }
}
