@props(['title' => null, 'meta' => null, 'summary' => null])

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
            padding: 20px;
        }

        .report-title {
            margin: 0 0 8px;
            font-size: 22px;
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
            page-break-inside: avoid;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px 10px;
            vertical-align: top;
        }

        th {
            background: #f0f0f0;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 10px;
        }

        .text-right {
            text-align: right;
        }

        .col-primary {
            font-weight: 700;
        }

        tbody tr:nth-child(even) {
            background: #f9f9f9;
        }

        .table-heading {
            margin: 16px 0 4px;
            font-size: 12px;
            font-weight: 700;
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

        {{ $slot }}
    </div>
</body>
</html>
