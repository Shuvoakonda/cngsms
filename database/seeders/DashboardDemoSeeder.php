<?php

namespace Database\Seeders;

use App\Enums\PaymentMethod;
use App\Enums\PumpStatus;
use App\Enums\VehicleStatus;
use App\Models\Driver;
use App\Models\Payment;
use App\Models\Pump;
use App\Models\Purchase;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class DashboardDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('username', 'admin')->first();

        $pumps = collect([
            ['name' => 'Green Road CNG', 'credit_limit' => 150000, 'opening_balance' => 25000],
            ['name' => 'Mirpur Pump Station', 'credit_limit' => 120000, 'opening_balance' => 10000],
            ['name' => 'Uttara Auto Gas', 'credit_limit' => 80000, 'opening_balance' => 0],
            ['name' => 'Motijheel CNG Point', 'credit_limit' => 50000, 'opening_balance' => 5000],
            ['name' => 'Bashundhara Pump', 'credit_limit' => 100000, 'opening_balance' => 15000],
        ])->map(fn (array $data) => Pump::query()->firstOrCreate(
            ['name' => $data['name']],
            [
                'address' => 'Dhaka, Bangladesh',
                'contact_person' => 'Station Manager',
                'mobile' => '01700000000',
                'opening_balance' => $data['opening_balance'],
                'credit_limit' => $data['credit_limit'],
                'status' => PumpStatus::Active,
            ]
        ));

        $drivers = collect([
            ['name' => 'Karim Uddin', 'mobile' => '01811111111'],
            ['name' => 'Rahim Mia', 'mobile' => '01822222222'],
            ['name' => 'Jamal Hossain', 'mobile' => '01833333333'],
        ])->mapWithKeys(fn (array $data) => [
            $data['mobile'] => Driver::query()->firstOrCreate(
                ['mobile' => $data['mobile']],
                ['name' => $data['name']]
            ),
        ]);

        $vehicles = collect([
            ['vehicle_number' => 'DHK-101', 'registration_number' => 'DHA-1234', 'driver_mobile' => '01811111111'],
            ['vehicle_number' => 'DHK-102', 'registration_number' => 'DHA-1235', 'driver_mobile' => '01822222222'],
            ['vehicle_number' => 'DHK-103', 'registration_number' => 'DHA-1236', 'driver_mobile' => '01833333333'],
            ['vehicle_number' => 'DHK-104', 'registration_number' => 'DHA-1237', 'driver_mobile' => '01811111111'],
        ])->map(fn (array $data) => Vehicle::query()->firstOrCreate(
            ['vehicle_number' => $data['vehicle_number']],
            [
                'registration_number' => $data['registration_number'],
                'driver_id' => $drivers[$data['driver_mobile']]->id,
                'type' => 'CNG Auto Rickshaw',
                'status' => VehicleStatus::Active,
            ]
        ));

        $vehicleList = $vehicles->values();
        $driverList = $drivers->values();

        for ($monthOffset = 5; $monthOffset >= 0; $monthOffset--) {
            $month = now()->subMonths($monthOffset);
            $purchaseCount = 10 + ($monthOffset % 3);

            foreach (range(1, $purchaseCount) as $index) {
                $pump = $pumps[$index % $pumps->count()];
                $vehicle = $vehicleList[$index % $vehicleList->count()];
                $driver = $driverList->firstWhere('id', $vehicle->driver_id) ?? $driverList->first();
                $quantity = 80 + (($index * 7) % 61);
                $rate = 95 + (($index * 3) % 16);
                $day = min(28, 2 + ($index * 2));
                $slipNumber = sprintf('%s-%03d', $month->format('Ym'), ($monthOffset * 100) + $index);

                Purchase::query()->firstOrCreate(
                    [
                        'pump_id' => $pump->id,
                        'slip_number' => $slipNumber,
                    ],
                    [
                        'purchase_date' => $month->copy()->day($day),
                        'vehicle_id' => $vehicle->id,
                        'driver_id' => $driver->id,
                        'quantity' => $quantity,
                        'rate' => $rate,
                        'amount' => round($quantity * $rate, 2),
                        'remarks' => null,
                        'created_by' => $admin?->id,
                    ]
                );
            }

            foreach (range(1, 3) as $index) {
                $pump = $pumps[($index + $monthOffset) % $pumps->count()];
                $voucherNumber = sprintf('PV-%s-%02d', $month->format('Ym'), $index);

                Payment::query()->firstOrCreate(
                    ['voucher_number' => $voucherNumber],
                    [
                        'payment_date' => $month->copy()->day(5 + ($index * 6)),
                        'pump_id' => $pump->id,
                        'payment_method' => $index % 2 === 0 ? PaymentMethod::Bank : PaymentMethod::Cash,
                        'amount' => 15000 + ($index * 7500) + ($monthOffset * 1000),
                        'reference_number' => $index % 2 === 0 ? 'TRX'.(100000 + ($monthOffset * 10) + $index) : null,
                        'remarks' => null,
                        'created_by' => $admin?->id,
                    ]
                );
            }
        }

        Purchase::query()->firstOrCreate(
            [
                'pump_id' => $pumps[0]->id,
                'slip_number' => today()->format('Ymd').'-001',
            ],
            [
                'purchase_date' => today(),
                'vehicle_id' => $vehicleList[0]->id,
                'driver_id' => $driverList[0]->id,
                'quantity' => 120,
                'rate' => 102,
                'amount' => 12240,
                'created_by' => $admin?->id,
            ]
        );

        Payment::query()->firstOrCreate(
            ['voucher_number' => 'PV-'.today()->format('Ymd').'-01'],
            [
                'payment_date' => today(),
                'pump_id' => $pumps[1]->id,
                'payment_method' => PaymentMethod::Cash,
                'amount' => 20000,
                'created_by' => $admin?->id,
            ]
        );
    }
}
