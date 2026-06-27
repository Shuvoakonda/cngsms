<?php

namespace App\Enums;

enum Permission: string
{
    case ViewDashboard = 'view_dashboard';
    case ManagePurchases = 'manage_purchases';
    case ManagePayments = 'manage_payments';
    case ViewReports = 'view_reports';
    case ExportReports = 'export_reports';
    case ManageMasterData = 'manage_master_data';
    case ManageSettings = 'manage_settings';
    case ManageUsers = 'manage_users';
    case DeleteRecords = 'delete_records';

    public function label(): string
    {
        return match ($this) {
            self::ViewDashboard => 'View dashboard',
            self::ManagePurchases => 'Manage purchases',
            self::ManagePayments => 'Manage payments',
            self::ViewReports => 'View reports',
            self::ExportReports => 'Export reports',
            self::ManageMasterData => 'Manage pumps, vehicles, and drivers',
            self::ManageSettings => 'Manage company settings',
            self::ManageUsers => 'Manage users',
            self::DeleteRecords => 'Delete records',
        };
    }
}
