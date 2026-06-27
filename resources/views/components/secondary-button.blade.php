<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-800 shadow-sm transition-all duration-200 hover:bg-slate-50 hover:border-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:ring-offset-1 active:scale-95']) }}>
    {{ $slot }}
</button>
