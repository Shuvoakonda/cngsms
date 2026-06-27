<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Vehicles</h1>
                <p class="mt-1 text-sm text-slate-600">Fleet vehicles used on daily CNG purchase slips.</p>
            </div>
            <a href="{{ route('admin.vehicles.index', ['create' => 1]) }}" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-teal-800 hover:shadow focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:ring-offset-1 active:scale-95">Add Vehicle</a>
        </div>
    </x-slot>

    <div x-data="{ activeTab: 'vehicles' }" x-cloak>
        <div class="mb-6 flex border-b border-slate-200">
            <button @click="activeTab = 'vehicles'"
                    class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors"
                    :class="activeTab === 'vehicles' ? 'border-teal-700 text-teal-700' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'">
                Active Vehicles
            </button>
            <button @click="activeTab = 'deleted'"
                    class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors"
                    :class="activeTab === 'deleted' ? 'border-teal-700 text-teal-700' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'">
                Deleted Vehicles
            </button>
        </div>

        <div x-data="offcanvasCrud(@js($offcanvasConfig))">
            <div x-show="activeTab === 'vehicles'">
                <x-data-table-card>
                    <thead>
                        <tr>
                            <th>Vehicle</th>
                            <th>Registration</th>
                            <th>Driver</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($vehicles as $vehicle)
                            <tr>
                                <td class="col-primary" data-label="Vehicle">
                                    <a href="{{ route('admin.vehicles.show', $vehicle) }}" class="font-medium text-teal-700 hover:underline">{{ $vehicle->vehicle_number }}</a>
                                    <p class="text-xs text-slate-500">{{ $vehicle->type ?: 'Vehicle' }}</p>
                                </td>
                                <td data-label="Registration">{{ $vehicle->registration_number ?: '—' }}</td>
                                <td data-label="Driver">
                                    @if ($vehicle->driver)
                                        <a href="{{ route('admin.drivers.show', $vehicle->driver) }}" class="text-teal-700 hover:underline">{{ $vehicle->driver->name }}</a>
                                    @else
                                        Unassigned
                                    @endif
                                </td>
                                <td data-label="Status">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $vehicle->status === \App\Enums\VehicleStatus::Active ? 'bg-teal-50 text-teal-700 ring-1 ring-teal-100' : 'bg-slate-100 text-slate-600' }}">{{ $vehicle->status->label() }}</span>
                                </td>
                                <td class="col-actions text-right">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <a href="{{ route('admin.vehicles.show', $vehicle) }}" class="rounded-lg px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-100">View</a>
                                        <a href="{{ route('admin.vehicles.index', ['edit' => $vehicle->id]) }}" class="rounded-lg px-3 py-1.5 text-xs font-medium text-teal-700 hover:bg-teal-50">Edit</a>
                                        <form method="post" action="{{ route('admin.vehicles.destroy', $vehicle) }}" onsubmit="return confirm('Delete this vehicle?')">
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
                                    <p class="text-slate-500">No vehicles yet.</p>
                                    <a href="{{ route('admin.vehicles.index', ['create' => 1]) }}" class="mt-3 inline-block text-sm font-medium text-teal-700 hover:underline">Add your first vehicle</a>
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
                            <th>Vehicle</th>
                            <th>Registration</th>
                            <th>Driver</th>
                            <th>Deleted At</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($trashedVehicles as $vehicle)
                            <tr>
                                <td class="col-primary" data-label="Vehicle">
                                    <span class="font-medium text-slate-900">{{ $vehicle->vehicle_number }}</span>
                                    <p class="text-xs text-slate-500">{{ $vehicle->type ?: 'Vehicle' }}</p>
                                </td>
                                <td data-label="Registration">{{ $vehicle->registration_number ?: '—' }}</td>
                                <td data-label="Driver">
                                    @if ($vehicle->driver)
                                        <span class="text-slate-700">{{ $vehicle->driver->name }}</span>
                                    @else
                                        Unassigned
                                    @endif
                                </td>
                                <td data-label="Deleted At">
                                    <span class="text-slate-600 text-sm">{{ $vehicle->deleted_at->format('d M Y, h:i A') }}</span>
                                </td>
                                <td class="col-actions text-right">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <form method="post" action="{{ route('admin.vehicles.restore', $vehicle->id) }}" onsubmit="return confirm('Restore this vehicle?')">
                                            @csrf
                                            <button type="submit" class="rounded-lg px-3 py-1.5 text-xs font-medium text-teal-700 hover:bg-teal-50">Restore</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="data-table-empty-row">
                                <td colspan="5" class="data-table-empty">
                                    <p class="text-slate-500">No deleted vehicles found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-data-table-card>
            </div>

            <x-offcanvas>
                @include('admin.vehicles._form', ['drivers' => $drivers])
            </x-offcanvas>
        </div>
    </div>
</x-app-layout>