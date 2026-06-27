<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        Company::query()->create([
            'name' => 'Marwha Enterprise',
            'address' => 'Dhaka, Bangladesh',
            'currency' => 'BDT',
            'date_format' => 'd-m-Y',
            'quantity_unit' => 'M3',
        ]);

        User::query()->create([
            'name' => 'System Administrator',
            'username' => 'admin',
            'email' => 'admin@cngsms.local',
            'password' => 'password',
            'role' => UserRole::Administrator,
            'status' => UserStatus::Active,
        ]);

        User::query()->create([
            'name' => 'Data Entry Staff',
            'username' => 'dataentry',
            'email' => 'dataentry@cngsms.local',
            'password' => 'password',
            'role' => UserRole::DataEntry,
            'status' => UserStatus::Active,
        ]);

        // $this->call(DashboardDemoSeeder::class);
    }
}
