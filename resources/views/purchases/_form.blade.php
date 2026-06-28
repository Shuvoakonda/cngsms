<form x-ref="form" method="post" :action="mode === 'edit' ? editActionUrl : '{{ route('purchases.store') }}'" class="space-y-4">
    @csrf
    <input type="hidden" name="_method" value="PATCH" x-ref="methodPatch" :disabled="mode !== 'edit'">
    <input type="hidden" name="_edit_id" x-ref="editIdInput" value="{{ old('_edit_id') }}">

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="form-field sm:col-span-2">
            <x-input-label for="purchase_date" value="Purchase Date" />
            <x-text-input id="purchase_date" name="purchase_date" type="date" :value="old('purchase_date', now()->toDateString())" required />
            <x-input-error :messages="$errors->get('purchase_date')" />
        </div>

        <div class="form-field">
            <x-input-label for="purchase_pump_id" value="Pump" />
            <x-select-input id="purchase_pump_id" name="pump_id" required>
                <option value="">Select pump</option>
                @foreach ($pumps as $pump)
                    <option value="{{ $pump->id }}" @selected((string) old('pump_id') === (string) $pump->id)>{{ $pump->name }}</option>
                @endforeach
            </x-select-input>
            <x-input-error :messages="$errors->get('pump_id')" />
        </div>

        <div class="form-field">
            <x-input-label for="purchase_slip_number" value="Slip Number" />
            <x-text-input id="purchase_slip_number" name="slip_number" type="text" :value="old('slip_number')" required />
            <x-input-error :messages="$errors->get('slip_number')" />
        </div>

        <div class="form-field">
            <x-input-label for="purchase_vehicle_id" value="Vehicle" />
            <x-select-input id="purchase_vehicle_id" name="vehicle_id" @change="onVehicleChange($event)">
                <option value="">Guest Vehicle</option>
                @foreach ($vehicles as $vehicle)
                    <option value="{{ $vehicle->id }}" data-driver-id="{{ $vehicle->driver_id }}" @selected((string) old('vehicle_id') === (string) $vehicle->id)>
                        {{ $vehicle->vehicle_number }}
                    </option>
                @endforeach
            </x-select-input>
            <p class="mt-1 text-xs text-slate-500">Leave as guest for walk-in purchases without a registered vehicle.</p>
            <x-input-error :messages="$errors->get('vehicle_id')" />
        </div>

        <div class="form-field" x-show="guestPurchase" x-cloak>
            <x-input-label for="purchase_guest_reference" value="Guest Reference" />
            <x-text-input id="purchase_guest_reference" name="guest_reference" type="text" :value="old('guest_reference')" placeholder="e.g. plate number or customer name" />
            <p class="mt-1 text-xs text-slate-500">Optional reference to identify the guest purchase.</p>
            <x-input-error :messages="$errors->get('guest_reference')" />
        </div>

        <div class="form-field">
            <x-input-label for="purchase_driver_id" value="Driver" />
            <x-select-input id="purchase_driver_id" name="driver_id" x-ref="driverSelect">
                <option value="">Guest</option>
                @foreach ($drivers as $driver)
                    <option value="{{ $driver->id }}" @selected((string) old('driver_id') === (string) $driver->id)>{{ $driver->name }}</option>
                @endforeach
            </x-select-input>
            <x-input-error :messages="$errors->get('driver_id')" />
        </div>

        <div class="form-field">
            <x-input-label for="purchase_quantity" value="Quantity ({{ $company->quantity_unit ?? 'KG' }})" />
            <x-text-input id="purchase_quantity" name="quantity" type="number" step="0.01" min="0.01" :value="old('quantity')" required inputmode="decimal" @input="updateAmount()" />
            <x-input-error :messages="$errors->get('quantity')" />
        </div>

        <div class="form-field">
            <x-input-label for="purchase_rate" value="Rate" />
            <x-text-input id="purchase_rate" name="rate" type="number" step="0.01" min="0.01" :value="old('rate')" required inputmode="decimal" @input="updateAmount()" />
            <x-input-error :messages="$errors->get('rate')" />
        </div>

        <div class="form-field sm:col-span-2">
            <x-input-label value="Total Amount" />
            <div class="form-control bg-slate-50 font-semibold text-slate-900" x-text="calculatedAmount"></div>
        </div>

        <div class="form-field sm:col-span-2">
            <x-input-label for="purchase_remarks" value="Remarks" />
            <x-textarea-input id="purchase_remarks" name="remarks" rows="2">{{ old('remarks') }}</x-textarea-input>
        </div>
    </div>

    <div class="sticky bottom-0 -mx-5 flex gap-3 border-t border-slate-200 bg-white px-5 py-4">
        <x-primary-button class="flex-1 justify-center" x-text="mode === 'edit' ? 'Update Purchase' : 'Save Purchase'"></x-primary-button>
        <x-secondary-button type="button" class="flex-1 justify-center" @click="closePanel()">Cancel</x-secondary-button>
    </div>
</form>
