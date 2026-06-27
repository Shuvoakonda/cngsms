@props(['action', 'export' => null, 'title' => 'Report Filters'])

@php
    $activeCount = collect(request()->query())->except('page')->filter(fn ($value) => filled($value))->count();
@endphp

<x-filter-offcanvas
    :action="$action"
    :reset-url="$action"
    :active-count="$activeCount"
    :title="$title"
>
    {{ $slot }}

    <x-slot:footer>
        @if ($export)
            <a href="{{ $export }}" class="inline-flex min-h-10 flex-1 items-center justify-center rounded-lg border border-slate-400 bg-white px-4 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-50 sm:flex-none">Export Excel</a>
        @endif
        <button type="button" onclick="window.print()" class="inline-flex min-h-10 flex-1 items-center justify-center rounded-lg border border-slate-400 bg-white px-4 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-50 print:hidden sm:flex-none">Print</button>
    </x-slot:footer>
</x-filter-offcanvas>
