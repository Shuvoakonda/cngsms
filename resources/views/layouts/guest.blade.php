<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $company->name ?? config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-gradient-to-br from-slate-100 via-teal-50 to-slate-200 font-sans text-slate-900 antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center px-4 py-8 sm:px-6">
            <div class="mb-8 text-center">
                <a href="/" class="inline-flex flex-col items-center gap-3">
                    <x-company-logo size="lg" />
                    <div>
                        <p class="text-lg font-semibold text-slate-900">{{ $company->name ?? 'Marwha Enterprise' }}</p>
                        <p class="text-sm text-slate-600">Ledger Management</p>
                    </div>
                </a>
            </div>

            <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white p-6 shadow-xl ring-1 ring-slate-900/5 sm:p-8">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
