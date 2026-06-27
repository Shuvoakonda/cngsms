<?php

namespace App\Support;

use App\Enums\Permission;
use App\Enums\UserRole;

class RolePermissions
{
    /**
     * @return array<int, Permission>
     */
    public static function for(UserRole $role): array
    {
        return match ($role) {
            UserRole::Administrator => Permission::cases(),
            UserRole::DataEntry => [
                Permission::ViewDashboard,
                Permission::ManagePurchases,
                Permission::ManagePayments,
                Permission::ViewReports,
                Permission::ExportReports,
            ],
        };
    }

    /**
     * @return array<int, string>
     */
    public static function keysFor(UserRole $role): array
    {
        return array_map(
            fn (Permission $permission) => $permission->value,
            self::for($role),
        );
    }

    public static function roleHas(UserRole $role, Permission $permission): bool
    {
        return in_array($permission, self::for($role), true);
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        $labels = [];

        foreach (Permission::cases() as $permission) {
            $labels[$permission->value] = $permission->label();
        }

        return $labels;
    }
}
