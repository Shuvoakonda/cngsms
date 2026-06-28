<form x-ref="form" method="post" :action="mode === 'edit' ? editActionUrl : '{{ route('admin.pumps.store') }}'" class="space-y-4">
    @csrf
    <input type="hidden" name="_method" value="PATCH" x-ref="methodPatch" :disabled="mode !== 'edit'">
    <input type="hidden" name="_edit_id" x-ref="editIdInput" value="{{ old('_edit_id') }}">

    <div class="form-field">
        <x-input-label for="pump_name" value="Pump Name" />
        <x-text-input id="pump_name" name="name" type="text" :value="old('name')" required />
        <x-input-error :messages="$errors->get('name')" />
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="form-field">
            <x-input-label for="pump_contact_person" value="Contact Person" />
            <x-text-input id="pump_contact_person" name="contact_person" type="text" :value="old('contact_person')" />
        </div>

        <div class="form-field">
            <x-input-label for="pump_mobile" value="Mobile" />
            <x-text-input id="pump_mobile" name="mobile" type="text" :value="old('mobile')" inputmode="tel" />
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="form-field">
            <x-input-label for="pump_opening_balance" value="Opening Due" />
            <x-text-input id="pump_opening_balance" name="opening_balance" type="number" step="0.01" min="0" :value="old('opening_balance', '0')" required inputmode="decimal" />
            <p class="mt-1 text-xs text-slate-500">Amount you already owed this pump when adding it.</p>
            <x-input-error :messages="$errors->get('opening_balance')" />
        </div>

        <div class="form-field">
            <x-input-label for="pump_credit_limit" value="Credit Limit" />
            <x-text-input id="pump_credit_limit" name="credit_limit" type="number" step="0.01" min="0" :value="old('credit_limit', '0')" required inputmode="decimal" />
            <x-input-error :messages="$errors->get('credit_limit')" />
        </div>
    </div>

    <div class="form-field">
        <x-input-label for="pump_status" value="Status" />
        <x-select-input id="pump_status" name="status">
            @foreach (\App\Enums\PumpStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(old('status', \App\Enums\PumpStatus::Active->value) === $status->value)>
                    {{ $status->label() }}
                </option>
            @endforeach
        </x-select-input>
    </div>

    <div class="form-field">
        <x-input-label for="pump_address" value="Address" />
        <x-textarea-input id="pump_address" name="address" rows="3">{{ old('address') }}</x-textarea-input>
    </div>

    <div class="sticky bottom-0 -mx-5 flex gap-3 border-t border-slate-200 bg-white px-5 py-4">
        <x-primary-button class="flex-1 justify-center" x-text="mode === 'edit' ? 'Update Pump' : 'Save Pump'"></x-primary-button>
        <x-secondary-button type="button" class="flex-1 justify-center" @click="closePanel()">Cancel</x-secondary-button>
    </div>
</form>
