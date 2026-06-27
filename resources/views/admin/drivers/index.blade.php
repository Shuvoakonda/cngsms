<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Drivers</h1>
                <p class="mt-1 text-sm text-slate-600">Drivers assigned to vehicles and purchase slips.</p>
            </div>
            <a href="{{ route('admin.drivers.index', ['create' => 1]) }}" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-teal-800 hover:shadow focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:ring-offset-1 active:scale-95">Add Driver</a>
        </div>
    </x-slot>

    <div x-data="{ activeTab: 'drivers' }" x-cloak>
        <div class="mb-6 flex border-b border-slate-200">
            <button @click="activeTab = 'drivers'"
                    class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors"
                    :class="activeTab === 'drivers' ? 'border-teal-700 text-teal-700' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'">
                Active Drivers
            </button>
            <button @click="activeTab = 'deleted'"
                    class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors"
                    :class="activeTab === 'deleted' ? 'border-teal-700 text-teal-700' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'">
                Deleted Drivers
            </button>
        </div>

        <div x-data="offcanvasCrud(@js($offcanvasConfig))">
            <div x-show="activeTab === 'drivers'">
                <x-data-table-card>
                    <thead>
                        <tr>
                            <th>Driver</th>
                            <th>Mobile</th>
                            <th>License</th>
                            <th>Vehicles</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($drivers as $driver)
                            <tr>
                                <td class="col-primary" data-label="Driver">
                                    <a href="{{ route('admin.drivers.show', $driver) }}" class="font-medium text-teal-700 hover:underline">{{ $driver->name }}</a>
                                    <p class="text-xs text-slate-500">{{ $driver->address ?: 'No address' }}</p>
                                </td>
                                <td data-label="Mobile">{{ $driver->mobile ?: '—' }}</td>
                                <td data-label="License">{{ $driver->license_number ?: '—' }}</td>
                                <td data-label="Vehicles">{{ $driver->vehicles_count }}</td>
                                <td class="col-actions text-right">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <a href="{{ route('admin.drivers.show', $driver) }}" class="rounded-lg px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-100">View</a>
                                        <a href="{{ route('admin.drivers.index', ['edit' => $driver->id]) }}" class="rounded-lg px-3 py-1.5 text-xs font-medium text-teal-700 hover:bg-teal-50">Edit</a>
                                        <form method="post" action="{{ route('admin.drivers.destroy', $driver) }}" onsubmit="return confirm('Delete this driver?')">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="rounded-lg px-3 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-50">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="data-table-empty-row">
                                <td colspan="5" class="data-table-empty">
                                    <p class="text-slate-500">No drivers yet.</p>
                                    <a href="{{ route('admin.drivers.index', ['create' => 1]) }}" class="mt-3 inline-block text-sm font-medium text-teal-700 hover:underline">Add your first driver</a>
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
                            <th>Driver</th>
                            <th>Mobile</th>
                            <th>License</th>
                            <th>Deleted At</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($trashedDrivers as $driver)
                            <tr>
                                <td class="col-primary" data-label="Driver">
                                    <span class="font-medium text-slate-900">{{ $driver->name }}</span>
                                    <p class="text-xs text-slate-500">{{ $driver->address ?: 'No address' }}</p>
                                </td>
                                <td data-label="Mobile">{{ $driver->mobile ?: '—' }}</td>
                                <td data-label="License">{{ $driver->license_number ?: '—' }}</td>
                                <td data-label="Deleted At">
                                    <span class="text-slate-600 text-sm">{{ $driver->deleted_at->format('d M Y, h:i A') }}</span>
                                </td>
                                <td class="col-actions text-right">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <form method="post" action="{{ route('admin.drivers.restore', $driver->id) }}" onsubmit="return confirm('Restore this driver?')">
                                            @csrf
                                            <button type="submit" class="rounded-lg px-3 py-1.5 text-xs font-medium text-teal-700 hover:bg-teal-50">Restore</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="data-table-empty-row">
                                <td colspan="5" class="data-table-empty">
                                    <p class="text-slate-500">No deleted drivers found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-data-table-card>
            </div>

            <x-offcanvas>
                @include('admin.drivers._form')
            </x-offcanvas>
        </div>
    </div>
</x-app-layout>