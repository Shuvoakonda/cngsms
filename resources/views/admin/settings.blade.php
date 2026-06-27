<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">System Settings</h1>
            <p class="mt-1 text-sm text-slate-600">Company profile and quick links to master data.</p>
        </div>
    </x-slot>

    <div class="space-y-6 pb-8">
        <div class="grid gap-4 sm:grid-cols-3">
            <a href="{{ route('admin.pumps.index') }}" class="stat-card group">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Pumps</p>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-50 text-teal-600 transition-transform group-hover:scale-110 group-hover:bg-teal-100">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                    </div>
                </div>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $pumpsCount }}</p>
                <p class="mt-2 text-sm font-medium text-teal-700 transition-transform group-hover:translate-x-1">Manage pumps &rarr;</p>
            </a>
            <a href="{{ route('admin.vehicles.index') }}" class="stat-card group">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Vehicles</p>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-50 text-teal-600 transition-transform group-hover:scale-110 group-hover:bg-teal-100">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" /></svg>
                    </div>
                </div>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $vehiclesCount }}</p>
                <p class="mt-2 text-sm font-medium text-teal-700 transition-transform group-hover:translate-x-1">Manage vehicles &rarr;</p>
            </a>
            <a href="{{ route('admin.drivers.index') }}" class="stat-card group">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Drivers</p>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-50 text-teal-600 transition-transform group-hover:scale-110 group-hover:bg-teal-100">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    </div>
                </div>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $driversCount }}</p>
                <p class="mt-2 text-sm font-medium text-teal-700 transition-transform group-hover:translate-x-1">Manage drivers &rarr;</p>
            </a>
        </div>

        <div class="profile-card">
            @include('admin.settings.partials.company-form')
        </div>
    </div>
</x-app-layout>
