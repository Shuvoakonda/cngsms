<form x-ref="form" method="post" :action="mode === 'edit' ? editActionUrl : '{{ route('payments.store') }}'" class="space-y-4">
    @csrf
    <input type="hidden" name="_method" value="PATCH" x-ref="methodPatch" :disabled="mode !== 'edit'">
    <input type="hidden" name="_edit_id" x-ref="editIdInput" value="{{ old('_edit_id') }}">

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="form-field sm:col-span-2">
            <x-input-label for="payment_date" value="Payment Date" />
            <x-text-input id="payment_date" name="payment_date" type="date" :value="old('payment_date', now()->toDateString())" required />
            <x-input-error :messages="$errors->get('payment_date')" />
        </div>

        <div class="form-field sm:col-span-2">
            <x-input-label for="payment_type" value="Entry Type" />
            <x-select-input id="payment_type" name="type" required>
                @foreach (\App\Enums\PaymentType::cases() as $entryType)
                    <option value="{{ $entryType->value }}" @selected(old('type', \App\Enums\PaymentType::Payment->value) === $entryType->value)>
                        {{ $entryType->label() }}
                    </option>
                @endforeach
            </x-select-input>
            <p class="mt-1 text-xs text-slate-500">Use Advance when paying money to the pump before or without settling a specific purchase.</p>
            <x-input-error :messages="$errors->get('type')" />
        </div>

        <div class="form-field">
            <x-input-label for="payment_pump_id" value="Pump" />
            <x-select-input id="payment_pump_id" name="pump_id" required>
                <option value="">Select pump</option>
                @foreach ($pumps as $pump)
                    <option value="{{ $pump->id }}" @selected((string) old('pump_id') === (string) $pump->id)>{{ $pump->name }}</option>
                @endforeach
            </x-select-input>
            <x-input-error :messages="$errors->get('pump_id')" />
        </div>

        <div class="form-field">
            <x-input-label for="payment_voucher_number" value="Voucher Number" />
            <x-text-input id="payment_voucher_number" name="voucher_number" type="text" :value="old('voucher_number')" required />
            <x-input-error :messages="$errors->get('voucher_number')" />
        </div>

        <div class="form-field">
            <x-input-label for="payment_method" value="Payment Method" />
            <x-select-input id="payment_method" name="payment_method" required>
                @foreach (\App\Enums\PaymentMethod::cases() as $method)
                    <option value="{{ $method->value }}" @selected(old('payment_method', \App\Enums\PaymentMethod::Cash->value) === $method->value)>
                        {{ $method->label() }}
                    </option>
                @endforeach
            </x-select-input>
        </div>

        <div class="form-field">
            <x-input-label for="payment_amount" value="Amount" />
            <x-text-input id="payment_amount" name="amount" type="number" step="0.01" min="0.01" :value="old('amount')" required inputmode="decimal" />
            <x-input-error :messages="$errors->get('amount')" />
        </div>

        <div class="form-field sm:col-span-2">
            <x-input-label for="payment_reference_number" value="Reference Number" />
            <x-text-input id="payment_reference_number" name="reference_number" type="text" :value="old('reference_number')" />
        </div>

        <div class="form-field sm:col-span-2">
            <x-input-label for="payment_remarks" value="Remarks" />
            <x-textarea-input id="payment_remarks" name="remarks" rows="2">{{ old('remarks') }}</x-textarea-input>
        </div>
    </div>

    <div class="sticky bottom-0 -mx-5 flex gap-3 border-t border-slate-200 bg-white px-5 py-4">
        <x-primary-button class="flex-1 justify-center" x-text="mode === 'edit' ? 'Update Payment' : 'Save Payment'"></x-primary-button>
        <x-secondary-button type="button" class="flex-1 justify-center" @click="closePanel()">Cancel</x-secondary-button>
    </div>
</form>
