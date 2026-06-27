<form x-ref="form" method="post" :action="mode === 'edit' ? editActionUrl : '{{ route('admin.drivers.store') }}'" class="space-y-4">
    @csrf
    <input type="hidden" name="_method" value="PATCH" x-ref="methodPatch" :disabled="mode !== 'edit'">
    <input type="hidden" name="_edit_id" x-ref="editIdInput" value="{{ old('_edit_id') }}">

    <div class="form-field">
        <x-input-label for="driver_name" value="Name" />
        <x-text-input id="driver_name" name="name" type="text" :value="old('name')" required />
        <x-input-error :messages="$errors->get('name')" />
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="form-field">
            <x-input-label for="driver_mobile" value="Mobile" />
            <x-text-input id="driver_mobile" name="mobile" type="text" :value="old('mobile')" inputmode="tel" />
        </div>

        <div class="form-field">
            <x-input-label for="driver_license_number" value="License Number" />
            <x-text-input id="driver_license_number" name="license_number" type="text" :value="old('license_number')" />
        </div>
    </div>

    <div class="form-field">
        <x-input-label for="driver_address" value="Address" />
        <x-textarea-input id="driver_address" name="address" rows="3">{{ old('address') }}</x-textarea-input>
    </div>

    <div class="sticky bottom-0 -mx-5 flex gap-3 border-t border-slate-200 bg-white px-5 py-4">
        <x-primary-button class="flex-1 justify-center" x-text="mode === 'edit' ? 'Update Driver' : 'Save Driver'"></x-primary-button>
        <x-secondary-button type="button" class="flex-1 justify-center" @click="closePanel()">Cancel</x-secondary-button>
    </div>
</form>
