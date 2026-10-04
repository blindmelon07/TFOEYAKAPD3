<?php

use App\Enums\DuesStatus;

it('derives the standing from the rate and amount paid', function (?string $rate, string $paid, DuesStatus $expected) {
    expect(DuesStatus::for($rate, $paid))->toBe($expected);
})->with([
    'nothing paid' => ['1200.00', '0', DuesStatus::Unpaid],
    'part paid' => ['1200.00', '1199.99', DuesStatus::Partial],
    'exactly paid' => ['1200.00', '1200.00', DuesStatus::Paid],
    'overpaid' => ['1200.00', '1500.00', DuesStatus::Paid],
    'no rate, something paid' => [null, '50.00', DuesStatus::Paid],
    'no rate, nothing paid' => [null, '0', DuesStatus::Unpaid],
    'free year, nothing paid' => ['0.00', '0', DuesStatus::Unpaid],
]);

it('computes the remaining balance without going negative', function (?string $rate, string $paid, string $expected) {
    expect(DuesStatus::balance($rate, $paid))->toBe($expected);
})->with([
    'nothing paid' => ['1200.00', '0', '1200.00'],
    'part paid' => ['1200.00', '700.50', '499.50'],
    'overpaid' => ['1200.00', '1500.00', '0.00'],
    'no rate' => [null, '0', '0.00'],
    'float-unsafe amounts' => ['0.30', '0.10', '0.20'],
]);
