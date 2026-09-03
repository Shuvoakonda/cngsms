<x-app-layout>

    <x-slot name="header">

        <div>

            <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $reportTitle ?? 'Daily Purchase Report' }}</h1>

            <p class="mt-1 text-sm text-slate-600">{{ $reportDescription ?? 'Filter purchase slips by date, pump, and vehicle.' }}</p>

        </div>

    </x-slot>



    <x-reports.filter-card

        :action="($filters['fuel_type'] ?? null) === 'diesel' ? route('reports.diesel-purchases') : route('reports.daily-purchases')"

        :export="route('reports.daily-purchases.export', [...request()->query(), ...(($filters['fuel_type'] ?? null) === 'diesel' ? ['fuel_type' => 'diesel'] : [])])"

        :pdf="route('reports.daily-purchases.pdf', [...request()->query(), ...(($filters['fuel_type'] ?? null) === 'diesel' ? ['fuel_type' => 'diesel'] : [])])"

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

        @if (($filters['fuel_type'] ?? null) !== 'diesel')
        <div class="form-field">

            <x-input-label for="fuel_type" value="Fuel Type" />

            <x-select-input id="fuel_type" name="fuel_type">

                <option value="">All fuel types</option>
                <option value="cng" @selected(($filters['fuel_type'] ?? '') == 'cng')>CNG</option>
                <option value="diesel" @selected(($filters['fuel_type'] ?? '') == 'diesel')>Diesel</option>

            </x-select-input>

        </div>
        @endif

        <div class="form-field">

            <x-input-label for="status" value="Sale Status" />

            <x-select-input id="status" name="status">

                <option value="">All status</option>
                <option value="unsold" @selected(($filters['status'] ?? '') == 'unsold')>Unsold</option>
                <option value="sold" @selected(($filters['status'] ?? '') == 'sold')>Sold</option>

            </x-select-input>

        </div>

    </x-reports.filter-card>



    <div class="report-screen-only mb-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">

        <div class="report-summary-card report-summary-card-total sm:col-span-2">
            <div>
                <p class="report-summary-label">Total amount</p>
                <p class="report-summary-value report-summary-value-total">{{ number_format($totals['amount'], 2) }}</p>
                <p class="report-summary-note">{{ $company->currency }} across selected purchases</p>
            </div>
            <div class="report-summary-mark report-summary-mark-total">{{ strtoupper($company->currency) }}</div>
        </div>

        <div class="report-summary-card report-summary-card-teal">
            <p class="report-summary-label">{{ ($filters['fuel_type'] ?? null) === 'diesel' ? 'Diesel Quantity' : 'CNG Slips' }}</p>
            <p class="report-summary-value">{{ ($filters['fuel_type'] ?? null) === 'diesel' ? number_format($totals['quantity'], 2) : number_format($totals['count']) }}</p>
            <p class="report-summary-note">{{ ($filters['fuel_type'] ?? null) === 'diesel' ? 'Liter' : 'Diesel excluded from count' }}</p>
        </div>

        <div class="report-summary-card report-summary-card-amber">
                <p class="report-summary-label">{{ ($filters['fuel_type'] ?? null) === 'diesel' ? 'Diesel Amount' : 'Quantity' }}</p>
                <p class="report-summary-value">{{ ($filters['fuel_type'] ?? null) === 'diesel' ? number_format($totals['diesel_amount'], 2) : number_format($totals['quantity'], 2) }}</p>
            <p class="report-summary-note">{{ ($filters['fuel_type'] ?? null) === 'diesel' ? 'Liter' : $company->quantity_unit }}</p>
        </div>

        <div class="report-summary-card report-summary-card-violet">
            <p class="report-summary-label">Sold</p>
            <p class="report-summary-value">{{ number_format($totals['sold_count']) }}</p>
            <p class="report-summary-note">Completed sales</p>
        </div>

        <div class="report-summary-card report-summary-card-slate">
            <p class="report-summary-label">Unsold</p>
            <p class="report-summary-value">{{ number_format($totals['unsold_count']) }}</p>
            <p class="report-summary-note">Still available</p>
        </div>

        @if (($filters['fuel_type'] ?? null) !== 'diesel')
        <div class="report-summary-card report-summary-card-emerald">
            <p class="report-summary-label">CNG</p>
            <p class="report-summary-value">{{ number_format($totals['cng_amount'], 2) }}</p>
            <p class="report-summary-note">{{ $company->currency }}</p>
        </div>

        <div class="report-summary-card report-summary-card-orange">
            <p class="report-summary-label">Diesel</p>
            <p class="report-summary-value">{{ number_format($totals['diesel_amount'], 2) }}</p>
            <p class="report-summary-note">{{ $company->currency }}</p>
        </div>
        @endif

        <div class="report-summary-card report-summary-card-slate">
            <p class="report-summary-label">Discount</p>
            <p class="report-summary-value">{{ number_format($totals['discount'], 2) }}</p>
            <p class="report-summary-note">{{ $company->currency }} adjustment</p>
        </div>

        <div class="report-summary-card report-summary-card-slate">
            <p class="report-summary-label">Bonus</p>
            <p class="report-summary-value">{{ number_format($totals['bonus'], 2) }}</p>
            <p class="report-summary-note">{{ $company->currency }} adjustment</p>
        </div>

    </div>



    <x-reports.print-shell

        :title="$reportTitle ?? 'Daily Purchase Report'"

        :meta="collect([

            ($filters['date_from'] ?? null) || ($filters['date_to'] ?? null) ? 'Period: '.($filters['date_from'] ?? 'Start').' to '.($filters['date_to'] ?? 'Today') : null,

        ])->filter()->implode(' | ')"

        :summary="'Total amount: '.number_format($totals['amount'], 2).' '.$company->currency"

    >

        <x-data-table-card class="report-print-body">

        <thead>

            <tr>

                <th>Date</th>

                <th>Slip</th>

                <th>Fuel</th>

                <th>Status</th>

                <th>Pump</th>

                <th>Vehicle</th>

                <th>Driver</th>

                <th class="text-right">Qty</th>

                <th class="text-right">Rate</th>

                <th class="text-right">Discount</th>

                <th class="text-right">Bonus</th>

                <th class="text-right">Amount</th>

            </tr>

        </thead>

        <tbody>

            @forelse ($rows as $row)

                <tr>

                    <td data-label="Date">{{ $row->purchase_date->format('d M Y') }}</td>

                    <td class="col-primary font-mono" data-label="Slip">{{ $row->slip_number }}</td>

                    <td data-label="Fuel">
                        <span @class([
                            'inline-flex rounded-full px-2 py-0.5 text-xs font-medium',
                            'bg-emerald-50 text-emerald-700' => $row->fuel_type?->value === 'cng',
                            'bg-amber-50 text-amber-700' => $row->fuel_type?->value === 'diesel',
                        ])>
                            {{ strtoupper($row->fuel_type?->value ?? 'cng') }}
                        </span>
                    </td>

                    <td data-label="Status">
                        <span @class([
                            'inline-flex rounded-full px-2 py-0.5 text-xs font-medium',
                            'bg-violet-50 text-violet-700' => $row->status?->value === 'sold',
                            'bg-slate-100 text-slate-700' => $row->status?->value !== 'sold',
                        ])>
                            {{ ucfirst($row->status?->value ?? 'unsold') }}
                        </span>
                    </td>

                    <td data-label="Pump">{{ $row->pump?->name }}</td>

                    <td data-label="Vehicle">{{ $row->displayVehicle() }}</td>

                    <td data-label="Driver">{{ $row->displayDriver() }}</td>

                    <td class="text-right" data-label="Qty">{{ number_format((float) $row->quantity, 2) }}</td>

                    <td class="text-right" data-label="Rate">{{ number_format((float) $row->rate, 2) }}</td>

                    <td class="text-right" data-label="Discount">{{ $row->displayDiscount() }}</td>

                    <td class="text-right" data-label="Bonus">{{ $row->displayBonus() }}</td>

                    <td class="text-right font-medium" data-label="Amount">{{ number_format((float) $row->amount, 2) }}</td>

                </tr>

            @empty

                <tr class="data-table-empty-row">

                    <td colspan="12" class="data-table-empty">No records for selected filters.</td>

                </tr>

            @endforelse

        </tbody>

    </x-data-table-card>

    </x-reports.print-shell>

</x-app-layout>

