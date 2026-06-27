<?php

namespace App\Models;

use App\Enums\Permission;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Support\RolePermissions;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'username', 'email', 'password', 'role', 'status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, \Illuminate\Database\Eloquent\SoftDeletes;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'status' => UserStatus::class,
        ];
    }

    public function isAdministrator(): bool
    {
        return $this->role === UserRole::Administrator;
    }

    public function isDataEntry(): bool
    {
        return $this->role === UserRole::DataEntry;
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }

    public function hasPermission(Permission $permission): bool
    {
        return RolePermissions::roleHas($this->role, $permission);
    }

    /**
     * @return array<int, Permission>
     */
    public function permissions(): array
    {
        return RolePermissions::for($this->role);
    }

    public function canDeleteRecords(): bool
    {
        return $this->hasPermission(Permission::DeleteRecords);
    }

    public function canManageSettings(): bool
    {
        return $this->hasPermission(Permission::ManageSettings);
    }

    public function canManageUsers(): bool
    {
        return $this->hasPermission(Permission::ManageUsers);
    }
}
