<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Services\LetterheadRenderer;
use App\Services\Reports\ClubReport;
use App\Services\Reports\ReportExporter;
use App\Services\Reports\ReportRegistry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ClubReportController extends Controller
{
    public function __construct(private ReportRegistry $reports) {}

    /**
     * List the reports available for the club.
     */
    public function index(Club $club): Response
    {
        Gate::authorize('viewReports', $club);

        return Inertia::render('admin/clubs/reports/index', [
            'club' => $club->only(['id', 'name']),
            'reports' => array_map(fn (ClubReport $report): array => $report->card(), $this->reports->all()),
        ]);
    }

    /**
     * Show a report on screen with its filters, summary, charts and tables.
     */
    public function show(Request $request, Club $club, string $report): Response
    {
        Gate::authorize('viewReports', $club);

        $definition = $this->reports->find($report);
        $filters = $definition->resolveFilters($request);

        return Inertia::render('admin/clubs/reports/show', [
            'club' => $club->only(['id', 'name']),
            'report' => [
                ...$definition->card(),
                'filters' => $definition->filters(),
            ],
            'filters' => $filters,
            'data' => $definition->build($club, $filters)->toArray(),
        ]);
    }

    /**
     * A plain page laid out for printing on long bond paper.
     */
    public function print(Request $request, Club $club, string $report): View
    {
        Gate::authorize('viewReports', $club);

        $definition = $this->reports->find($report);

        return view('reports.print', [
            'club' => $club,
            'report' => $definition->build($club, $definition->resolveFilters($request)),
        ]);
    }

    /**
     * Download the report as a Word file on the club's letterhead.
     */
    public function word(Request $request, LetterheadRenderer $renderer, ReportExporter $exporter, Club $club, string $report): BinaryFileResponse
    {
        Gate::authorize('viewReports', $club);

        $definition = $this->reports->find($report);
        $data = $definition->build($club, $definition->resolveFilters($request));

        $path = $renderer->render(
            $club->letterheadTemplatePath(),
            [
                '[title]' => Str::upper($data->title),
                '{date}' => now()->format('F j, Y'),
                '{club_name}' => $club->name,
                '{club_location}' => (string) $club->location,
                '{charter_number}' => (string) $club->charter_number,
            ],
            $exporter->toWordHtml($data, $club),
        );

        return response()
            ->download($path, $this->fileName($club, $data->title, 'docx'), [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ])
            ->deleteFileAfterSend();
    }

    /**
     * Download the report as a CSV spreadsheet.
     */
    public function csv(Request $request, ReportExporter $exporter, Club $club, string $report): HttpResponse
    {
        Gate::authorize('viewReports', $club);

        $definition = $this->reports->find($report);
        $data = $definition->build($club, $definition->resolveFilters($request));

        return response($exporter->toCsv($data, $club), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$this->fileName($club, $data->title, 'csv').'"',
        ]);
    }

    private function fileName(Club $club, string $title, string $extension): string
    {
        return Str::slug("{$club->name} {$title}").'-'.now()->format('Y-m-d').".{$extension}";
    }
}
