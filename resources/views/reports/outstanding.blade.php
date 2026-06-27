<x-app-layout>

    <x-slot name="header">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

            <div>

                <h1 class="text-2xl font-bold text-slate-900">Outstanding Due Report</h1>

                <p class="mt-1 text-sm text-slate-600">Total purchase, payment, and due amount per pump.</p>

            </div>

            <div class="flex flex-wrap gap-2 print:hidden">

                <a href="{{ route('reports.outstanding.export') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-400 bg-white px-4 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-50">Export Excel</a>

                <button type="button" onclick="window.print()" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-400 bg-white px-4 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-50">Print</button>

            </div>

        </div>

    </x-slot>



    <x-reports.print-header title="Outstanding Due Report" :meta="'As of '.now()->format('d M Y')" />



    <div class="report-print-body mb-6 grid gap-6 lg:grid-cols-2">

        <div class="rounded-2xl bg-white p-5 ring-1 ring-slate-200 print:hidden">

            <h2 class="font-semibold text-slate-900">Due by Pump</h2>

            <div class="mt-4 h-72"><canvas id="outstandingReportChart"></canvas></div>

        </div>

        <x-data-table-card class="lg:col-span-1">

            <thead>

                <tr>

                    <th>Pump</th>

                    <th class="text-right">Purchase</th>

                    <th class="text-right">Payment</th>

                    <th class="text-right">Due</th>

                </tr>

            </thead>

            <tbody>

                @foreach ($rows as $row)

                    <tr @class(['bg-rose-50' => $row['over_limit']])>

                        <td class="col-primary font-medium" data-label="Pump">{{ $row['pump'] }}</td>

                        <td class="text-right" data-label="Purchase">{{ number_format($row['total_purchase'], 2) }}</td>

                        <td class="text-right" data-label="Payment">{{ number_format($row['total_payment'], 2) }}</td>

                        <td class="text-right font-semibold" data-label="Due">{{ number_format($row['due'], 2) }}</td>

                    </tr>

                @endforeach

            </tbody>

        </x-data-table-card>

    </div>



    <x-reports.print-footer :summary="'Total due: '.number_format($rows->sum('due'), 2).' '.$company->currency" />



    <script type="application/json" id="outstanding-report-chart">@json($chart)</script>

</x-app-layout>

