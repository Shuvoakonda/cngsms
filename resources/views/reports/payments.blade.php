<x-app-layout>

    <x-slot name="header">

        <div><h1 class="text-2xl font-bold text-slate-900">Payment Report</h1><p class="mt-1 text-sm text-slate-600">Payments by date and pump.</p></div>

    </x-slot>



    <x-reports.filter-card

        :action="route('reports.payments')"

        :export="route('reports.payments.export', request()->query())"

    >

        <div class="form-field"><x-input-label for="date_from" value="From" /><x-text-input id="date_from" name="date_from" type="date" :value="$filters['date_from'] ?? ''" /></div>

        <div class="form-field"><x-input-label for="date_to" value="To" /><x-text-input id="date_to" name="date_to" type="date" :value="$filters['date_to'] ?? ''" /></div>

        <div class="form-field">

            <x-input-label for="pump_id" value="Pump" />

            <x-select-input id="pump_id" name="pump_id">

                <option value="">All pumps</option>

                @foreach ($pumps as $pump)

                    <option value="{{ $pump->id }}" @selected(($filters['pump_id'] ?? '') == $pump->id)>{{ $pump->name }}</option>

                @endforeach

            </x-select-input>

        </div>

    </x-reports.filter-card>



    <div class="report-screen-only mb-4 rounded-2xl bg-white p-4 ring-1 ring-slate-200">

        <p class="text-sm text-slate-600">{{ $totals['count'] }} payments · Total <strong>{{ number_format($totals['amount'], 2) }}</strong> {{ $company->currency }}</p>

    </div>



    <x-reports.print-shell title="Payment Report" :summary="'Total amount: '.number_format($totals['amount'], 2).' '.$company->currency">

        <x-data-table-card class="report-print-body">

        <thead>

            <tr>

                <th>Date</th>

                <th>Type</th>

                <th>Voucher</th>

                <th>Pump</th>

                <th>Method</th>

                <th class="text-right">Amount</th>

            </tr>

        </thead>

        <tbody>

            @forelse ($rows as $row)

                <tr>

                    <td data-label="Date">{{ $row->payment_date->format('d M Y') }}</td>

                    <td data-label="Type">{{ $row->type->label() }}</td>

                    <td class="col-primary font-mono" data-label="Voucher">{{ $row->voucher_number }}</td>

                    <td data-label="Pump">{{ $row->pump?->name }}</td>

                    <td data-label="Method">{{ $row->payment_method->label() }}</td>

                    <td class="text-right font-medium" data-label="Amount">{{ number_format((float) $row->amount, 2) }}</td>

                </tr>

            @empty

                <tr class="data-table-empty-row">

                    <td colspan="6" class="data-table-empty">No records.</td>

                </tr>

            @endforelse

        </tbody>

    </x-data-table-card>

    </x-reports.print-shell>

</x-app-layout>

