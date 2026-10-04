<?php

namespace App\Services\Reports;

use App\Models\Club;
use Carbon\CarbonImmutable;

/**
 * Turns a ReportData into downloadable formats: CSV for spreadsheets and
 * rich-text HTML that LetterheadRenderer prints on the club's letterhead.
 *
 * @phpstan-import-type Cell from ReportData
 * @phpstan-import-type ColumnType from ReportData
 */
class ReportExporter
{
    /**
     * Format a cell for people to read: "₱1,200.00", "October 5, 2026", "85.0%".
     *
     * @param  Cell  $value
     * @param  ColumnType  $type
     */
    public static function display(string|int|float|null $value, string $type): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        if (is_string($value) && ! is_numeric($value) && $type !== 'date') {
            return $value;
        }

        return match ($type) {
            'money' => '₱'.number_format((float) $value, 2),
            'number' => number_format((float) $value),
            'percent' => number_format((float) $value, 1).'%',
            'date' => CarbonImmutable::parse((string) $value)->format('M j, Y'),
            default => (string) $value,
        };
    }

    /**
     * Build a CSV that opens cleanly in Excel: a UTF-8 byte order mark, a
     * header block, the summary, then every section with its own column row.
     * Numbers stay numeric so they can be summed.
     */
    public function toCsv(ReportData $report, Club $club): string
    {
        $handle = fopen('php://temp', 'r+');

        if ($handle === false) {
            return '';
        }

        $write = function (array $row) use ($handle): void {
            fputcsv($handle, $row, escape: '');
        };

        $write([$report->title]);
        $write([$club->name]);
        $write([$report->period]);
        $write(['Generated', now()->format('Y-m-d H:i')]);

        if ($report->notice !== null) {
            $write([$report->notice]);
        }

        $write([]);

        foreach ($report->summary as $item) {
            $write([$item['label'], $this->csvValue($item['value'], $item['type'])]);
        }

        foreach ($report->sections as $section) {
            $write([]);
            $write([$section['title']]);
            $write(array_column($section['columns'], 'label'));

            foreach ($section['rows'] as $row) {
                $write(array_map(
                    fn (array $column): string|int|float => $this->csvValue($row[$column['key']] ?? null, $column['type']),
                    $section['columns'],
                ));
            }

            if ($section['totals'] !== null) {
                $totals = $section['totals'];
                $write(array_map(
                    fn (array $column): string|int|float => $this->csvValue($totals[$column['key']] ?? null, $column['type']),
                    $section['columns'],
                ));
            }
        }

        rewind($handle);
        $csv = (string) stream_get_contents($handle);
        fclose($handle);

        return "\u{FEFF}".$csv;
    }

    /**
     * Build editor-style HTML (paragraphs, bold, tables) for the Word file.
     */
    public function toWordHtml(ReportData $report, Club $club): string
    {
        $html = '<p style="text-align: center"><strong>'.e($club->name).'</strong></p>';
        $html .= '<p style="text-align: center">'.e($report->period).'</p>';

        if ($report->notice !== null) {
            $html .= '<p><em>'.e($report->notice).'</em></p>';
        }

        $html .= '<p><strong>Summary</strong></p><table><tbody>';

        foreach (array_chunk($report->summary, 2) as $pair) {
            $html .= '<tr>';

            foreach ($pair as $item) {
                $html .= '<td><p>'.e($item['label']).'</p></td><td><p><strong>'.e(self::display($item['value'], $item['type'])).'</strong></p></td>';
            }

            if (count($pair) === 1) {
                $html .= '<td><p></p></td><td><p></p></td>';
            }

            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        foreach ($report->sections as $section) {
            $html .= '<p></p><p><strong>'.e($section['title']).'</strong></p>';

            if ($section['rows'] === []) {
                $html .= '<p><em>'.e($section['empty']).'</em></p>';

                continue;
            }

            $html .= '<table><tbody><tr>';

            foreach ($section['columns'] as $column) {
                $html .= '<th><p>'.e($column['label']).'</p></th>';
            }

            $html .= '</tr>';

            foreach ($section['rows'] as $row) {
                $html .= '<tr>';

                foreach ($section['columns'] as $column) {
                    $html .= '<td><p>'.e(self::display($row[$column['key']] ?? null, $column['type'])).'</p></td>';
                }

                $html .= '</tr>';
            }

            if ($section['totals'] !== null) {
                $html .= '<tr>';

                foreach ($section['columns'] as $column) {
                    $value = $section['totals'][$column['key']] ?? null;
                    $html .= '<td><p><strong>'.($value === null ? '' : e(self::display($value, $column['type']))).'</strong></p></td>';
                }

                $html .= '</tr>';
            }

            $html .= '</tbody></table>';
        }

        return $html;
    }

    /**
     * @param  Cell  $value
     * @param  ColumnType  $type
     */
    private function csvValue(string|int|float|null $value, string $type): string|int|float
    {
        if ($value === null) {
            return '';
        }

        if ($type === 'percent' && is_numeric($value)) {
            return round((float) $value / 100, 4);
        }

        return $value;
    }
}
