<nav class="fixed inset-x-0 bottom-0 z-20 border-t border-slate-200 bg-white lg:hidden">
    <div class="grid grid-cols-4 gap-1 px-2 py-2">
        <a href="{{ route('dashboard') }}" @class([
            'flex min-h-14 flex-col items-center justify-center rounded-xl text-xs font-medium',
            'bg-teal-50 text-teal-800' => request()->routeIs('dashboard'),
            'text-slate-600' => ! request()->routeIs('dashboard'),
        ])>
            <svg class="mb-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Home
        </a>
        <a href="{{ route('purchases.index') }}" @class([
            'flex min-h-14 flex-col items-center justify-center rounded-xl text-xs font-medium',
            'bg-teal-50 text-teal-800' => request()->routeIs('purchases.*'),
            'text-slate-600' => ! request()->routeIs('purchases.*'),
        ])>
            <svg class="mb-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            Buy
        </a>
        <a href="{{ route('payments.index') }}" @class([
            'flex min-h-14 flex-col items-center justify-center rounded-xl text-xs font-medium',
            'bg-teal-50 text-teal-800' => request()->routeIs('payments.*'),
            'text-slate-600' => ! request()->routeIs('payments.*'),
        ])>
            <svg class="mb-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            Pay
        </a>
        <a href="{{ route('profile.edit') }}" @class([
            'flex min-h-14 flex-col items-center justify-center rounded-xl text-xs font-medium',
            'bg-teal-50 text-teal-800' => request()->routeIs('profile.*'),
            'text-slate-600' => ! request()->routeIs('profile.*'),
        ])>
            <svg class="mb-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            Profile
        </a>
    </div>
</nav>

<div class="h-20 lg:hidden"></div>
