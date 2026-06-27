@php

    $isEdit = old('_method') === 'patch';

    $editId = old('_edit_id');

    $offcanvasConfig = [

        'openOnLoad' => $errors->any(),

        'initialMode' => $isEdit ? 'edit' : 'create',

        'initialTitle' => $isEdit ? 'Edit Payment' : 'Add Payment',

        'initialEditUrl' => $editId ? route('payments.update', $editId) : '',

        'createTitle' => 'Add Payment',

        'editTitle' => 'Edit Payment',

        'storeUrl' => route('payments.store'),

        'updateUrlTemplate' => route('payments.update', ['payment' => '__ID__']),

        'defaults' => [

            'payment_date' => now()->toDateString(),

            'pump_id' => '',

            'voucher_number' => '',

            'payment_method' => \App\Enums\PaymentMethod::Cash->value,

            'amount' => '',

            'reference_number' => '',

            'remarks' => '',

        ],

    ];

    $activeFilterCount = collect($filters ?? [])->filter(fn ($value) => filled($value))->count();

@endphp



<x-app-layout>

    <x-slot name="header">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

            <div>

                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Payments</h1>

                <p class="mt-1 text-sm text-slate-600">Record payments made to pumps.</p>

            </div>

            <button type="button" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-teal-800 hover:shadow focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:ring-offset-1 active:scale-95" onclick="window.dispatchEvent(new CustomEvent('payments-open-create'))">

                Add Payment

            </button>

        </div>

    </x-slot>



    <div x-data="offcanvasCrud(@js($offcanvasConfig))" @payments-open-create.window="openCreate()">

        <x-filter-offcanvas

            :active-count="$activeFilterCount"

            reset-url="{{ route('payments.index') }}"

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
                <x-input-label for="filter_status" value="Status" />
                <x-select-input id="filter_status" name="status">
                    <option value="">Active</option>
                    <option value="trashed" @selected(($filters['status'] ?? '') == 'trashed')>Deleted</option>
                </x-select-input>
            </div>
        </x-filter-offcanvas>



        <x-data-table-card :paginator="$payments">

            <thead>

                <tr>

                    <th>Date</th>

                    <th>Voucher</th>

                    <th>Pump</th>

                    <th>Method</th>

                    <th class="text-right">Amount</th>

                    <th class="text-right">Actions</th>

                </tr>

            </thead>

            <tbody>

                @forelse ($payments as $payment)

                    <tr>

                        <td data-label="Date">{{ $payment->payment_date->format('d M Y') }}</td>

                        <td class="col-primary font-mono text-slate-900" data-label="Voucher">{{ $payment->voucher_number }}</td>

                        <td data-label="Pump">{{ $payment->pump?->name }}</td>

                        <td data-label="Method">{{ $payment->payment_method->label() }}</td>

                        <td class="text-right font-medium text-teal-700" data-label="Amount">{{ number_format((float) $payment->amount, 2) }}</td>

                        <td class="col-actions text-right">
                            <div class="flex justify-end gap-2">
                                @if($payment->trashed())
                                    @can('delete-records')
                                        <form method="post" action="{{ route('payments.restore', $payment->id) }}" onsubmit="return confirm('Restore this payment?')">
                                            @csrf
                                            <button type="submit" class="rounded-lg px-3 py-1.5 text-xs font-medium text-teal-700 hover:bg-teal-50">Restore</button>
                                        </form>
                                    @endcan
                                @else
                                    <button type="button" class="rounded-lg px-3 py-1.5 text-xs font-medium text-teal-700 hover:bg-teal-50" @click="openEdit(@js([

                                    'id' => $payment->id,

                                    'payment_date' => $payment->payment_date->format('Y-m-d'),

                                    'pump_id' => (string) $payment->pump_id,

                                    'voucher_number' => $payment->voucher_number,

                                    'payment_method' => $payment->payment_method->value,

                                    'amount' => (string) $payment->amount,

                                    'reference_number' => $payment->reference_number,

                                    'remarks' => $payment->remarks,
                                ]))">Edit</button>
                                @can('delete-records')
                                    <form method="post" action="{{ route('payments.destroy', $payment) }}" onsubmit="return confirm('Delete this payment?')">
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

                        <td colspan="6" class="data-table-empty">No payment entries found.</td>

                    </tr>

                @endforelse

            </tbody>

        </x-data-table-card>



        <x-offcanvas title="Payment Entry">

            @include('payments._form')

        </x-offcanvas>

    </div>

</x-app-layout>

