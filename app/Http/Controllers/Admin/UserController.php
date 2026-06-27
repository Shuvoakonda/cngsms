<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\User;
use App\Support\OffcanvasDeepLinks;
use App\Support\RolePermissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()->orderBy('name')->get();
        $trashedUsers = User::onlyTrashed()->orderBy('name')->get();
        $isEdit = old('_method') === 'patch';
        $editId = old('_edit_id');

        $offcanvasConfig = OffcanvasDeepLinks::apply([
            'openOnLoad' => $isEdit || session()->has('errors'),
            'initialMode' => $isEdit ? 'edit' : 'create',
            'initialTitle' => $isEdit ? 'Edit User' : 'Add User',
            'initialEditUrl' => $editId ? route('admin.users.update', $editId) : '',
            'createTitle' => 'Add User',
            'editTitle' => 'Edit User',
            'storeUrl' => route('admin.users.store'),
            'updateUrlTemplate' => route('admin.users.update', ['user' => '__ID__']),
            'defaults' => [
                'name' => '',
                'username' => '',
                'email' => '',
                'password' => '',
                'password_confirmation' => '',
                'role' => \App\Enums\UserRole::DataEntry->value,
                'status' => \App\Enums\UserStatus::Active->value,
            ],
        ], $request, $users, fn (User $user) => [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'password' => '',
            'password_confirmation' => '',
            'role' => $user->role->value,
            'status' => $user->status->value,
        ]);

        return view('admin.users.index', [
            'users' => $users,
            'trashedUsers' => $trashedUsers,
            'offcanvasConfig' => $offcanvasConfig,
            'permissionLabels' => RolePermissions::labels(),
            'rolePermissions' => [
                \App\Enums\UserRole::Administrator->value => RolePermissions::keysFor(\App\Enums\UserRole::Administrator),
                \App\Enums\UserRole::DataEntry->value => RolePermissions::keysFor(\App\Enums\UserRole::DataEntry),
            ],
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        User::query()->create($request->safe()->except(['password_confirmation']));

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User created successfully.');
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $data = $request->safe()->except(['password', 'password_confirmation']);

        if ($request->filled('password')) {
            $data['password'] = $request->validated('password');
        }

        if ($user->is($request->user()) && $data['status'] !== $user->status->value) {
            return redirect()
                ->route('admin.users.index', ['edit' => $user->id])
                ->with('error', 'You cannot change your own account status.');
        }

        if ($user->is($request->user()) && $data['role'] !== $user->role->value) {
            return redirect()
                ->route('admin.users.index', ['edit' => $user->id])
                ->with('error', 'You cannot change your own role.');
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_unless(auth()->user()?->canManageUsers(), 403);

        if ($user->is(auth()->user())) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User deleted successfully.');
    }

    public function restore(int $id): RedirectResponse
    {
        abort_unless(auth()->user()?->canManageUsers(), 403);

        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User restored successfully.');
    }
}
