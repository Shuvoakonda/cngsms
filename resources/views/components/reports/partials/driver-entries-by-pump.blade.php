@props(['rows'])

<div {{ $attributes->merge(['class' => 'data-table-card']) }}>
    <div class="border-b border-slate-200 px-5 py-4">
        <h2 class="font-semibold text-slate-900">Driver Entries by Pump</h2>
        <p class="mt-1 text-sm text-slate-600">How many purchase entries each driver made at each pump.</p>
    </div>

    <div class="data-table-scroll">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Pump</th>
                    <th>Driver</th>
                    <th class="text-right">Entries</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td class="col-primary font-medium" data-label="Pump">{{ $row['pump'] }}</td>
                        <td data-label="Driver">{{ $row['driver'] }}</td>
                        <td class="text-right font-medium" data-label="Entries">{{ $row['count'] }}</td>
                        <td class="text-right" data-label="Qty">{{ number_format($row['quantity'], 2) }}</td>
                        <td class="text-right font-medium" data-label="Amount">{{ number_format($row['amount'], 2) }}</td>
                    </tr>
                @empty
                    <tr class="data-table-empty-row">
                        <td colspan="5" class="data-table-empty">No data for selected filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
