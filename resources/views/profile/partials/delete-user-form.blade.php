<section class="space-y-6">
    <header>
        <h2 class="text-lg font-semibold text-slate-900">Delete Account</h2>
        <p class="mt-1 text-sm text-slate-600">Once deleted, all data associated with this account will be permanently removed.</p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Delete Account') }}</x-danger-button>

    <div
        x-data="{ show: false }"
        x-on:open-modal.window="if ($event.detail === 'confirm-user-deletion') show = true"
        x-show="show"
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;"
    >
        <div class="flex min-h-full items-end justify-center px-4 py-6 sm:items-center">
            <div x-show="show" x-transition.opacity class="fixed inset-0 bg-slate-900/50" @click="show = false"></div>

            <div x-show="show" x-transition class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
                <form method="post" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('delete')

                    <h2 class="text-lg font-semibold text-slate-900">Are you sure?</h2>
                    <p class="mt-2 text-sm text-slate-600">Enter your password to confirm account deletion.</p>

                    <div class="mt-4">
                        <x-input-label for="password" value="Password" class="sr-only" />
                        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" placeholder="Password" />
                        <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <x-secondary-button type="button" x-on:click="show = false">Cancel</x-secondary-button>
                        <x-danger-button>{{ __('Delete Account') }}</x-danger-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
