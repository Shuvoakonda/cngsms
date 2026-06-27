<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Reports</h1>
            <p class="mt-1 text-sm text-slate-600">Filter, print, and export fleet purchase and payment reports to Excel.</p>
        </div>
    </x-slot>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ([
            ['Daily Purchases', 'Purchase slips by date, pump, and vehicle.', 'reports.daily-purchases'],
            ['Monthly Summary', 'Pump and vehicle totals for a selected month.', 'reports.monthly-purchases'],
            ['Pump Ledger', 'Debit/credit statement with running balance.', 'reports.pump-ledger'],
            ['Outstanding Due', 'Amount owed to each pump.', 'reports.outstanding'],
            ['Vehicle-wise', 'Purchase totals grouped by vehicle.', 'reports.vehicle-wise'],
            ['Driver-wise', 'Purchase totals grouped by driver.', 'reports.driver-wise'],
            ['Payments', 'Payment vouchers by date and pump.', 'reports.payments'],
        ] as [$title, $desc, $route])
            <a href="{{ route($route) }}" class="group rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 transition-all duration-300 hover:-translate-y-1 hover:shadow-md hover:ring-slate-300 animate-fade-in-up">
                <div class="flex items-start justify-between">
                    <h2 class="text-lg font-semibold text-slate-900 transition-colors group-hover:text-teal-700">{{ $title }}</h2>
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-50 text-slate-400 transition-colors group-hover:bg-teal-50 group-hover:text-teal-600">
                        <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </span>
                </div>
                <p class="mt-2 text-sm text-slate-600">{{ $desc }}</p>
            </a>
        @endforeach
    </div>
</x-app-layout>
