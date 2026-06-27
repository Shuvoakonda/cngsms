@props(['users', 'rolePermissions', 'permissionLabels'])

@php
    $allPermissions = array_keys($permissionLabels);
@endphp

<section {{ $attributes->merge(['class' => 'mb-8']) }}>
    <div class="mb-4">
        <h2 class="text-lg font-semibold text-slate-900">Access Levels</h2>
        <p class="mt-1 text-sm text-slate-600">Predefined roles and what each role can do in the system.</p>
    </div>

    <div class="mb-6 grid gap-4 lg:grid-cols-2">
        @foreach (\App\Enums\UserRole::cases() as $role)
            @php
                $permissions = $rolePermissions[$role->value];
                $userCount = $users->where('role', $role)->count();
                $isAdmin = $role === \App\Enums\UserRole::Administrator;
            @endphp
            <div @class(['role-card', 'role-card-admin' => $isAdmin, 'role-card-entry' => ! $isAdmin])>
                <div class="role-card-header">
                    <div class="role-card-icon">
                        @if ($isAdmin)
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                            </svg>
                        @else
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="font-semibold text-slate-900">{{ $role->label() }}</h3>
                            <span @class([
                                'inline-flex rounded-full px-2 py-0.5 text-xs font-medium',
                                'bg-teal-50 text-teal-700 ring-1 ring-teal-100' => $isAdmin,
                                'bg-sky-50 text-sky-700 ring-1 ring-sky-100' => ! $isAdmin,
                            ])>{{ count($permissions) }} permissions</span>
                        </div>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ $userCount }} {{ str('user')->plural($userCount) }} assigned
                        </p>
                    </div>
                </div>
                <div class="role-card-body">
                    <ul class="role-permission-grid">
                        @foreach ($permissions as $permissionKey)
                            <li class="role-permission-item">
                                <svg class="role-permission-check" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>{{ $permissionLabels[$permissionKey] ?? $permissionKey }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endforeach
    </div>

    <div class="access-matrix">
        <div class="border-b border-slate-200 px-4 py-4 lg:px-5">
            <h3 class="font-semibold text-slate-900">Permission Comparison</h3>
            <p class="mt-1 text-sm text-slate-500">Quick side-by-side view of role capabilities.</p>
        </div>
        <div class="access-matrix-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Permission</th>
                        @foreach (\App\Enums\UserRole::cases() as $role)
                            <th class="text-center">{{ $role->label() }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($allPermissions as $permissionKey)
                        <tr>
                            <td>{{ $permissionLabels[$permissionKey] ?? $permissionKey }}</td>
                            @foreach (\App\Enums\UserRole::cases() as $role)
                                @php $hasPermission = in_array($permissionKey, $rolePermissions[$role->value], true); @endphp
                                <td class="text-center">
                                    @if ($hasPermission)
                                        <span class="access-check" aria-label="Allowed">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </span>
                                    @else
                                        <span class="access-dash" aria-label="Not allowed">—</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>
