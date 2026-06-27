<x-app-layout>

    <x-slot name="header">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

            <div class="min-w-0">

                <p class="truncate text-sm text-slate-500"><a href="{{ route('admin.vehicles.index') }}" class="hover:text-teal-700">Vehicles</a> / {{ $vehicle->vehicle_number }}</p>

                <h1 class="text-xl font-bold tracking-tight break-words text-slate-900 sm:text-2xl">{{ $vehicle->vehicle_number }}</h1>

                <p class="mt-1 text-sm text-slate-600">Vehicle profile, assigned driver, and purchase history.</p>

            </div>

            <a href="{{ route('admin.vehicles.index', ['edit' => $vehicle->id]) }}" class="inline-flex min-h-11 w-full shrink-0 items-center justify-center rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-teal-800 hover:shadow focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:ring-offset-1 active:scale-95 sm:w-auto">Edit Vehicle</a>

        </div>

    </x-slot>



    <div class="grid gap-4 sm:grid-cols-3">

        <div class="stat-card"><p class="text-xs uppercase text-slate-500">Purchase Total</p><p class="mt-2 text-xl font-bold break-words text-slate-900 sm:text-2xl">{{ number_format($stats['purchase_total'], 2) }} {{ $company->currency }}</p></div>

        <div class="stat-card"><p class="text-xs uppercase text-slate-500">Purchase Entries</p><p class="mt-2 text-xl font-bold text-slate-900 sm:text-2xl">{{ $stats['purchase_count'] }}</p></div>

        <div class="stat-card"><p class="text-xs uppercase text-slate-500">Total Quantity</p><p class="mt-2 text-xl font-bold break-words text-slate-900 sm:text-2xl">{{ number_format($stats['quantity_total'], 2) }} {{ $company->quantity_unit }}</p></div>

    </div>



    <div class="mt-6 grid gap-6 lg:grid-cols-2">

        <div class="profile-card">

            <h2 class="text-lg font-semibold text-slate-900">Vehicle Details</h2>

            <dl class="detail-list">

                <div class="detail-row"><dt>Vehicle Number</dt><dd class="font-medium">{{ $vehicle->vehicle_number }}</dd></div>

                <div class="detail-row"><dt>Registration</dt><dd>{{ $vehicle->registration_number ?: '—' }}</dd></div>

                <div class="detail-row"><dt>Type</dt><dd>{{ $vehicle->type ?: '—' }}</dd></div>

                <div class="detail-row"><dt>Status</dt><dd><span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $vehicle->status === \App\Enums\VehicleStatus::Active ? 'bg-teal-50 text-teal-700' : 'bg-slate-100 text-slate-600' }}">{{ $vehicle->status->label() }}</span></dd></div>

                <div class="detail-row"><dt>Assigned Driver</dt><dd>@if ($vehicle->driver)<a href="{{ route('admin.drivers.show', $vehicle->driver) }}" class="text-teal-700 hover:underline">{{ $vehicle->driver->name }}</a>@else — @endif</dd></div>

            </dl>

        </div>



        <div class="profile-card">

            <h2 class="text-lg font-semibold text-slate-900">Quick Links</h2>

            <div class="mt-4 space-y-2 text-sm">

                <a href="{{ route('purchases.index', ['vehicle_id' => $vehicle->id]) }}" class="block rounded-lg border border-slate-200 px-4 py-3 hover:bg-slate-50">View purchases for this vehicle</a>

                <a href="{{ route('reports.vehicle-wise') }}" class="block rounded-lg border border-slate-200 px-4 py-3 hover:bg-slate-50">Open vehicle-wise report</a>

            </div>

        </div>

    </div>



    <x-data-table-card class="mt-6" title="Recent Purchases">

        <thead>

            <tr>

                <th>Date</th>

                <th>Slip</th>

                <th>Pump</th>

                <th>Driver</th>

                <th class="text-right">Qty</th>

                <th class="text-right">Amount</th>

            </tr>

        </thead>

        <tbody>

            @forelse ($recentPurchases as $purchase)

                <tr>

                    <td data-label="Date">{{ $purchase->purchase_date->format('d M Y') }}</td>

                    <td class="col-primary font-mono" data-label="Slip"><a href="{{ route('purchases.index', ['edit' => $purchase->id]) }}" class="text-teal-700 hover:underline">{{ $purchase->slip_number }}</a></td>

                    <td data-label="Pump">@if ($purchase->pump)<a href="{{ route('admin.pumps.show', $purchase->pump) }}" class="text-teal-700 hover:underline">{{ $purchase->pump->name }}</a>@else — @endif</td>

                    <td data-label="Driver">{{ $purchase->driver?->name ?: '—' }}</td>

                    <td class="text-right" data-label="Qty">{{ number_format((float) $purchase->quantity, 2) }}</td>

                    <td class="text-right font-medium" data-label="Amount">{{ number_format((float) $purchase->amount, 2) }}</td>

                </tr>

            @empty

                <tr class="data-table-empty-row">

                    <td colspan="6" class="data-table-empty">No purchases recorded.</td>

                </tr>

            @endforelse

        </tbody>

    </x-data-table-card>

</x-app-layout>

