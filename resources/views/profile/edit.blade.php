<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Profile</h1>
            <p class="mt-1 text-sm text-slate-600">Update your account details and password.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl space-y-6">
        <div class="profile-card">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="profile-card">
            @include('profile.partials.update-password-form')
        </div>

        @can('admin')
            <div class="profile-card">
                @include('profile.partials.delete-user-form')
            </div>
        @endcan
    </div>
</x-app-layout>
