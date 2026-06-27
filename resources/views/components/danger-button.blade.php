<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex min-h-11 items-center justify-center rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-red-700 hover:shadow focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 active:scale-95']) }}>
    {{ $slot }}
</button>
