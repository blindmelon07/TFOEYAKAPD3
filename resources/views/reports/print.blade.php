@php
    use App\Services\Reports\ReportExporter;

    /** @var \App\Models\Club $club */
    /** @var \App\Services\Reports\ReportData $report */
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $report->title }} · {{ $club->name }}</title>
    <link rel="icon" href="/favicon.ico?v=2">
    <style>
        @page {
            size: 8.5in 13in;
            margin: 0.4in 0.6in;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #ebeef3;
            color: #000;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10.5pt;
            line-height: 1.35;
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 1;
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            padding: 0.75rem;
            background: #000d29;
        }

        .toolbar button {
            padding: 0.55rem 1.25rem;
            border: 0;
            border-radius: 4px;
            font: 600 0.875rem Arial, sans-serif;
            cursor: pointer;
        }

        .toolbar .primary {
            background: #fed65b;
            color: #241a00;
        }

        .toolbar .secondary {
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
        }

        .page {
            width: 8.5in;
            max-width: 100%;
            min-height: 13in;
            margin: 1rem auto;
            padding: 0.4in 0.6in;
            background: #fff;
            box-shadow: 0 8px 24px rgba(15, 35, 71, 0.15);
        }

        .banner {
            display: block;
            width: 100%;
            margin-bottom: 0.15in;
        }

        h1 {
            margin: 0;
            text-align: center;
            font-size: 15pt;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .club,
        .period {
            margin: 0.04in 0 0;
            text-align: center;
        }

        .club {
            font-weight: 700;
        }

        .generated {
            margin: 0.06in 0 0.2in;
            text-align: right;
            font-size: 9pt;
            color: #44474e;
        }

        .notice {
            margin: 0 0 0.15in;
            padding: 0.08in 0.12in;
            border-left: 3px solid #e9c349;
            background: #fffbeb;
            font-style: italic;
        }

        .summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.08in;
            margin-bottom: 0.22in;
        }

        .summary div {
            padding: 0.07in 0.1in;
            border: 1px solid #c5c6cf;
            border-radius: 3px;
        }

        .summary span {
            display: block;
            font-size: 8.5pt;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #44474e;
        }

        .summary strong {
            font-size: 12.5pt;
        }

        h2 {
            margin: 0.2in 0 0.08in;
            font-size: 11.5pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: auto;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
        }

        th,
        td {
            padding: 0.04in 0.06in;
            border: 1px solid #44474e;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #ebeef3;
            font-size: 9pt;
        }

        td.number {
            text-align: right;
            white-space: nowrap;
        }

        tfoot td {
            font-weight: 700;
            background: #f7f9ff;
        }

        .empty {
            font-style: italic;
            color: #44474e;
        }

        .signature {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.6in;
            margin-top: 0.6in;
        }

        .signature div {
            padding-top: 0.05in;
            border-top: 1px solid #000;
            text-align: center;
            font-size: 9.5pt;
        }

        @media print {
            body {
                background: #fff;
            }

            .toolbar {
                display: none;
            }

            .page {
                width: auto;
                min-height: 0;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
        }

        @media (max-width: 640px) {
            .page {
                padding: 0.25in;
            }

            .summary {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" class="primary" onclick="window.print()">Print or save as PDF</button>
        <button type="button" class="secondary" onclick="window.close()">Close</button>
    </div>

    <main class="page">
        <img class="banner" src="/images/letterhead-banner.png" alt="The Fraternal Order of Eagles (Philippine Eagles)">

        <h1>{{ $report->title }}</h1>
        <p class="club">{{ $club->name }}{{ $club->location ? ' · '.$club->location : '' }}</p>
        <p class="period">{{ $report->period }}</p>
        <p class="generated">Generated {{ now()->format('F j, Y g:i A') }}</p>

        @if ($report->notice)
            <p class="notice">{{ $report->notice }}</p>
        @endif

        <section class="summary">
            @foreach ($report->summary as $item)
                <div>
                    <span>{{ $item['label'] }}</span>
                    <strong>{{ ReportExporter::display($item['value'], $item['type']) }}</strong>
                </div>
            @endforeach
        </section>

        @foreach ($report->sections as $section)
            <h2>{{ $section['title'] }}</h2>

            @if ($section['rows'] === [])
                <p class="empty">{{ $section['empty'] }}</p>
            @else
                <table>
                    <thead>
                        <tr>
                            @foreach ($section['columns'] as $column)
                                <th>{{ $column['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($section['rows'] as $row)
                            <tr>
                                @foreach ($section['columns'] as $column)
                                    <td @class(['number' => in_array($column['type'], ['money', 'number', 'percent'], true)])>
                                        {{ ReportExporter::display($row[$column['key']] ?? null, $column['type']) }}
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                    @if ($section['totals'])
                        <tfoot>
                            <tr>
                                @foreach ($section['columns'] as $column)
                                    @php($total = $section['totals'][$column['key']] ?? null)
                                    <td @class(['number' => in_array($column['type'], ['money', 'number', 'percent'], true)])>
                                        {{ $total === null ? '' : ReportExporter::display($total, $column['type']) }}
                                    </td>
                                @endforeach
                            </tr>
                        </tfoot>
                    @endif
                </table>
            @endif
        @endforeach

        <section class="signature">
            <div>Prepared by</div>
            <div>Noted by</div>
        </section>
    </main>

    <script>
        window.addEventListener('load', () => window.print());
    </script>
</body>
</html>
