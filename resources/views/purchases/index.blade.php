@php

    $isEdit = old('_method') === 'patch';

    $editId = old('_edit_id');

    $offcanvasConfig = [

        'openOnLoad' => $errors->any(),

        'initialMode' => $isEdit ? 'edit' : 'create',

        'initialTitle' => $isEdit ? 'Edit Purchase' : 'Add Purchase',

        'initialEditUrl' => $editId ? route('purchases.update', $editId) : '',

        'createTitle' => 'Add Purchase',

        'editTitle' => 'Edit Purchase',

        'storeUrl' => route('purchases.store'),

        'updateUrlTemplate' => route('purchases.update', ['purchase' => '__ID__']),

        'defaults' => [

            'purchase_date' => now()->toDateString(),

            'pump_id' => '',

            'slip_number' => '',

            'vehicle_id' => '',

            'driver_id' => '',

            'guest_reference' => '',

            'quantity' => '',

            'rate' => '',

            'remarks' => '',

        ],

    ];

    $activeFilterCount = collect($filters ?? [])->filter(fn ($value) => filled($value))->count();

@endphp



<x-app-layout>

    <x-slot name="header">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

            <div>

                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Purchases</h1>

                <p class="mt-1 text-sm text-slate-600">Daily CNG purchase slip entries.</p>

            </div>

                    <button class="inline-flex min-h-11 items-center justify-center rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-teal-800 hover:shadow focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:ring-offset-1 active:scale-95" onclick="window.dispatchEvent(new CustomEvent('purchases-open-create'))">

                Add Purchase

            </button>

        </div>

    </x-slot>



    <div x-data="offcanvasCrud(@js($offcanvasConfig))" @purchases-open-create.window="openCreate()">

        <x-filter-offcanvas

            :active-count="$activeFilterCount"

            reset-url="{{ route('purchases.index') }}"

            submit-label="Apply"

        >

            <div class="form-field">

                <x-input-label for="filter_date_from" value="From" />

                <x-text-input id="filter_date_from" name="date_from" type="date" :value="$filters['date_from'] ?? ''" />

            </div>

            <div class="form-field">

                <x-input-label for="filter_date_to" value="To" />

                <x-text-input id="filter_date_to" name="date_to" type="date" :value="$filters['date_to'] ?? ''" />

            </div>

            <div class="form-field">

                <x-input-label for="filter_pump_id" value="Pump" />

                <x-select-input id="filter_pump_id" name="pump_id">

                    <option value="">All pumps</option>

                    @foreach ($pumps as $pump)

                        <option value="{{ $pump->id }}" @selected(($filters['pump_id'] ?? '') == $pump->id)>{{ $pump->name }}</option>

                    @endforeach

                </x-select-input>

            </div>

            <div class="form-field">

                <x-input-label for="filter_vehicle_id" value="Vehicle" />

                <x-select-input id="filter_vehicle_id" name="vehicle_id">

                    <option value="">All vehicles</option>

                    <option value="guest" @selected(($filters['vehicle_id'] ?? '') == 'guest')>Guest vehicles</option>

                    @foreach ($vehicles as $vehicle)

                        <option value="{{ $vehicle->id }}" @selected(($filters['vehicle_id'] ?? '') == $vehicle->id)>{{ $vehicle->vehicle_number }}</option>

                    @endforeach

                </x-select-input>

            </div>

            <div class="form-field">
                <x-input-label for="filter_status" value="Status" />
                <x-select-input id="filter_status" name="status">
                    <option value="">Active</option>
                    <option value="trashed" @selected(($filters['status'] ?? '') == 'trashed')>Deleted</option>
                </x-select-input>
            </div>
        </x-filter-offcanvas>



        <x-data-table-card :paginator="$purchases">

            <thead>

                <tr>

                    <th>Date</th>

                    <th>Slip</th>

                    <th>Pump</th>

                    <th>Vehicle</th>

                    <th>Qty</th>

                    <th class="text-right">Amount</th>

                    <th class="text-right">Actions</th>

                </tr>

            </thead>

            <tbody>

                @forelse ($purchases as $purchase)

                    <tr>

                        <td data-label="Date">{{ $purchase->purchase_date->format('d M Y') }}</td>

                        <td class="col-primary font-mono text-slate-900" data-label="Slip">{{ $purchase->slip_number }}</td>

                        <td data-label="Pump">{{ $purchase->pump?->name }}</td>

                        <td data-label="Vehicle">{{ $purchase->displayVehicle() }}</td>

                        <td data-label="Qty">{{ number_format((float) $purchase->quantity, 2) }}</td>

                        <td class="text-right font-medium text-slate-900" data-label="Amount">{{ number_format((float) $purchase->amount, 2) }}</td>

                        <td class="col-actions text-right">

                            <div class="flex justify-end gap-2">
                                @if($purchase->trashed())
                                    @can('delete-records')
                                        <form method="post" action="{{ route('purchases.restore', $purchase->id) }}" onsubmit="return confirm('Restore this purchase?')">
                                            @csrf
                                            <button type="submit" class="rounded-lg px-3 py-1.5 text-xs font-medium text-teal-700 hover:bg-teal-50">Restore</button>
                                        </form>
                                    @endcan
                                @else
                                    <button type="button" class="rounded-lg px-3 py-1.5 text-xs font-medium text-teal-700 hover:bg-teal-50" @click="openEdit(@js([

                                    'id' => $purchase->id,

                                    'purchase_date' => $purchase->purchase_date->format('Y-m-d'),

                                    'pump_id' => (string) $purchase->pump_id,

                                    'slip_number' => $purchase->slip_number,

                                    'vehicle_id' => (string) ($purchase->vehicle_id ?? ''),

                                    'driver_id' => (string) ($purchase->driver_id ?? ''),

                                    'guest_reference' => $purchase->guest_reference ?? '',

                                    'quantity' => (string) $purchase->quantity,

                                    'rate' => (string) $purchase->rate,

                                    'remarks' => $purchase->remarks,
                                ]))">Edit</button>
                                @can('delete-records')
                                    <form method="post" action="{{ route('purchases.destroy', $purchase) }}" onsubmit="return confirm('Delete this purchase?')">
                                        @csrf @method('delete')
                                        <button type="submit" class="rounded-lg px-3 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-50">Delete</button>
                                    </form>
                                @endcan
                                @endif
                            </div>

                        </td>

                    </tr>

                @empty

                    <tr class="data-table-empty-row">

                        <td colspan="7" class="data-table-empty">No purchase entries found.</td>

                    </tr>

                @endforelse

            </tbody>

        </x-data-table-card>



        <x-offcanvas title="Purchase Entry">

            @include('purchases._form')

        </x-offcanvas>

    </div>

</x-app-layout>

