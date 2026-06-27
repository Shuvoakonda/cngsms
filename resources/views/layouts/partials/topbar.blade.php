<header class="sticky top-0 z-20 border-b border-slate-200 bg-white/95 backdrop-blur">
    <div class="mx-auto flex h-16 max-w-7xl items-center gap-3 px-4 sm:px-6 lg:px-10 xl:px-12">
        <button
            type="button"
            class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-xl border border-slate-200 text-slate-600 lg:hidden"
            @click="sidebarOpen = true"
            aria-label="Open menu"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <div class="hidden min-w-0 flex-1 sm:block">
            <label class="sr-only" for="global-search">Search</label>
            <div class="relative max-w-xl" x-data="globalSearch()" @click.outside="open = false">
                <input
                    id="global-search"
                    type="search"
                    x-model="query"
                    @input.debounce.300ms="search"
                    @focus="query.length >= 2 && (open = true)"
                    @keydown.escape="open = false"
                    placeholder="Search everywhere..."
                    class="block w-full rounded-full border-slate-200 bg-slate-50/50 py-2 pl-10 pr-4 text-sm transition-colors placeholder:text-slate-400 focus:border-slate-300 focus:bg-white focus:ring-0"
                >
                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>

                <div
                    x-show="open"
                    x-transition
                    class="absolute left-0 right-0 top-full z-50 mt-2 overflow-hidden rounded-xl bg-white shadow-xl ring-1 ring-black/5"
                    style="display: none;"
                >
                    <template x-if="loading">
                        <p class="px-4 py-3 text-sm text-slate-500">Searching...</p>
                    </template>
                    <template x-if="!loading && query.length >= 2 && results.length === 0">
                        <p class="px-4 py-3 text-sm text-slate-500">No results found.</p>
                    </template>
                    <template x-if="!loading && results.length > 0">
                        <ul class="max-h-72 overflow-y-auto py-2">
                            <template x-for="result in results" :key="result.id">
                                <li>
                                    <a :href="result.url" class="block px-4 py-2 hover:bg-slate-50">
                                        <span class="block text-sm font-medium text-slate-900" x-text="result.label"></span>
                                        <span class="mt-0.5 block text-xs text-slate-500">
                                            <span x-text="result.type"></span>
                                            <template x-if="result.subtitle">
                                                <span> · </span>
                                                <span x-text="result.subtitle"></span>
                                            </template>
                                        </span>
                                    </a>
                                </li>
                            </template>
                        </ul>
                    </template>
                </div>
            </div>
        </div>

        <div class="ms-auto flex items-center gap-1.5">
            <a href="{{ route('profile.edit') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-400 transition-colors hover:bg-slate-50 hover:text-slate-600" title="Profile">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
            </a>
            @if (auth()->user()?->isAdministrator())
                <a href="{{ route('admin.settings') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-400 transition-colors hover:bg-slate-50 hover:text-slate-600" title="Settings">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 14a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                </a>
            @endif
            <form method="POST" action="{{ route('logout') }}" class="inline-flex">
                @csrf
                <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-full text-rose-400 transition-colors hover:bg-rose-50 hover:text-rose-600" title="Log Out">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                </button>
            </form>
        </div>
    </div>
</header>
