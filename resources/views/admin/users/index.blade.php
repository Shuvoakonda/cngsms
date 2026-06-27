<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Users</h1>
                <p class="mt-1 text-sm text-slate-600">Manage system users, roles, and access permissions.</p>
            </div>
            <a href="{{ route('admin.users.index', ['create' => 1]) }}" class="inline-flex min-h-11 w-full shrink-0 items-center justify-center rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-teal-800 hover:shadow focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:ring-offset-1 active:scale-95 sm:w-auto">Add User</a>
        </div>
    </x-slot>

    <div x-data="{ activeTab: 'users' }" x-cloak>
        <div class="mb-6 flex border-b border-slate-200">
            <button @click="activeTab = 'users'"
                    class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors"
                    :class="activeTab === 'users' ? 'border-teal-700 text-teal-700' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'">
                Active Users
            </button>
            <button @click="activeTab = 'deleted'"
                    class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors"
                    :class="activeTab === 'deleted' ? 'border-teal-700 text-teal-700' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'">
                Deleted Users
            </button>
            <button @click="activeTab = 'roles'"
                    class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors"
                    :class="activeTab === 'roles' ? 'border-teal-700 text-teal-700' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'">
                Access Levels
            </button>
        </div>

        <div x-show="activeTab === 'roles'" x-transition.opacity>
            <x-admin.role-overview :users="$users" :role-permissions="$rolePermissions" :permission-labels="$permissionLabels" />
        </div>

        <div x-data="offcanvasCrud(@js($offcanvasConfig))" x-show="activeTab === 'users' || activeTab === 'deleted'" x-transition.opacity>
            
            <div x-show="activeTab === 'users'">
                <div class="mb-3">
                    <h2 class="text-lg font-semibold text-slate-900">Active Users</h2>
                    <p class="mt-1 text-sm text-slate-600">{{ $users->count() }} {{ str('user')->plural($users->count()) }} in the system.</p>
                </div>

                <x-data-table-card>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td class="col-primary" data-label="User">
                                    <p class="font-medium text-slate-900">{{ $user->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $user->email ?: 'No email' }}</p>
                                </td>
                                <td class="font-mono" data-label="Username">{{ $user->username }}</td>
                                <td data-label="Role">
                                    <span @class([
                                        'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium',
                                        'bg-teal-50 text-teal-700 ring-1 ring-teal-100' => $user->role === \App\Enums\UserRole::Administrator,
                                        'bg-sky-50 text-sky-700 ring-1 ring-sky-100' => $user->role === \App\Enums\UserRole::DataEntry,
                                    ])>{{ $user->role->label() }}</span>
                                </td>
                                <td data-label="Status">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $user->status === \App\Enums\UserStatus::Active ? 'bg-teal-50 text-teal-700 ring-1 ring-teal-100' : 'bg-slate-100 text-slate-600' }}">{{ $user->status->label() }}</span>
                                </td>
                                <td class="col-actions text-right">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <a href="{{ route('admin.users.index', ['edit' => $user->id]) }}" class="rounded-lg px-3 py-1.5 text-xs font-medium text-teal-700 hover:bg-teal-50">Edit</a>
                                        @if (! $user->is(auth()->user()))
                                            <form method="post" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete this user?')">
                                                @csrf
                                                @method('delete')
                                                <button type="submit" class="rounded-lg px-3 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-50">Delete</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="data-table-empty-row">
                                <td colspan="5" class="data-table-empty">No active users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-data-table-card>
            </div>

            <div x-show="activeTab === 'deleted'">
                <div class="mb-3">
                    <h2 class="text-lg font-semibold text-slate-900">Deleted Users</h2>
                    <p class="mt-1 text-sm text-slate-600">{{ $trashedUsers->count() }} {{ str('user')->plural($trashedUsers->count()) }} previously removed.</p>
                </div>

                <x-data-table-card>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Deleted At</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($trashedUsers as $user)
                            <tr>
                                <td class="col-primary" data-label="User">
                                    <p class="font-medium text-slate-900">{{ $user->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $user->email ?: 'No email' }}</p>
                                </td>
                                <td class="font-mono" data-label="Username">{{ $user->username }}</td>
                                <td data-label="Role">
                                    <span @class([
                                        'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium',
                                        'bg-teal-50 text-teal-700 ring-1 ring-teal-100' => $user->role === \App\Enums\UserRole::Administrator,
                                        'bg-sky-50 text-sky-700 ring-1 ring-sky-100' => $user->role === \App\Enums\UserRole::DataEntry,
                                    ])>{{ $user->role->label() }}</span>
                                </td>
                                <td data-label="Deleted At">
                                    <span class="text-slate-600 text-sm">{{ $user->deleted_at->format('d M Y, h:i A') }}</span>
                                </td>
                                <td class="col-actions text-right">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <form method="post" action="{{ route('admin.users.restore', $user) }}" onsubmit="return confirm('Restore this user?')">
                                            @csrf
                                            <button type="submit" class="rounded-lg px-3 py-1.5 text-xs font-medium text-teal-700 hover:bg-teal-50">Restore</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="data-table-empty-row">
                                <td colspan="5" class="data-table-empty">No deleted users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-data-table-card>
            </div>

            <x-offcanvas>
                @include('admin.users._form', compact('permissionLabels', 'rolePermissions'))
            </x-offcanvas>
        </div>
    </div>
</x-app-layout>