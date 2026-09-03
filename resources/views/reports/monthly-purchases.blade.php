<x-app-layout>

    <x-slot name="header">

        <div><h1 class="text-2xl font-bold text-slate-900">Monthly Purchase Summary</h1><p class="mt-1 text-sm text-slate-600">Totals by pump, vehicle, and driver entries per pump for the selected month.</p></div>

    </x-slot>



    <x-reports.filter-card

        :action="route('reports.monthly-purchases')"

        :export="route('reports.monthly-purchases.export', request()->query())"

        :pdf="route('reports.monthly-purchases.pdf', request()->query())"

    >

        <div class="form-field">

            <x-input-label for="month" value="Month" />

            <x-text-input id="month" name="month" type="month" :value="$filters['month']" />

        </div>

        <div class="form-field">

            <x-input-label for="status" value="Sale Status" />

            <x-select-input id="status" name="status">

                <option value="">All status</option>
                <option value="unsold" @selected(($filters['status'] ?? '') == 'unsold')>Unsold</option>
                <option value="sold" @selected(($filters['status'] ?? '') == 'sold')>Sold</option>

            </x-select-input>

        </div>

        <div class="form-field">

            <x-input-label for="fuel_type" value="Fuel Type" />

            <x-select-input id="fuel_type" name="fuel_type">

                <option value="">All fuel types</option>
                <option value="cng" @selected(($filters['fuel_type'] ?? '') == 'cng')>CNG</option>
                <option value="diesel" @selected(($filters['fuel_type'] ?? '') == 'diesel')>Diesel</option>

            </x-select-input>

        </div>

    </x-reports.filter-card>



    <div class="report-screen-only mb-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-6 rounded-2xl bg-white p-4 ring-1 ring-slate-200">

        <div><p class="text-xs text-slate-500">Entries</p><p class="text-2xl font-bold">{{ $report['totals']['count'] }}</p></div>
        <div><p class="text-xs text-slate-500">Sold</p><p class="text-2xl font-bold">{{ $report['totals']['sold_count'] }}</p></div>
        <div><p class="text-xs text-slate-500">Unsold</p><p class="text-2xl font-bold">{{ $report['totals']['unsold_count'] }}</p></div>
        <div><p class="text-xs text-slate-500">Quantity</p><p class="text-2xl font-bold">{{ number_format($report['totals']['quantity'], 2) }}</p></div>
        <div><p class="text-xs text-slate-500">Discount</p><p class="text-2xl font-bold">{{ number_format($report['totals']['discount'], 2) }}</p></div>
        <div><p class="text-xs text-slate-500">Bonus</p><p class="text-2xl font-bold">{{ number_format($report['totals']['bonus'], 2) }}</p></div>
        <div><p class="text-xs text-slate-500">CNG</p><p class="text-2xl font-bold">{{ number_format($report['totals']['cng_amount'], 2) }}</p></div>
        <div><p class="text-xs text-slate-500">Diesel</p><p class="text-2xl font-bold">{{ number_format($report['totals']['diesel_amount'], 2) }}</p></div>

    </div>



    <x-reports.print-shell title="Monthly Purchase Summary" :meta="collect(['Month: '.$filters['month'], !empty($filters['fuel_type']) ? 'Fuel: '.strtoupper($filters['fuel_type']) : null, !empty($filters['status']) ? 'Status: '.ucfirst($filters['status']) : null])->filter()->implode(' | ')" :summary="'Total amount: '.number_format($report['totals']['amount'], 2).' '.$company->currency.' | CNG: '.number_format($report['totals']['cng_amount'], 2).' | Diesel: '.number_format($report['totals']['diesel_amount'], 2).' '.$company->currency">

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

                            <th class="text-right">Rate</th>

                            <th class="text-right">Discount</th>

                            <th class="text-right">Bonus</th>

                            <th class="text-right">Amount</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse ($report['byPump'] as $row)

                            <tr>

                                <td class="col-primary" data-label="Pump">{{ $row['label'] }}</td>

                                <td class="text-right" data-label="Entries">{{ $row['count'] }}</td>

                                <td class="text-right" data-label="Qty">{{ number_format($row['quantity'], 2) }}</td>

                                <td class="text-right" data-label="Rate">{{ number_format($row['rate'], 2) }}</td>

                                <td class="text-right" data-label="Discount">{{ number_format($row['discount'], 2) }}</td>

                                <td class="text-right" data-label="Bonus">{{ number_format($row['bonus'], 2) }}</td>

                                <td class="text-right font-medium" data-label="Amount">{{ number_format($row['amount'], 2) }}</td>

                            </tr>

                        @empty

                            <tr class="data-table-empty-row"><td colspan="7" class="data-table-empty">No data.</td></tr>

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

                            <th class="text-right">Rate</th>

                            <th class="text-right">Discount</th>

                            <th class="text-right">Bonus</th>

                            <th class="text-right">Amount</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse ($report['byVehicle'] as $row)

                            <tr>

                                <td class="col-primary" data-label="Vehicle">{{ $row['label'] }}</td>

                                <td class="text-right" data-label="Entries">{{ $row['count'] }}</td>

                                <td class="text-right" data-label="Qty">{{ number_format($row['quantity'], 2) }}</td>

                                <td class="text-right" data-label="Rate">{{ number_format($row['rate'], 2) }}</td>

                                <td class="text-right" data-label="Discount">{{ number_format($row['discount'], 2) }}</td>

                                <td class="text-right" data-label="Bonus">{{ number_format($row['bonus'], 2) }}</td>

                                <td class="text-right font-medium" data-label="Amount">{{ number_format($row['amount'], 2) }}</td>

                            </tr>

                        @empty

                            <tr class="data-table-empty-row"><td colspan="7" class="data-table-empty">No data.</td></tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


        </div>

    </div>



    <x-reports.partials.driver-entries-by-pump :rows="$report['byPumpDriver']" class="report-print-body mt-6" />

    </x-reports.print-shell>

</x-app-layout>

