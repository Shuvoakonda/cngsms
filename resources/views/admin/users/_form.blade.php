<form x-ref="form" method="post" :action="mode === 'edit' ? editActionUrl : '{{ route('admin.users.store') }}'" class="space-y-4">
    @csrf
    <input type="hidden" name="_method" value="PATCH" x-ref="methodPatch" :disabled="mode !== 'edit'">
    <input type="hidden" name="_edit_id" x-ref="editIdInput" value="{{ old('_edit_id') }}">

    <div class="form-field">
        <x-input-label for="user_name" value="Full Name" />
        <x-text-input id="user_name" name="name" type="text" :value="old('name')" required />
        <x-input-error :messages="$errors->get('name')" />
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="form-field">
            <x-input-label for="user_username" value="Username" />
            <x-text-input id="user_username" name="username" type="text" :value="old('username')" required />
            <x-input-error :messages="$errors->get('username')" />
        </div>
        <div class="form-field">
            <x-input-label for="user_email" value="Email" />
            <x-text-input id="user_email" name="email" type="email" :value="old('email')" />
            <x-input-error :messages="$errors->get('email')" />
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="form-field">
            <x-input-label for="user_password" value="Password" />
            <x-text-input id="user_password" name="password" type="password" autocomplete="new-password" />
            <p class="text-xs text-slate-500" x-show="mode === 'edit'">Leave blank to keep the current password.</p>
            <x-input-error :messages="$errors->get('password')" />
        </div>
        <div class="form-field">
            <x-input-label for="user_password_confirmation" value="Confirm Password" />
            <x-text-input id="user_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" />
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="form-field">
            <x-input-label for="user_role" value="Role" />
            <x-select-input id="user_role" name="role">
                @foreach (\App\Enums\UserRole::cases() as $role)
                    <option value="{{ $role->value }}">{{ $role->label() }}</option>
                @endforeach
            </x-select-input>
            <x-input-error :messages="$errors->get('role')" />
        </div>
        <div class="form-field">
            <x-input-label for="user_status" value="Status" />
            <x-select-input id="user_status" name="status">
                @foreach (\App\Enums\UserStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(old('status', \App\Enums\UserStatus::Active->value) === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </x-select-input>
            <x-input-error :messages="$errors->get('status')" />
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
        <p class="text-sm font-medium text-slate-900">Role Permissions</p>
        <p class="mt-1 text-xs text-slate-500">Permissions are assigned automatically based on the selected role. See the role matrix above the user list.</p>
    </div>

    <div class="sticky bottom-0 -mx-5 flex gap-3 border-t border-slate-200 bg-white px-5 py-4">
        <x-primary-button class="flex-1 justify-center" x-text="mode === 'edit' ? 'Update User' : 'Create User'"></x-primary-button>
        <x-secondary-button type="button" class="flex-1 justify-center" @click="closePanel()">Cancel</x-secondary-button>
    </div>
</form>
