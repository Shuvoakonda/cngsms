<?php

namespace App\Services;

use App\Enums\Permission;
use App\Models\Driver;
use App\Models\Payment;
use App\Models\Pump;
use App\Models\Purchase;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Collection;

class GlobalSearchService
{
    /**
     * @return Collection<int, array{id: string, type: string, label: string, subtitle: string|null, url: string}>
     */
    public function search(User $user, string $query, int $limit = 12): Collection
    {
        $query = trim($query);

        if (strlen($query) < 2) {
            return collect();
        }

        $results = collect();

        if ($user->hasPermission(Permission::ManageMasterData)) {
            $results = $results->concat($this->searchPumps($query));
            $results = $results->concat($this->searchVehicles($query));
            $results = $results->concat($this->searchDrivers($query));
        }

        if ($user->hasPermission(Permission::ManagePurchases)) {
            $results = $results->concat($this->searchPurchases($query));
        }

        if ($user->hasPermission(Permission::ManagePayments)) {
            $results = $results->concat($this->searchPayments($query));
        }

        if ($user->hasPermission(Permission::ManageUsers)) {
            $results = $results->concat($this->searchUsers($query));
        }

        return $results->take($limit)->values();
    }

    /**
     * @return Collection<int, array{id: string, type: string, label: string, subtitle: string|null, url: string}>
     */
    protected function searchPumps(string $query): Collection
    {
        return Pump::query()
            ->where(function ($builder) use ($query) {
                $builder->where('name', 'like', "%{$query}%")
                    ->orWhere('address', 'like', "%{$query}%")
                    ->orWhere('contact_person', 'like', "%{$query}%")
                    ->orWhere('mobile', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->limit(5)
            ->get()
            ->map(fn (Pump $pump) => [
                'id' => 'pump-'.$pump->id,
                'type' => 'Pump',
                'label' => $pump->name,
                'subtitle' => $pump->address ?: $pump->mobile,
                'url' => route('admin.pumps.show', $pump),
            ]);
    }

    /**
     * @return Collection<int, array{id: string, type: string, label: string, subtitle: string|null, url: string}>
     */
    protected function searchVehicles(string $query): Collection
    {
        return Vehicle::query()
            ->with('driver:id,name')
            ->where(function ($builder) use ($query) {
                $builder->where('vehicle_number', 'like', "%{$query}%")
                    ->orWhere('registration_number', 'like', "%{$query}%")
                    ->orWhere('type', 'like', "%{$query}%");
            })
            ->orderBy('vehicle_number')
            ->limit(5)
            ->get()
            ->map(fn (Vehicle $vehicle) => [
                'id' => 'vehicle-'.$vehicle->id,
                'type' => 'Vehicle',
                'label' => $vehicle->vehicle_number,
                'subtitle' => $vehicle->driver?->name,
                'url' => route('admin.vehicles.show', $vehicle),
            ]);
    }

    /**
     * @return Collection<int, array{id: string, type: string, label: string, subtitle: string|null, url: string}>
     */
    protected function searchDrivers(string $query): Collection
    {
        return Driver::query()
            ->where(function ($builder) use ($query) {
                $builder->where('name', 'like', "%{$query}%")
                    ->orWhere('mobile', 'like', "%{$query}%")
                    ->orWhere('license_number', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->limit(5)
            ->get()
            ->map(fn (Driver $driver) => [
                'id' => 'driver-'.$driver->id,
                'type' => 'Driver',
                'label' => $driver->name,
                'subtitle' => $driver->mobile,
                'url' => route('admin.drivers.show', $driver),
            ]);
    }

    /**
     * @return Collection<int, array{id: string, type: string, label: string, subtitle: string|null, url: string}>
     */
    protected function searchPurchases(string $query): Collection
    {
        return Purchase::query()
            ->with(['pump:id,name', 'vehicle:id,vehicle_number'])
            ->where('slip_number', 'like', "%{$query}%")
            ->latest('purchase_date')
            ->limit(5)
            ->get()
            ->map(fn (Purchase $purchase) => [
                'id' => 'purchase-'.$purchase->id,
                'type' => 'Purchase Slip',
                'label' => $purchase->slip_number,
                'subtitle' => $purchase->purchase_date->format('d M Y').' · '.$purchase->pump?->name,
                'url' => route('purchases.index', ['edit' => $purchase->id]),
            ]);
    }

    /**
     * @return Collection<int, array{id: string, type: string, label: string, subtitle: string|null, url: string}>
     */
    protected function searchPayments(string $query): Collection
    {
        return Payment::query()
            ->with('pump:id,name')
            ->where(function ($builder) use ($query) {
                $builder->where('voucher_number', 'like', "%{$query}%")
                    ->orWhere('reference_number', 'like', "%{$query}%");
            })
            ->latest('payment_date')
            ->limit(5)
            ->get()
            ->map(fn (Payment $payment) => [
                'id' => 'payment-'.$payment->id,
                'type' => 'Payment',
                'label' => $payment->voucher_number,
                'subtitle' => $payment->payment_date->format('d M Y').' · '.$payment->pump?->name,
                'url' => route('payments.index', ['edit' => $payment->id]),
            ]);
    }

    /**
     * @return Collection<int, array{id: string, type: string, label: string, subtitle: string|null, url: string}>
     */
    protected function searchUsers(string $query): Collection
    {
        return User::query()
            ->where(function ($builder) use ($query) {
                $builder->where('name', 'like', "%{$query}%")
                    ->orWhere('username', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->limit(5)
            ->get()
            ->map(fn (User $user) => [
                'id' => 'user-'.$user->id,
                'type' => 'User',
                'label' => $user->name,
                'subtitle' => $user->username.' · '.$user->role->label(),
                'url' => route('admin.users.index', ['edit' => $user->id]),
            ]);
    }
}
