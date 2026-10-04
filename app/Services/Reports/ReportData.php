<?php

namespace App\Services\Reports;

/**
 * A rendered report, independent of output format. The on-screen page, the
 * print page, the Word file and the CSV are all produced from this one value,
 * so every format always shows the same numbers.
 *
 * Cell values stay raw (numbers as numbers) and each column declares its
 * type, so each format can present them appropriately: "₱1,200.00" on paper,
 * 1200 in a spreadsheet.
 *
 * @phpstan-type ColumnType 'text'|'money'|'number'|'date'|'percent'
 * @phpstan-type Column array{key: string, label: string, type: ColumnType}
 * @phpstan-type Cell string|int|float|null
 * @phpstan-type Section array{title: string, columns: list<Column>, rows: list<array<string, Cell>>, totals: array<string, Cell>|null, empty: string}
 * @phpstan-type SummaryItem array{label: string, value: Cell, type: ColumnType, tone: 'good'|'warning'|'critical'|null}
 * @phpstan-type Chart array{kind: 'columns'|'proportion', title: string, valueType: ColumnType, points: list<array{label: string, value: int|float, tone?: 'good'|'warning'|'critical'}>}
 */
final readonly class ReportData
{
    /**
     * @param  list<SummaryItem>  $summary
     * @param  list<Chart>  $charts
     * @param  list<Section>  $sections
     */
    public function __construct(
        public string $title,
        public string $period,
        public array $summary,
        public array $charts,
        public array $sections,
        public ?string $notice = null,
    ) {}

    /**
     * @return array{title: string, period: string, summary: list<SummaryItem>, charts: list<Chart>, sections: list<Section>, notice: string|null}
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'period' => $this->period,
            'summary' => $this->summary,
            'charts' => $this->charts,
            'sections' => $this->sections,
            'notice' => $this->notice,
        ];
    }
}
