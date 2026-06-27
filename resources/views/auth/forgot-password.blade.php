<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Reset password</h1>
        <p class="mt-1 text-sm text-slate-600">Enter the email linked to your account and we will send a reset link.</p>
    </div>

    <x-auth-session-status class="mb-4" :messages="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-2 block w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <x-primary-button class="w-full">
            {{ __('Email Password Reset Link') }}
        </x-primary-button>
    </form>
</x-guest-layout>
