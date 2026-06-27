@props(['open' => 'panelOpen'])

<div
    x-show="{{ $open }}"
    x-cloak
    class="fixed inset-0 z-50 flex justify-end"
    role="dialog"
    aria-modal="true"
    @keydown.escape.window="closePanel()"
>
    <div
        x-show="{{ $open }}"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="absolute inset-0 bg-slate-900/40"
        @click="closePanel()"
    ></div>

    <div
        x-show="{{ $open }}"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="relative flex h-full w-full max-w-lg flex-col bg-white shadow-2xl ring-1 ring-slate-200"
    >
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-teal-700">Master Data</p>
                <h2 class="text-lg font-semibold text-slate-900" x-text="title"></h2>
            </div>
            <button
                type="button"
                class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-800"
                @click="closePanel()"
                aria-label="Close panel"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto px-5 py-4">
            {{ $slot }}
        </div>
    </div>
</div>
