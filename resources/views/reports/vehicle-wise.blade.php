<x-app-layout>

    <x-slot name="header">

        <div><h1 class="text-2xl font-bold text-slate-900">Vehicle-wise Report</h1><p class="mt-1 text-sm text-slate-600">Purchase count, quantity, and amount per vehicle.</p></div>

    </x-slot>



    <x-reports.filter-card

        :action="route('reports.vehicle-wise')"

        :export="route('reports.vehicle-wise.export', request()->query())"

    >

        <div class="form-field"><x-input-label for="date_from" value="From" /><x-text-input id="date_from" name="date_from" type="date" :value="$filters['date_from'] ?? ''" /></div>

        <div class="form-field"><x-input-label for="date_to" value="To" /><x-text-input id="date_to" name="date_to" type="date" :value="$filters['date_to'] ?? ''" /></div>

    </x-reports.filter-card>



    <x-reports.print-shell title="Vehicle-wise Purchase Report" :summary="'Total amount: '.number_format($rows->sum('amount'), 2).' '.$company->currency">

        <x-data-table-card class="report-print-body">

        <thead>

            <tr>

                <th>Vehicle</th>

                <th class="text-right">Entries</th>

                <th class="text-right">Quantity</th>

                <th class="text-right">Amount</th>

            </tr>

        </thead>

        <tbody>

            @forelse ($rows as $row)

                <tr>

                    <td class="col-primary font-medium" data-label="Vehicle">{{ $row['vehicle'] }}</td>

                    <td class="text-right" data-label="Entries">{{ $row['count'] }}</td>

                    <td class="text-right" data-label="Quantity">{{ number_format($row['quantity'], 2) }}</td>

                    <td class="text-right font-semibold" data-label="Amount">{{ number_format($row['amount'], 2) }}</td>

                </tr>

            @empty

                <tr class="data-table-empty-row"><td colspan="4" class="data-table-empty">No data.</td></tr>

            @endforelse

        </tbody>

    </x-data-table-card>



        <x-reports.partials.driver-entries-by-pump :rows="$driverByPump" class="report-print-body mt-6" />

    </x-reports.print-shell>

</x-app-layout>

