<x-app-layout>

    <x-slot name="header">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

            <div class="min-w-0">

                <p class="truncate text-sm text-slate-500"><a href="{{ route('admin.drivers.index') }}" class="hover:text-teal-700">Drivers</a> / {{ $driver->name }}</p>

                <h1 class="text-xl font-bold tracking-tight break-words text-slate-900 sm:text-2xl">{{ $driver->name }}</h1>

                <p class="mt-1 text-sm text-slate-600">Driver profile, assigned vehicles, and purchase history.</p>

            </div>

            <a href="{{ route('admin.drivers.index', ['edit' => $driver->id]) }}" class="inline-flex min-h-11 w-full shrink-0 items-center justify-center rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-teal-800 hover:shadow focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:ring-offset-1 active:scale-95 sm:w-auto">Edit Driver</a>

        </div>

    </x-slot>



    <div class="grid gap-4 sm:grid-cols-3">

        <div class="stat-card"><p class="text-xs uppercase text-slate-500">Assigned Vehicles</p><p class="mt-2 text-xl font-bold text-slate-900 sm:text-2xl">{{ $driver->vehicles_count }}</p></div>

        <div class="stat-card"><p class="text-xs uppercase text-slate-500">Purchase Total</p><p class="mt-2 text-xl font-bold break-words text-slate-900 sm:text-2xl">{{ number_format($stats['purchase_total'], 2) }} {{ $company->currency }}</p></div>

        <div class="stat-card"><p class="text-xs uppercase text-slate-500">Total Quantity</p><p class="mt-2 text-xl font-bold break-words text-slate-900 sm:text-2xl">{{ number_format($stats['quantity_total'], 2) }} {{ $company->quantity_unit }}</p></div>

    </div>



    <div class="mt-6 grid gap-6 lg:grid-cols-2">

        <div class="profile-card">

            <h2 class="text-lg font-semibold text-slate-900">Driver Details</h2>

            <dl class="detail-list">

                <div class="detail-row"><dt>Mobile</dt><dd>{{ $driver->mobile ?: '—' }}</dd></div>

                <div class="detail-row"><dt>License Number</dt><dd>{{ $driver->license_number ?: '—' }}</dd></div>

                <div class="detail-row"><dt>Address</dt><dd>{{ $driver->address ?: '—' }}</dd></div>

            </dl>

        </div>



        <div class="data-table-card">

            <div class="border-b border-slate-200 px-4 py-4 lg:px-5">

                <h2 class="font-semibold text-slate-900">Assigned Vehicles</h2>

            </div>

            <div class="divide-y divide-slate-100">

                @forelse ($vehicles as $vehicle)

                    <a href="{{ route('admin.vehicles.show', $vehicle) }}" class="flex items-center justify-between gap-3 px-4 py-3 text-sm hover:bg-slate-50 lg:px-5">

                        <span class="min-w-0 truncate font-medium text-slate-900">{{ $vehicle->vehicle_number }}</span>

                        <span class="shrink-0 text-teal-700">View →</span>

                    </a>

                @empty

                    <p class="px-4 py-8 text-center text-sm text-slate-500 lg:px-5">No vehicles assigned.</p>

                @endforelse

            </div>

        </div>

    </div>



    <x-data-table-card class="mt-6" title="Recent Purchases">

        <thead>

            <tr>

                <th>Date</th>

                <th>Slip</th>

                <th>Pump</th>

                <th>Vehicle</th>

                <th class="text-right">Amount</th>

            </tr>

        </thead>

        <tbody>

            @forelse ($recentPurchases as $purchase)

                <tr>

                    <td data-label="Date">{{ $purchase->purchase_date->format('d M Y') }}</td>

                    <td class="col-primary font-mono" data-label="Slip"><a href="{{ route('purchases.index', ['edit' => $purchase->id]) }}" class="text-teal-700 hover:underline">{{ $purchase->slip_number }}</a></td>

                    <td data-label="Pump">@if ($purchase->pump)<a href="{{ route('admin.pumps.show', $purchase->pump) }}" class="text-teal-700 hover:underline">{{ $purchase->pump->name }}</a>@else — @endif</td>

                    <td data-label="Vehicle">@if ($purchase->vehicle)<a href="{{ route('admin.vehicles.show', $purchase->vehicle) }}" class="text-teal-700 hover:underline">{{ $purchase->vehicle->vehicle_number }}</a>@else — @endif</td>

                    <td class="text-right font-medium" data-label="Amount">{{ number_format((float) $purchase->amount, 2) }}</td>

                </tr>

            @empty

                <tr class="data-table-empty-row">

                    <td colspan="5" class="data-table-empty">No purchases recorded.</td>

                </tr>

            @endforelse

        </tbody>

    </x-data-table-card>

</x-app-layout>

