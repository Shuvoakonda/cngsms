<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $company->name ?? config('app.name') }} — @yield('title', 'Dashboard')</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-100 font-sans text-slate-900 antialiased" x-data="{ sidebarOpen: false }">
        <x-toast />

        <div class="flex min-h-screen">
            @include('layouts.partials.sidebar')

            <div class="flex min-h-screen flex-1 flex-col lg:pl-64">
                @include('layouts.partials.topbar')

                @if (isset($header))
                    <header class="border-b border-slate-200 bg-white px-4 py-6 sm:px-6 lg:px-10 xl:px-12">
                        <div class="mx-auto max-w-7xl">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                <main class="flex-1 px-4 py-8 sm:px-6 lg:px-10 xl:px-12">
                    <div class="mx-auto max-w-7xl">
                        {{ $slot }}
                    </div>
                </main>

                @include('layouts.partials.mobile-nav')
            </div>
        </div>

        @if (session('status'))
            <script>
                document.addEventListener('alpine:init', () => {
                    window.dispatchEvent(new CustomEvent('notify', {
                        detail: { message: @json(session('status')), type: 'success' }
                    }));
                });
            </script>
        @endif

        @if (session('error'))
            <script>
                document.addEventListener('alpine:init', () => {
                    window.dispatchEvent(new CustomEvent('notify', {
                        detail: { message: @json(session('error')), type: 'error' }
                    }));
                });
            </script>
        @endif

        @stack('scripts')
    </body>
</html>
