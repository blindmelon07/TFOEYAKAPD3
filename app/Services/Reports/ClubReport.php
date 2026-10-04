<?php

namespace App\Services\Reports;

use App\Models\Club;
use Illuminate\Http\Request;

/**
 * A report about one club. Subclasses declare their filters and build a
 * format-independent ReportData.
 *
 * @phpstan-type Filter array{name: string, label: string, type: 'year'|'date'|'select', options?: list<array{value: string, label: string}>}
 */
abstract class ClubReport
{
    /**
     * The URL key, e.g. "dues-collection".
     */
    abstract public function key(): string;

    abstract public function name(): string;

    abstract public function description(): string;

    /**
     * The Material Symbols icon shown on the report card.
     */
    abstract public function icon(): string;

    /**
     * The filters the report accepts, for the on-screen filter bar.
     *
     * @return list<Filter>
     */
    abstract public function filters(): array;

    /**
     * Read and validate this report's filters from the request, with defaults.
     *
     * @return array<string, string|int>
     */
    abstract public function resolveFilters(Request $request): array;

    /**
     * @param  array<string, string|int>  $filters
     */
    abstract public function build(Club $club, array $filters): ReportData;

    /**
     * Describe the report for the reports index.
     *
     * @return array{key: string, name: string, description: string, icon: string}
     */
    public function card(): array
    {
        return [
            'key' => $this->key(),
            'name' => $this->name(),
            'description' => $this->description(),
            'icon' => $this->icon(),
        ];
    }
}
