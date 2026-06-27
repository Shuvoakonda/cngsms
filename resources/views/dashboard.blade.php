<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Dashboard</h1>
                <p class="mt-1 text-sm text-slate-600">
                    Welcome back, {{ auth()->user()->name }}. Here is your fleet purchase and payment overview.
                </p>
            </div>
            <p class="text-sm text-slate-500">{{ now()->format('l, d M Y') }}</p>
        </div>
    </x-slot>

    <div class="space-y-6 pb-8">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="stat-card group">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Today's Purchases</p>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-50 text-teal-600 transition-transform group-hover:scale-110 group-hover:bg-teal-100">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                    </div>
                </div>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($stats['today_purchase'], 2) }}</p>
                <p class="mt-1 text-xs text-slate-400">{{ $company->currency ?? 'BDT' }}</p>
            </div>
            <div class="stat-card group">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Today's Payments</p>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-50 text-teal-600 transition-transform group-hover:scale-110 group-hover:bg-teal-100">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    </div>
                </div>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($stats['today_payment'], 2) }}</p>
                <p class="mt-1 text-xs text-slate-400">{{ $company->currency ?? 'BDT' }}</p>
            </div>
            <div class="stat-card group">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">This Month</p>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-50 text-teal-600 transition-transform group-hover:scale-110 group-hover:bg-teal-100">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    </div>
                </div>
                <p class="mt-2 text-lg font-bold text-slate-900">
                    <span class="text-teal-800">{{ number_format($stats['monthly_purchase'], 2) }}</span>
                    <span class="mx-1 text-base font-normal text-slate-400">/</span>
                    <span class="text-slate-700">{{ number_format($stats['monthly_payment'], 2) }}</span>
                </p>
                <p class="mt-1 text-xs text-slate-400">Purchase / Payment</p>
            </div>
            <div @class([
                'stat-card group',
                'ring-rose-200 bg-rose-50' => $stats['outstanding_over_threshold'] || $stats['pumps_over_limit'] > 0,
            ])>
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Total Outstanding</p>
                    <div @class([
                        'flex h-10 w-10 items-center justify-center rounded-xl transition-transform group-hover:scale-110',
                        'bg-rose-100 text-rose-600 group-hover:bg-rose-200' => $stats['outstanding_over_threshold'] || $stats['pumps_over_limit'] > 0,
                        'bg-teal-50 text-teal-600 group-hover:bg-teal-100' => ! $stats['outstanding_over_threshold'] && $stats['pumps_over_limit'] === 0,
                    ])>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                </div>
                <p @class([
                    'mt-2 text-3xl font-bold',
                    'text-rose-700' => $stats['outstanding_over_threshold'] || $stats['pumps_over_limit'] > 0,
                    'text-teal-800' => ! $stats['outstanding_over_threshold'] && $stats['pumps_over_limit'] === 0,
                ])>{{ number_format($stats['total_outstanding'], 2) }}</p>
                <p class="mt-1 text-xs text-slate-400">
                    @if ($stats['pumps_over_limit'] > 0)
                        {{ $stats['pumps_over_limit'] }} pump{{ $stats['pumps_over_limit'] === 1 ? '' : 's' }} over credit limit
                    @else
                        Across all pumps
                    @endif
                </p>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <div class="data-table-card p-5">
                <h2 class="text-lg font-semibold text-slate-900">Monthly Purchase vs Payment</h2>
                <p class="mt-1 text-sm text-slate-500">Last 6 months comparison</p>
                <div class="mt-4 h-72">
                    <canvas id="monthlyComparisonChart"></canvas>
                </div>
            </div>

            <div class="data-table-card p-5">
                <h2 class="text-lg font-semibold text-slate-900">Outstanding Trend</h2>
                <p class="mt-1 text-sm text-slate-500">Running balance over the last 12 months</p>
                <div class="mt-4 h-72">
                    <canvas id="outstandingTrendChart"></canvas>
                </div>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="data-table-card p-5 xl:col-span-2">
                <h2 class="text-lg font-semibold text-slate-900">Pump-wise Outstanding</h2>
                <p class="mt-1 text-sm text-slate-500">Top pumps by current due amount</p>
                <div class="mt-4 h-80">
                    <canvas id="pumpOutstandingChart"></canvas>
                </div>
            </div>

            <div class="data-table-card p-5">
                <h2 class="text-lg font-semibold text-slate-900">Alerts</h2>
                <p class="mt-1 text-sm text-slate-500">Credit limit and risk notifications</p>
                <div class="mt-4 space-y-3">
                    @forelse ($alerts as $alert)
                        <div @class([
                            'rounded-xl border px-4 py-3 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-sm',
                            'border-rose-200 bg-rose-50 text-rose-900' => $alert['type'] === 'danger',
                            'border-amber-200 bg-amber-50 text-amber-900' => $alert['type'] === 'warning',
                            'border-teal-200 bg-teal-50 text-teal-900' => $alert['type'] === 'success',
                        ])>
                            <div class="flex items-start gap-3">
                                @if ($alert['type'] === 'danger')
                                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                @elseif ($alert['type'] === 'warning')
                                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                @else
                                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                @endif
                                <div>
                                    <p class="text-sm font-semibold">{{ $alert['title'] }}</p>
                                    <p class="mt-1 text-sm opacity-90">{{ $alert['message'] }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-600">
                            <svg class="h-5 w-5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            <p class="text-sm">No alerts right now.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <x-data-table-card class="lg:col-span-2" title="Recent Transactions">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Reference</th>
                        <th>Pump</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentTransactions as $transaction)
                        <tr>
                            <td data-label="Date">{{ \Carbon\Carbon::parse($transaction['date'])->format('d M Y') }}</td>
                            <td data-label="Type">
                                <span @class([
                                    'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium',
                                    'bg-orange-50 text-orange-700 ring-1 ring-orange-100' => $transaction['type'] === 'purchase',
                                    'bg-teal-50 text-teal-700 ring-1 ring-teal-100' => $transaction['type'] === 'payment',
                                ])>
                                    {{ ucfirst($transaction['type']) }}
                                </span>
                            </td>
                            <td class="col-primary font-mono text-slate-900" data-label="Reference">{{ $transaction['label'] }}</td>
                            <td data-label="Pump">{{ $transaction['pump'] ?? '—' }}</td>
                            <td @class([
                                'text-right font-medium',
                                'text-orange-700' => $transaction['direction'] === 'debit',
                                'text-teal-700' => $transaction['direction'] === 'credit',
                            ]) data-label="Amount">
                                {{ $transaction['direction'] === 'debit' ? '+' : '−' }}{{ number_format($transaction['amount'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr class="data-table-empty-row">
                            <td colspan="5" class="data-table-empty">No transactions recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-data-table-card>

            <div class="data-table-card">
                <div class="border-b border-slate-200 px-5 py-4">
                    <h2 class="text-lg font-semibold text-slate-900">Top Pumps This Month</h2>
                    <p class="mt-1 text-sm text-slate-500">Highest purchase volume by amount</p>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($topPumps as $index => $pump)
                        <div class="flex items-start gap-3 px-5 py-4 transition-colors hover:bg-slate-50">
                            <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-teal-50 text-sm font-semibold text-teal-800">
                                {{ $index + 1 }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-medium text-slate-900">{{ $pump['pump'] }}</p>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ number_format($pump['quantity'], 2) }} {{ $company->quantity_unit ?? 'M3' }}
                                    · {{ $pump['count'] }} slip{{ $pump['count'] === 1 ? '' : 's' }}
                                </p>
                            </div>
                            <p class="whitespace-nowrap text-sm font-semibold text-slate-900">{{ number_format($pump['amount'], 2) }}</p>
                        </div>
                    @empty
                        <p class="px-5 py-8 text-center text-sm text-slate-500">No purchases recorded this month.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <script type="application/json" id="dashboard-chart-data">@json($charts)</script>
</x-app-layout>
