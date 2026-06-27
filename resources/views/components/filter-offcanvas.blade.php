@props([
    'action' => null,
    'method' => 'get',
    'resetUrl' => null,
    'activeCount' => 0,
    'title' => 'Filters',
    'submitLabel' => 'Apply Filters',
])

<div
    x-data="{ filterOpen: false }"
    @keydown.escape.window="filterOpen = false"
    {{ $attributes->class(['report-screen-only']) }}
>
    <button
        type="button"
        class="filter-fab print:hidden"
        @click="filterOpen = true"
        aria-label="Open filters"
    >
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
        </svg>
        @if ($activeCount > 0)
            <span class="filter-fab-badge">{{ $activeCount }}</span>
        @endif
    </button>

    <div
        x-show="filterOpen"
        x-cloak
        class="fixed inset-0 z-50 flex justify-end"
        role="dialog"
        aria-modal="true"
        aria-labelledby="filter-offcanvas-title"
    >
        <div
            x-show="filterOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="absolute inset-0 bg-slate-900/40"
            @click="filterOpen = false"
        ></div>

        <div
            x-show="filterOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="relative flex h-full w-full max-w-md flex-col bg-white shadow-2xl ring-1 ring-slate-200"
        >
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-teal-700">Refine results</p>
                    <h2 id="filter-offcanvas-title" class="text-lg font-semibold text-slate-900">{{ $title }}</h2>
                </div>
                <button
                    type="button"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-800"
                    @click="filterOpen = false"
                    aria-label="Close filters"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form
                method="{{ $method }}"
                action="{{ $action ?? url()->current() }}"
                class="flex flex-1 flex-col overflow-hidden"
            >
                <div class="flex-1 space-y-4 overflow-y-auto px-5 py-4">
                    {{ $slot }}
                </div>

                <div class="flex flex-wrap gap-2 border-t border-slate-200 bg-slate-50 px-5 py-4">
                    <x-primary-button class="flex-1 justify-center sm:flex-none">{{ $submitLabel }}</x-primary-button>
                    @if ($resetUrl)
                        <a href="{{ $resetUrl }}" class="inline-flex min-h-10 flex-1 items-center justify-center rounded-lg border border-slate-400 bg-white px-4 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-50 sm:flex-none">Reset</a>
                    @endif
                    @isset($footer)
                        {{ $footer }}
                    @endisset
                </div>
            </form>
        </div>
    </div>
</div>
