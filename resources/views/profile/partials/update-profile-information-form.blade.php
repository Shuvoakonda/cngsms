<section>
    <header>
        <h2 class="text-lg font-semibold text-slate-900">Profile Information</h2>
        <p class="mt-1 text-sm text-slate-600">Update your name and optional email address.</p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-2 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="username" value="Username" />
            <x-text-input id="username" type="text" class="mt-2 block w-full bg-slate-50" :value="$user->username" disabled />
            <p class="mt-1 text-xs text-slate-500">Username changes are managed by an administrator.</p>
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-2 block w-full" :value="old('email', $user->email)" autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p class="text-sm text-teal-700">Saved.</p>
            @endif
        </div>
    </form>
</section>
