<x-app-layout>

    <x-slot name="header">

        <div><h1 class="text-2xl font-bold text-slate-900">Monthly Purchase Summary</h1><p class="mt-1 text-sm text-slate-600">Totals by pump, vehicle, and driver entries per pump for the selected month.</p></div>

    </x-slot>



    <x-reports.filter-card

        :action="route('reports.monthly-purchases')"

        :export="route('reports.monthly-purchases.export', request()->query())"

    >

        <div class="form-field">

            <x-input-label for="month" value="Month" />

            <x-text-input id="month" name="month" type="month" :value="$filters['month']" />

        </div>

    </x-reports.filter-card>



    <div class="report-screen-only mb-4 rounded-2xl bg-white p-4 ring-1 ring-slate-200">

        <p class="text-sm text-slate-600">Total: <strong>{{ $report['totals']['count'] }}</strong> entries · <strong>{{ number_format($report['totals']['quantity'], 2) }}</strong> qty · <strong>{{ number_format($report['totals']['amount'], 2) }}</strong> {{ $company->currency }}</p>

    </div>



    <x-reports.print-shell title="Monthly Purchase Summary" :meta="'Month: '.$filters['month']" :summary="'Total amount: '.number_format($report['totals']['amount'], 2).' '.$company->currency">

        <div class="report-print-body grid gap-6 lg:grid-cols-2">

        <div class="data-table-card">

            <div class="border-b border-slate-200 px-5 py-4"><h2 class="font-semibold">By Pump</h2></div>

            <div class="data-table-scroll">

                <table class="data-table">

                    <thead>

                        <tr>

                            <th>Pump</th>

                            <th class="text-right">Entries</th>

                            <th class="text-right">Qty</th>

                            <th class="text-right">Amount</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse ($report['byPump'] as $row)

                            <tr>

                                <td class="col-primary" data-label="Pump">{{ $row['label'] }}</td>

                                <td class="text-right" data-label="Entries">{{ $row['count'] }}</td>

                                <td class="text-right" data-label="Qty">{{ number_format($row['quantity'], 2) }}</td>

                                <td class="text-right font-medium" data-label="Amount">{{ number_format($row['amount'], 2) }}</td>

                            </tr>

                        @empty

                            <tr class="data-table-empty-row"><td colspan="4" class="data-table-empty">No data.</td></tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

        <div class="data-table-card">

            <div class="border-b border-slate-200 px-5 py-4"><h2 class="font-semibold">By Vehicle</h2></div>

            <div class="data-table-scroll">

                <table class="data-table">

                    <thead>

                        <tr>

                            <th>Vehicle</th>

                            <th class="text-right">Entries</th>

                            <th class="text-right">Qty</th>

                            <th class="text-right">Amount</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse ($report['byVehicle'] as $row)

                            <tr>

                                <td class="col-primary" data-label="Vehicle">{{ $row['label'] }}</td>

                                <td class="text-right" data-label="Entries">{{ $row['count'] }}</td>

                                <td class="text-right" data-label="Qty">{{ number_format($row['quantity'], 2) }}</td>

                                <td class="text-right font-medium" data-label="Amount">{{ number_format($row['amount'], 2) }}</td>

                            </tr>

                        @empty

                            <tr class="data-table-empty-row"><td colspan="4" class="data-table-empty">No data.</td></tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>



    <x-reports.partials.driver-entries-by-pump :rows="$report['byPumpDriver']" class="report-print-body mt-6" />

    </x-reports.print-shell>

</x-app-layout>

