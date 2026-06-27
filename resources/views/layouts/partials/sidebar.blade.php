@php

    $navItems = [

        ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],

        ['label' => 'Purchases', 'route' => 'purchases.index', 'match' => 'purchases.*', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],

        ['label' => 'Payments', 'route' => 'payments.index', 'match' => 'payments.*', 'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],

        ['label' => 'Reports', 'route' => 'reports.index', 'match' => 'reports.*', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],

    ];



    if (auth()->user()?->isAdministrator()) {

        $navItems[] = ['label' => 'Master Data', 'section' => true];

        $navItems[] = ['label' => 'Pumps', 'route' => 'admin.pumps.index', 'match' => 'admin.pumps.*', 'icon' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z'];

        $navItems[] = ['label' => 'Vehicles', 'route' => 'admin.vehicles.index', 'match' => 'admin.vehicles.*', 'icon' => 'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0'];

        $navItems[] = ['label' => 'Drivers', 'route' => 'admin.drivers.index', 'match' => 'admin.drivers.*', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'];

        $navItems[] = ['label' => 'Users', 'route' => 'admin.users.index', 'match' => 'admin.users.*', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'];

        $navItems[] = ['label' => 'Settings', 'route' => 'admin.settings', 'match' => 'admin.settings*', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'];

    }

@endphp



<div
    class="fixed inset-y-0 left-0 z-40 w-64 -translate-x-full border-r border-slate-800 bg-slate-900 transition-transform lg:translate-x-0"
    :class="{ 'translate-x-0': sidebarOpen }"
>
    <div class="flex h-16 items-center gap-3 border-b border-slate-800 px-5">
        <x-company-logo size="sm" variant="sidebar" />
        <div class="min-w-0">
            <p class="truncate text-sm font-semibold text-white">{{ $company->name ?? 'Marwha Enterprise' }}</p>
            <p class="truncate text-xs text-slate-400">Ledger Management</p>
        </div>
    </div>



    <nav class="space-y-1 overflow-y-auto p-4 pb-28">

        @foreach ($navItems as $item)

            @if (! empty($item['section']))

                <p class="px-3 pb-1 pt-4 text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ $item['label'] }}</p>

            @elseif ($item['route'])

                @php

                    $isActive = request()->routeIs($item['match'] ?? $item['route']);

                @endphp

                <a href="{{ route($item['route']) }}"
                   @class([
                       'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200',
                       'bg-slate-800 text-white shadow-sm' => $isActive,
                       'text-slate-400 hover:bg-slate-800/50 hover:text-white' => ! $isActive,
                   ])>
                    <svg @class([
                        'h-5 w-5 shrink-0 transition-transform duration-200 group-hover:scale-110',
                        'text-slate-300' => $isActive,
                        'text-slate-500 group-hover:text-slate-300' => ! $isActive,
                    ]) fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $item['icon'] }}" />
                    </svg>
                    {{ $item['label'] }}
                </a>

            @else

                <span class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-500">
                    <svg class="h-5 w-5 shrink-0 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $item['icon'] }}" />
                    </svg>
                    {{ $item['label'] }}
                    <span class="ms-auto rounded-full bg-slate-800 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Soon</span>
                </span>

            @endif

        @endforeach

    </nav>



    <div class="absolute bottom-0 w-full border-t border-slate-800 bg-slate-900 p-4">
        <div class="flex items-center gap-3 rounded-xl bg-slate-800/50 p-3 ring-1 ring-slate-700/50 transition-all duration-200 hover:bg-slate-800 hover:shadow-sm">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-slate-700 to-slate-800 text-sm font-bold text-white shadow-sm ring-1 ring-slate-600/50">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold text-white">{{ auth()->user()->name }}</p>
                <p class="truncate text-xs text-slate-400">{{ auth()->user()->role->label() }}</p>
            </div>
        </div>
    </div>

</div>



<div

    x-show="sidebarOpen"

    x-transition.opacity

    class="fixed inset-0 z-30 bg-slate-900/40 lg:hidden"

    style="display: none;"

    @click="sidebarOpen = false"

></div>


