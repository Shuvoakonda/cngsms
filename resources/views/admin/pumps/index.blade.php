<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Pumps</h1>
                <p class="mt-1 text-sm text-slate-600">Gas stations where the fleet buys CNG on credit.</p>
            </div>
            <a href="{{ route('admin.pumps.index', ['create' => 1]) }}" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-teal-800 hover:shadow focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:ring-offset-1 active:scale-95">
                Add Pump
            </a>
        </div>
    </x-slot>

    <div x-data="{ activeTab: 'pumps' }" x-cloak>
        <div class="mb-6 flex border-b border-slate-200">
            <button @click="activeTab = 'pumps'"
                    class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors"
                    :class="activeTab === 'pumps' ? 'border-teal-700 text-teal-700' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'">
                Active Pumps
            </button>
            <button @click="activeTab = 'deleted'"
                    class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors"
                    :class="activeTab === 'deleted' ? 'border-teal-700 text-teal-700' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'">
                Deleted Pumps
            </button>
        </div>

        <div x-data="offcanvasCrud(@js($offcanvasConfig))">
            <div x-show="activeTab === 'pumps'">
                <x-data-table-card>
                    <thead>
                        <tr>
                            <th>Pump</th>
                            <th>Contact</th>
                            <th>Credit Limit</th>
                            <th>Outstanding</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pumps as $pump)
                            @php
                                $outstanding = round((float) $pump->opening_balance + (float) $pump->purchases_total - (float) $pump->payments_total, 2);
                            @endphp
                            <tr>
                                <td class="col-primary" data-label="Pump">
                                    <a href="{{ route('admin.pumps.show', $pump) }}" class="font-medium text-teal-700 hover:underline">{{ $pump->name }}</a>
                                    <p class="text-xs text-slate-500">{{ $pump->address ?: 'No address' }}</p>
                                </td>
                                <td data-label="Contact">
                                    {{ $pump->contact_person ?: '—' }}
                                    @if ($pump->mobile)
                                        <br><span class="text-xs text-slate-500">{{ $pump->mobile }}</span>
                                    @endif
                                </td>
                                <td data-label="Credit Limit">{{ number_format((float) $pump->credit_limit, 2) }}</td>
                                <td @class([
                                    'font-medium',
                                    'text-rose-700' => $pump->credit_limit > 0 && $outstanding > $pump->credit_limit,
                                    'text-slate-900' => ! ($pump->credit_limit > 0 && $outstanding > $pump->credit_limit),
                                ]) data-label="Outstanding">{{ number_format($outstanding, 2) }}</td>
                                <td data-label="Status">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $pump->status === \App\Enums\PumpStatus::Active ? 'bg-teal-50 text-teal-700 ring-1 ring-teal-100' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $pump->status->label() }}
                                    </span>
                                </td>
                                <td class="col-actions text-right">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <a href="{{ route('admin.pumps.show', $pump) }}" class="rounded-lg px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-100">View</a>
                                        <a href="{{ route('admin.pumps.index', ['edit' => $pump->id]) }}" class="rounded-lg px-3 py-1.5 text-xs font-medium text-teal-700 hover:bg-teal-50">Edit</a>
                                        <form method="post" action="{{ route('admin.pumps.destroy', $pump) }}" onsubmit="return confirm('Delete this pump?')">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="rounded-lg px-3 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-50">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="data-table-empty-row">
                                <td colspan="6" class="data-table-empty">
                                    <p class="text-slate-500">No pumps yet.</p>
                                    <a href="{{ route('admin.pumps.index', ['create' => 1]) }}" class="mt-3 inline-block text-sm font-medium text-teal-700 hover:underline">Add your first pump</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-data-table-card>
            </div>

            <div x-show="activeTab === 'deleted'" style="display: none;">
                <x-data-table-card>
                    <thead>
                        <tr>
                            <th>Pump</th>
                            <th>Contact</th>
                            <th>Credit Limit</th>
                            <th>Outstanding</th>
                            <th>Deleted At</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($trashedPumps as $pump)
                            @php
                                $outstanding = round((float) $pump->opening_balance + (float) $pump->purchases_total - (float) $pump->payments_total, 2);
                            @endphp
                            <tr>
                                <td class="col-primary" data-label="Pump">
                                    <span class="font-medium text-slate-900">{{ $pump->name }}</span>
                                    <p class="text-xs text-slate-500">{{ $pump->address ?: 'No address' }}</p>
                                </td>
                                <td data-label="Contact">
                                    {{ $pump->contact_person ?: '—' }}
                                    @if ($pump->mobile)
                                        <br><span class="text-xs text-slate-500">{{ $pump->mobile }}</span>
                                    @endif
                                </td>
                                <td data-label="Credit Limit">{{ number_format((float) $pump->credit_limit, 2) }}</td>
                                <td @class([
                                    'font-medium',
                                    'text-rose-700' => $pump->credit_limit > 0 && $outstanding > $pump->credit_limit,
                                    'text-slate-900' => ! ($pump->credit_limit > 0 && $outstanding > $pump->credit_limit),
                                ]) data-label="Outstanding">{{ number_format($outstanding, 2) }}</td>
                                <td data-label="Deleted At">
                                    <span class="text-slate-600 text-sm">{{ $pump->deleted_at->format('d M Y, h:i A') }}</span>
                                </td>
                                <td class="col-actions text-right">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <form method="post" action="{{ route('admin.pumps.restore', $pump->id) }}" onsubmit="return confirm('Restore this pump?')">
                                            @csrf
                                            <button type="submit" class="rounded-lg px-3 py-1.5 text-xs font-medium text-teal-700 hover:bg-teal-50">Restore</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="data-table-empty-row">
                                <td colspan="6" class="data-table-empty">
                                    <p class="text-slate-500">No deleted pumps found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-data-table-card>
            </div>

            <x-offcanvas>
                @include('admin.pumps._form')
            </x-offcanvas>
        </div>
    </div>
</x-app-layout>