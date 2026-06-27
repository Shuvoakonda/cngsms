<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Confirm password</h1>
        <p class="mt-1 text-sm text-slate-600">Please confirm your password before continuing.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="mt-2 block w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error class="mt-2" :messages="$errors->get('password')" />
        </div>

        <x-primary-button class="w-full">
            {{ __('Confirm') }}
        </x-primary-button>
    </form>
</x-guest-layout>
