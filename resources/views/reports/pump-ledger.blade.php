<x-app-layout>

    <x-slot name="header">

        <div><h1 class="text-2xl font-bold text-slate-900">Pump Ledger</h1><p class="mt-1 text-sm text-slate-600">Running debit/credit statement for a pump.</p></div>

    </x-slot>



    <x-reports.print-header

        title="Pump Ledger"

        :meta="($report['pump']?->name ?? 'All pumps').' | Closing: '.number_format($report['closing_balance'], 2).' '.$company->currency"

    />



    <x-reports.filter-card

        :action="route('reports.pump-ledger')"

        :export="route('reports.pump-ledger.export', request()->query())"

    >

        <div class="form-field">

            <x-input-label for="pump_id" value="Pump" />

            <x-select-input id="pump_id" name="pump_id" required>

                @foreach ($pumps as $pump)

                    <option value="{{ $pump->id }}" @selected(($filters['pump_id'] ?? '') == $pump->id)>{{ $pump->name }}</option>

                @endforeach

            </x-select-input>

        </div>

        <div class="form-field">

            <x-input-label for="date_from" value="From" />

            <x-text-input id="date_from" name="date_from" type="date" :value="$filters['date_from'] ?? ''" />

        </div>

        <div class="form-field">

            <x-input-label for="date_to" value="To" />

            <x-text-input id="date_to" name="date_to" type="date" :value="$filters['date_to'] ?? ''" />

        </div>

    </x-reports.filter-card>



    @if ($report['pump'])

        <div class="report-screen-only mb-4 rounded-2xl bg-white p-4 ring-1 ring-slate-200">

            <p class="font-semibold text-slate-900">{{ $report['pump']->name }}</p>

            <p class="mt-1 text-sm text-slate-600">Closing balance: <strong>{{ number_format($report['closing_balance'], 2) }}</strong> {{ $company->currency }}</p>

        </div>

    @endif



    <x-data-table-card class="report-print-body">

        <thead>

            <tr>

                <th>Date</th>

                <th>Reference</th>

                <th>Description</th>

                <th class="text-right">Debit</th>

                <th class="text-right">Credit</th>

                <th class="text-right">Balance</th>

            </tr>

        </thead>

        <tbody>

            @foreach ($report['entries'] as $entry)

                <tr>

                    <td data-label="Date">{{ $entry['date'] ?? '—' }}</td>

                    <td class="col-primary font-mono" data-label="Reference">{{ $entry['reference'] }}</td>

                    <td data-label="Description">{{ $entry['description'] }}</td>

                    <td class="text-right" data-label="Debit">{{ $entry['debit'] ? number_format($entry['debit'], 2) : '—' }}</td>

                    <td class="text-right" data-label="Credit">{{ $entry['credit'] ? number_format($entry['credit'], 2) : '—' }}</td>

                    <td class="text-right font-medium" data-label="Balance">{{ number_format($entry['balance'], 2) }}</td>

                </tr>

            @endforeach

        </tbody>

    </x-data-table-card>



    <x-reports.print-footer :summary="'Closing balance: '.number_format($report['closing_balance'], 2).' '.$company->currency" />

</x-app-layout>

