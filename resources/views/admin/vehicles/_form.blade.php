<form x-ref="form" method="post" :action="mode === 'edit' ? editActionUrl : '{{ route('admin.vehicles.store') }}'" class="space-y-4">
    @csrf
    <input type="hidden" name="_method" value="PATCH" x-ref="methodPatch" :disabled="mode !== 'edit'">
    <input type="hidden" name="_edit_id" x-ref="editIdInput" value="{{ old('_edit_id') }}">

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="form-field">
            <x-input-label for="vehicle_number" value="Vehicle Number" />
            <x-text-input id="vehicle_number" name="vehicle_number" type="text" :value="old('vehicle_number')" required />
            <x-input-error :messages="$errors->get('vehicle_number')" />
        </div>

        <div class="form-field">
            <x-input-label for="registration_number" value="Registration Number" />
            <x-text-input id="registration_number" name="registration_number" type="text" :value="old('registration_number')" />
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="form-field">
            <x-input-label for="vehicle_type" value="Type" />
            <x-text-input id="vehicle_type" name="type" type="text" :value="old('type', 'CNG Auto Rickshaw')" />
        </div>

        <div class="form-field">
            <x-input-label for="vehicle_driver_id" value="Assigned Driver" />
            <x-select-input id="vehicle_driver_id" name="driver_id">
                <option value="">Unassigned</option>
                @foreach ($drivers as $driver)
                    <option value="{{ $driver->id }}" @selected((string) old('driver_id') === (string) $driver->id)>
                        {{ $driver->name }}
                    </option>
                @endforeach
            </x-select-input>
            @if ($drivers->isEmpty())
                <p class="text-xs text-slate-500">Add a driver first to assign one.</p>
            @endif
        </div>
    </div>

    <div class="form-field">
        <x-input-label for="vehicle_status" value="Status" />
        <x-select-input id="vehicle_status" name="status">
            @foreach (\App\Enums\VehicleStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(old('status', \App\Enums\VehicleStatus::Active->value) === $status->value)>
                    {{ $status->label() }}
                </option>
            @endforeach
        </x-select-input>
    </div>

    <div class="sticky bottom-0 -mx-5 flex gap-3 border-t border-slate-200 bg-white px-5 py-4">
        <x-primary-button class="flex-1 justify-center" x-text="mode === 'edit' ? 'Update Vehicle' : 'Save Vehicle'"></x-primary-button>
        <x-secondary-button type="button" class="flex-1 justify-center" @click="closePanel()">Cancel</x-secondary-button>
    </div>
</form>
