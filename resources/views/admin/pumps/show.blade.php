<x-app-layout>

    <x-slot name="header">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

            <div class="min-w-0">

                <p class="truncate text-sm text-slate-500"><a href="{{ route('admin.pumps.index') }}" class="hover:text-teal-700">Pumps</a> / {{ $pump->name }}</p>

                <h1 class="text-xl font-bold tracking-tight break-words text-slate-900 sm:text-2xl">{{ $pump->name }}</h1>

                <p class="mt-1 text-sm text-slate-600">Complete pump profile, ledger summary, and recent activity.</p>

            </div>

            <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:flex-wrap">
                <a href="{{ route('reports.pump-ledger', ['pump_id' => $pump->id]) }}" class="inline-flex min-h-11 w-full items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-800 shadow-sm transition-all duration-200 hover:bg-slate-50 hover:border-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:ring-offset-1 active:scale-95 sm:w-auto">Pump Ledger</a>
                <a href="{{ route('admin.pumps.index', ['edit' => $pump->id]) }}" class="inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-teal-800 hover:shadow focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:ring-offset-1 active:scale-95 sm:w-auto">Edit Pump</a>
            </div>

        </div>

    </x-slot>



    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <div class="stat-card">

            <p class="text-xs uppercase text-slate-500">{{ $outstanding < 0 ? 'Advance Balance' : 'Outstanding' }}</p>

            <p @class(['mt-2 text-xl font-bold break-words sm:text-2xl', 'text-violet-700' => $outstanding < 0, 'text-rose-700' => $outstanding > 0 && $pump->isOverCreditLimit(), 'text-slate-900' => $outstanding >= 0 && ! $pump->isOverCreditLimit()])>{{ number_format(abs($outstanding), 2) }} {{ $company->currency }}</p>

        </div>

        <div class="stat-card">

            <p class="text-xs uppercase text-slate-500">Total Purchases</p>

            <p class="mt-2 text-xl font-bold text-slate-900 sm:text-2xl">{{ number_format($stats['purchase_total'], 2) }}</p>

            <p class="text-xs text-slate-500">{{ $stats['purchase_count'] }} entries</p>

        </div>

        <div class="stat-card">

            <p class="text-xs uppercase text-slate-500">Total Payments</p>

            <p class="mt-2 text-xl font-bold text-slate-900 sm:text-2xl">{{ number_format($stats['payment_total'], 2) }}</p>

            <p class="text-xs text-slate-500">{{ $stats['payment_count'] }} entries</p>

        </div>

        <div class="stat-card">

            <p class="text-xs uppercase text-slate-500">Credit Limit</p>

            <p class="mt-2 text-xl font-bold text-slate-900 sm:text-2xl">{{ number_format((float) $pump->credit_limit, 2) }}</p>

        </div>

    </div>



    <div class="mt-6 grid gap-6 lg:grid-cols-2">

        <div class="profile-card">

            <h2 class="text-lg font-semibold text-slate-900">Pump Details</h2>

            <dl class="detail-list">

                <div class="detail-row"><dt>Status</dt><dd><span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $pump->status === \App\Enums\PumpStatus::Active ? 'bg-teal-50 text-teal-700' : 'bg-slate-100 text-slate-600' }}">{{ $pump->status->label() }}</span></dd></div>

                <div class="detail-row"><dt>Address</dt><dd>{{ $pump->address ?: '—' }}</dd></div>

                <div class="detail-row"><dt>Contact Person</dt><dd>{{ $pump->contact_person ?: '—' }}</dd></div>

                <div class="detail-row"><dt>Mobile</dt><dd>{{ $pump->mobile ?: '—' }}</dd></div>

                <div class="detail-row"><dt>Opening Due</dt><dd>{{ number_format((float) $pump->opening_balance, 2) }} {{ $company->currency }}</dd></div>

                <div class="detail-row"><dt>Opening Advance</dt><dd>{{ number_format((float) $pump->opening_advance, 2) }} {{ $company->currency }}</dd></div>

                <div class="detail-row"><dt>Created</dt><dd>{{ $pump->created_at?->format('d M Y') }}</dd></div>

            </dl>

        </div>



        <x-data-table-card title="Recent Payments">

            <thead>

                <tr>

                    <th>Date</th>

                    <th>Type</th>

                    <th>Voucher</th>

                    <th class="text-right">Amount</th>

                </tr>

            </thead>

            <tbody>

                @forelse ($recentPayments as $payment)

                    <tr>

                        <td data-label="Date">{{ $payment->payment_date->format('d M Y') }}</td>

                        <td data-label="Type">
                            <span @class([
                                'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium',
                                'bg-violet-50 text-violet-700 ring-1 ring-violet-100' => $payment->type === \App\Enums\PaymentType::Advance,
                                'bg-teal-50 text-teal-700 ring-1 ring-teal-100' => $payment->type === \App\Enums\PaymentType::Payment,
                            ])>{{ $payment->type->label() }}</span>
                        </td>

                        <td class="col-primary font-mono" data-label="Voucher"><a href="{{ route('payments.index', ['edit' => $payment->id]) }}" class="text-teal-700 hover:underline">{{ $payment->voucher_number }}</a></td>

                        <td class="text-right font-medium" data-label="Amount">{{ number_format((float) $payment->amount, 2) }}</td>

                    </tr>

                @empty

                    <tr class="data-table-empty-row">

                        <td colspan="4" class="data-table-empty">No payments recorded.</td>

                    </tr>

                @endforelse

            </tbody>

        </x-data-table-card>

    </div>



    <x-data-table-card class="mt-6" title="Recent Purchases">

        <thead>

            <tr>

                <th>Date</th>

                <th>Slip</th>

                <th>Vehicle</th>

                <th>Driver</th>

                <th class="text-right">Amount</th>

            </tr>

        </thead>

        <tbody>

            @forelse ($recentPurchases as $purchase)

                <tr>

                    <td data-label="Date">{{ $purchase->purchase_date->format('d M Y') }}</td>

                    <td class="col-primary font-mono" data-label="Slip"><a href="{{ route('purchases.index', ['edit' => $purchase->id]) }}" class="text-teal-700 hover:underline">{{ $purchase->slip_number }}</a></td>

                    <td data-label="Vehicle">@if ($purchase->vehicle)<a href="{{ route('admin.vehicles.show', $purchase->vehicle) }}" class="text-teal-700 hover:underline">{{ $purchase->vehicle->vehicle_number }}</a>@else — @endif</td>

                    <td data-label="Driver">@if ($purchase->driver)<a href="{{ route('admin.drivers.show', $purchase->driver) }}" class="text-teal-700 hover:underline">{{ $purchase->driver->name }}</a>@else — @endif</td>

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

