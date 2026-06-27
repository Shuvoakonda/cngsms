<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Sign in</h1>
        <p class="mt-1 text-sm text-slate-600">Enter your credentials to access the dashboard.</p>
    </div>

    <x-auth-session-status class="mb-4" :messages="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="username" :value="__('Username')" />
            <x-text-input id="username" class="mt-2 block w-full" type="text" name="username" :value="old('username')" required autofocus autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('username')" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="mt-2 block w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error class="mt-2" :messages="$errors->get('password')" />
        </div>

        <div class="flex items-center justify-between gap-4">
            <label for="remember_me" class="inline-flex items-center gap-2">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-teal-700 shadow-sm focus:ring-teal-500" name="remember">
                <span class="text-sm text-slate-600">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm font-medium text-teal-700 hover:text-teal-900" href="{{ route('password.request') }}">
                    {{ __('Forgot password?') }}
                </a>
            @endif
        </div>

        <x-primary-button class="w-full">
            {{ __('Log in') }}
        </x-primary-button>
    </form>

    <div class="mt-6 rounded-xl bg-slate-50 p-4 text-xs text-slate-600 ring-1 ring-slate-200">
        <p class="font-semibold text-slate-800">Demo accounts</p>
        <p class="mt-1">Admin: <span class="font-mono">admin</span> / <span class="font-mono">password</span></p>
        <p>Data Entry: <span class="font-mono">dataentry</span> / <span class="font-mono">password</span></p>
    </div>
</x-guest-layout>
