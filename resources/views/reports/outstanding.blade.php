<x-app-layout>

    <x-slot name="header">

        <div>

            <h1 class="text-2xl font-bold text-slate-900">Pump Summary</h1>

            <p class="mt-1 text-sm text-slate-600">Purchase entries, amount due, and advance balance left per pump.</p>

        </div>

    </x-slot>



    <x-reports.filter-card

        :action="route('reports.outstanding')"

        :export="route('reports.outstanding.export', request()->query())"

    >

        <div class="form-field">

            <x-input-label for="date_from" value="From" />

            <x-text-input id="date_from" name="date_from" type="date" :value="$filters['date_from'] ?? ''" />

        </div>

        <div class="form-field">

            <x-input-label for="date_to" value="To" />

            <x-text-input id="date_to" name="date_to" type="date" :value="$filters['date_to'] ?? ''" />

        </div>

    </x-reports.filter-card>



    <div class="report-screen-only mb-4 grid gap-4 sm:grid-cols-3">

        <div class="rounded-2xl bg-white p-4 ring-1 ring-slate-200">

            <p class="text-xs text-slate-500">Total Entries</p>

            <p class="text-2xl font-bold text-slate-900">{{ number_format($totals['entries']) }}</p>

        </div>

        <div class="rounded-2xl bg-white p-4 ring-1 ring-slate-200">

            <p class="text-xs text-slate-500">Total Due</p>

            <p class="text-2xl font-bold text-rose-700">{{ number_format($totals['due'], 2) }} {{ $company->currency }}</p>

        </div>

        <div class="rounded-2xl bg-white p-4 ring-1 ring-slate-200">

            <p class="text-xs text-slate-500">Total Advance Left</p>

            <p class="text-2xl font-bold text-violet-700">{{ number_format($totals['advance'], 2) }} {{ $company->currency }}</p>

        </div>

    </div>



    <x-reports.print-shell

        title="Pump Summary Report"

        :meta="collect([

            ($filters['date_from'] ?? null) || ($filters['date_to'] ?? null) ? 'Period: '.($filters['date_from'] ?? 'Start').' to '.($filters['date_to'] ?? 'Today') : null,

            'As of '.now()->format('d M Y'),

        ])->filter()->implode(' | ')"

        :summary="'Due: '.number_format($totals['due'], 2).' '.$company->currency.' | Advance: '.number_format($totals['advance'], 2).' '.$company->currency"

    >

        <div class="report-print-body mb-6 grid gap-6 lg:grid-cols-2">

            <div class="rounded-2xl bg-white p-5 ring-1 ring-slate-200 print:hidden">

                <h2 class="font-semibold text-slate-900">Due by Pump</h2>

                <div class="mt-4 h-72"><canvas id="outstandingReportChart"></canvas></div>

            </div>

            <x-data-table-card class="lg:col-span-1">

                <thead>

                    <tr>

                        <th>Pump</th>

                        <th class="text-right">Entries</th>

                        <th class="text-right">Purchase</th>

                        <th class="text-right">Payment</th>

                        <th class="text-right">Due</th>

                        <th class="text-right">Advance</th>

                    </tr>

                </thead>

                <tbody>

                    @foreach ($rows as $row)

                        <tr @class(['bg-rose-50' => $row['over_limit']])>

                            <td class="col-primary font-medium" data-label="Pump">{{ $row['pump'] }}</td>

                            <td class="text-right" data-label="Entries">{{ number_format($row['entries']) }}</td>

                            <td class="text-right" data-label="Purchase">{{ number_format($row['total_purchase'], 2) }}</td>

                            <td class="text-right" data-label="Payment">{{ number_format($row['total_payment'], 2) }}</td>

                            <td @class([
                                'text-right font-semibold',
                                'text-rose-700' => $row['due'] > 0,
                                'text-slate-400' => $row['due'] <= 0,
                            ]) data-label="Due">

                                {{ $row['due'] > 0 ? number_format($row['due'], 2) : '—' }}

                            </td>

                            <td @class([
                                'text-right font-semibold',
                                'text-violet-700' => $row['advance'] > 0,
                                'text-slate-400' => $row['advance'] <= 0,
                            ]) data-label="Advance">

                                {{ $row['advance'] > 0 ? number_format($row['advance'], 2) : '—' }}

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </x-data-table-card>

        </div>

    </x-reports.print-shell>



    <script type="application/json" id="outstanding-report-chart">@json($chart)</script>

</x-app-layout>
