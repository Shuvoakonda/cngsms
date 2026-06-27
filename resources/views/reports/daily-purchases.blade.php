<x-app-layout>

    <x-slot name="header">

        <div>

            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Daily Purchase Report</h1>

            <p class="mt-1 text-sm text-slate-600">Filter purchase slips by date, pump, and vehicle.</p>

        </div>

    </x-slot>



    <x-reports.print-header

        title="Daily Purchase Report"

        :meta="collect([

            ($filters['date_from'] ?? null) || ($filters['date_to'] ?? null) ? 'Period: '.($filters['date_from'] ?? 'Start').' to '.($filters['date_to'] ?? 'Today') : null,

        ])->filter()->implode(' | ')"

    />



    <x-reports.filter-card

        :action="route('reports.daily-purchases')"

        :export="route('reports.daily-purchases.export', request()->query())"

    >

        <div class="form-field">

            <x-input-label for="date_from" value="From" />

            <x-text-input id="date_from" name="date_from" type="date" :value="$filters['date_from'] ?? ''" />

        </div>

        <div class="form-field">

            <x-input-label for="date_to" value="To" />

            <x-text-input id="date_to" name="date_to" type="date" :value="$filters['date_to'] ?? ''" />

        </div>

        <div class="form-field">

            <x-input-label for="pump_id" value="Pump" />

            <x-select-input id="pump_id" name="pump_id">

                <option value="">All pumps</option>

                @foreach ($pumps as $pump)

                    <option value="{{ $pump->id }}" @selected(($filters['pump_id'] ?? '') == $pump->id)>{{ $pump->name }}</option>

                @endforeach

            </x-select-input>

        </div>

        <div class="form-field">

            <x-input-label for="vehicle_id" value="Vehicle" />

            <x-select-input id="vehicle_id" name="vehicle_id">

                <option value="">All vehicles</option>

                <option value="guest" @selected(($filters['vehicle_id'] ?? '') == 'guest')>Guest vehicles</option>

                @foreach ($vehicles as $vehicle)

                    <option value="{{ $vehicle->id }}" @selected(($filters['vehicle_id'] ?? '') == $vehicle->id)>{{ $vehicle->vehicle_number }}</option>

                @endforeach

            </x-select-input>

        </div>

    </x-reports.filter-card>



    <div class="report-screen-only mb-4 grid gap-4 sm:grid-cols-3">

        <div class="rounded-2xl bg-white p-4 ring-1 ring-slate-200"><p class="text-xs text-slate-500">Entries</p><p class="text-2xl font-bold">{{ $totals['count'] }}</p></div>

        <div class="rounded-2xl bg-white p-4 ring-1 ring-slate-200"><p class="text-xs text-slate-500">Quantity</p><p class="text-2xl font-bold">{{ number_format($totals['quantity'], 2) }}</p></div>

        <div class="rounded-2xl bg-white p-4 ring-1 ring-slate-200"><p class="text-xs text-slate-500">Amount</p><p class="text-2xl font-bold">{{ number_format($totals['amount'], 2) }}</p></div>

    </div>



    <x-data-table-card class="report-print-body">

        <thead>

            <tr>

                <th>Date</th>

                <th>Slip</th>

                <th>Pump</th>

                <th>Vehicle</th>

                <th>Driver</th>

                <th class="text-right">Qty</th>

                <th class="text-right">Amount</th>

            </tr>

        </thead>

        <tbody>

            @forelse ($rows as $row)

                <tr>

                    <td data-label="Date">{{ $row->purchase_date->format('d M Y') }}</td>

                    <td class="col-primary font-mono" data-label="Slip">{{ $row->slip_number }}</td>

                    <td data-label="Pump">{{ $row->pump?->name }}</td>

                    <td data-label="Vehicle">{{ $row->displayVehicle() }}</td>

                    <td data-label="Driver">{{ $row->displayDriver() }}</td>

                    <td class="text-right" data-label="Qty">{{ number_format((float) $row->quantity, 2) }}</td>

                    <td class="text-right font-medium" data-label="Amount">{{ number_format((float) $row->amount, 2) }}</td>

                </tr>

            @empty

                <tr class="data-table-empty-row">

                    <td colspan="7" class="data-table-empty">No records for selected filters.</td>

                </tr>

            @endforelse

        </tbody>

    </x-data-table-card>



    <x-reports.print-footer :summary="'Total amount: '.number_format($totals['amount'], 2).' '.$company->currency" />

</x-app-layout>

