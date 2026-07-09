@props(['title' => null, 'meta' => null, 'summary' => null, 'tables' => []])

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: #000;
            font-size: 11px;
        }

        .page {
            width: 100%;
            padding: 24px;
        }

        .report-title {
            margin: 0 0 6px;
            font-size: 24px;
            font-weight: 700;
        }

        .report-meta,
        .report-summary {
            margin: 0 0 12px;
            line-height: 1.4;
        }

        .report-meta {
            color: #444;
            font-size: 10px;
        }

        .report-summary {
            color: #000;
            font-size: 11px;
            font-weight: 700;
        }

        .table-heading {
            margin: 18px 0 8px;
            font-size: 12px;
            font-weight: 700;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
            page-break-inside: auto;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        thead {
            display: table-header-group;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px 10px;
            vertical-align: top;
            font-size: 10px;
        }

        th {
            background: #f0f0f0;
            font-weight: 700;
            text-transform: uppercase;
        }

        .text-right {
            text-align: right;
        }

        tbody tr:nth-child(even) {
            background: #f8f8f8;
        }

        .empty-row td {
            text-align: center;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="page">
        <h1 class="report-title">{{ $title }}</h1>

        @if ($meta)
            <div class="report-meta">{{ is_array($meta) ? implode(' | ', $meta) : $meta }}</div>
        @endif

        @if ($summary)
            <div class="report-summary">{{ $summary }}</div>
        @endif

        @foreach ($tables as $table)
            @if (! empty($table['title']))
                <div class="table-heading">{{ $table['title'] }}</div>
            @endif

            <table>
                <thead>
                    <tr>
                        @foreach ($table['columns'] as $column)
                            <th>{{ $column }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @if (count($table['rows']) > 0)
                        @foreach ($table['rows'] as $row)
                            <tr>
                                @foreach ($row as $cell)
                                    <td class="{{ is_numeric($cell) || preg_match('/^\d[\d,.]*$/', (string) $cell) ? 'text-right' : '' }}">{{ $cell }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    @else
                        <tr class="empty-row">
                            <td colspan="{{ count($table['columns']) }}">No data available.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        @endforeach
    </div>
</body>
</html>
