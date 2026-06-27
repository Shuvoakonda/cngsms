<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\Payment;
use App\Models\Pump;
use App\Models\Purchase;
use App\Models\Vehicle;
use Illuminate\Support\Collection;

class ReportService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array{rows: Collection<int, Purchase>, totals: array<string, float|int>}
     */
    public function dailyPurchases(array $filters): array
    {
        $query = Purchase::query()
            ->with(['pump:id,name', 'vehicle:id,vehicle_number', 'driver:id,name'])
            ->orderBy('purchase_date')
            ->orderBy('id');

        $this->applyPurchaseFilters($query, $filters);

        $rows = $query->get();

        return [
            'rows' => $rows,
            'totals' => [
                'count' => $rows->count(),
                'quantity' => round((float) $rows->sum('quantity'), 2),
                'amount' => round((float) $rows->sum('amount'), 2),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function monthlyPurchaseSummary(array $filters): array
    {
        $month = $filters['month'] ?? now()->format('Y-m');
        [$year, $monthNum] = explode('-', $month);

        $purchases = Purchase::query()
            ->with(['pump:id,name', 'vehicle:id,vehicle_number'])
            ->whereYear('purchase_date', (int) $year)
            ->whereMonth('purchase_date', (int) $monthNum)
            ->get();

        $byPump = $purchases->groupBy('pump_id')->map(function (Collection $items) {
            $pump = $items->first()?->pump;

            return [
                'label' => $pump?->name ?? 'Unknown',
                'count' => $items->count(),
                'quantity' => round((float) $items->sum('quantity'), 2),
                'amount' => round((float) $items->sum('amount'), 2),
            ];
        })->sortByDesc('amount')->values();

        $byVehicle = $purchases->groupBy('vehicle_id')->map(function (Collection $items) {
            return [
                'label' => $items->first()?->displayVehicle() ?? 'Guest',
                'count' => $items->count(),
                'quantity' => round((float) $items->sum('quantity'), 2),
                'amount' => round((float) $items->sum('amount'), 2),
            ];
        })->sortByDesc('amount')->values();

        return [
            'month' => $month,
            'byPump' => $byPump,
            'byVehicle' => $byVehicle,
            'totals' => [
                'count' => $purchases->count(),
                'quantity' => round((float) $purchases->sum('quantity'), 2),
                'amount' => round((float) $purchases->sum('amount'), 2),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function pumpLedger(int $pumpId, ?string $dateFrom, ?string $dateTo): array
    {
        $pump = Pump::withTrashed()->findOrFail($pumpId);
        $running = (float) $pump->opening_balance;

        $entries = collect([
            [
                'date' => null,
                'reference' => 'OPENING',
                'description' => 'Opening balance',
                'debit' => $running > 0 ? $running : 0,
                'credit' => $running < 0 ? abs($running) : 0,
                'balance' => $running,
            ],
        ]);

        $purchases = Purchase::query()
            ->with('vehicle:id,vehicle_number')
            ->where('pump_id', $pumpId)
            ->when($dateFrom, fn ($q) => $q->whereDate('purchase_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('purchase_date', '<=', $dateTo))
            ->orderBy('purchase_date')
            ->orderBy('id')
            ->get()
            ->map(fn (Purchase $purchase) => [
                'sort_date' => $purchase->purchase_date->format('Y-m-d'),
                'date' => $purchase->purchase_date->format('d M Y'),
                'reference' => $purchase->slip_number,
                'description' => 'Purchase — '.$purchase->displayVehicle(),
                'debit' => (float) $purchase->amount,
                'credit' => 0.0,
                'type' => 'purchase',
            ]);

        $payments = Payment::query()
            ->where('pump_id', $pumpId)
            ->when($dateFrom, fn ($q) => $q->whereDate('payment_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('payment_date', '<=', $dateTo))
            ->orderBy('payment_date')
            ->orderBy('id')
            ->get()
            ->map(fn (Payment $payment) => [
                'sort_date' => $payment->payment_date->format('Y-m-d'),
                'date' => $payment->payment_date->format('d M Y'),
                'reference' => $payment->voucher_number,
                'description' => 'Payment — '.$payment->payment_method->label(),
                'debit' => 0.0,
                'credit' => (float) $payment->amount,
                'type' => 'payment',
            ]);

        $transactions = $purchases->concat($payments)->sortBy([
            ['sort_date', 'asc'],
            ['reference', 'asc'],
        ])->values();

        foreach ($transactions as $transaction) {
            $running = round($running + $transaction['debit'] - $transaction['credit'], 2);
            $entries->push([
                'date' => $transaction['date'],
                'reference' => $transaction['reference'],
                'description' => $transaction['description'],
                'debit' => $transaction['debit'],
                'credit' => $transaction['credit'],
                'balance' => $running,
            ]);
        }

        return [
            'pump' => $pump,
            'entries' => $entries,
            'closing_balance' => $running,
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function outstandingReport(): Collection
    {
        return Pump::withTrashed()
            ->withSum('purchases as purchases_total', 'amount')
            ->withSum('payments as payments_total', 'amount')
            ->orderBy('name')
            ->get()
            ->map(function (Pump $pump) {
                $purchases = (float) $pump->purchases_total;
                $payments = (float) $pump->payments_total;
                $due = round((float) $pump->opening_balance + $purchases - $payments, 2);

                return [
                    'pump' => $pump->name,
                    'opening_balance' => (float) $pump->opening_balance,
                    'total_purchase' => $purchases,
                    'total_payment' => $payments,
                    'due' => $due,
                    'credit_limit' => (float) $pump->credit_limit,
                    'over_limit' => $pump->credit_limit > 0 && $due > $pump->credit_limit,
                    'trashed' => $pump->trashed(),
                ];
            })
            ->filter(fn ($row) => ! $row['trashed'] || $row['due'] != 0)
            ->sortByDesc('due')
            ->values();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function vehicleWise(array $filters): Collection
    {
        $query = Purchase::query()->with('vehicle:id,vehicle_number');

        $this->applyPurchaseFilters($query, $filters);

        return $query->get()
            ->groupBy('vehicle_id')
            ->map(function (Collection $items) {
                return [
                    'vehicle' => $items->first()?->displayVehicle() ?? 'Guest',
                    'count' => $items->count(),
                    'quantity' => round((float) $items->sum('quantity'), 2),
                    'amount' => round((float) $items->sum('amount'), 2),
                ];
            })
            ->sortByDesc('amount')
            ->values();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function driverWise(array $filters): Collection
    {
        $query = Purchase::query()->with('driver:id,name');

        $this->applyPurchaseFilters($query, $filters);

        return $query->get()
            ->groupBy('driver_id')
            ->map(function (Collection $items) {
                return [
                    'driver' => $items->first()?->displayDriver() ?? 'Guest',
                    'count' => $items->count(),
                    'quantity' => round((float) $items->sum('quantity'), 2),
                    'amount' => round((float) $items->sum('amount'), 2),
                ];
            })
            ->sortByDesc('amount')
            ->values();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{rows: Collection<int, Payment>, totals: array<string, float|int>}
     */
    public function paymentReport(array $filters): array
    {
        $query = Payment::query()
            ->with('pump:id,name')
            ->orderBy('payment_date')
            ->orderBy('id');

        if (! empty($filters['date_from'])) {
            $query->whereDate('payment_date', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('payment_date', '<=', $filters['date_to']);
        }

        if (! empty($filters['pump_id'])) {
            $query->where('pump_id', $filters['pump_id']);
        }

        $rows = $query->get();

        return [
            'rows' => $rows,
            'totals' => [
                'count' => $rows->count(),
                'amount' => round((float) $rows->sum('amount'), 2),
            ],
        ];
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<Purchase>  $query
     * @param  array<string, mixed>  $filters
     */
    protected function applyPurchaseFilters($query, array $filters): void
    {
        if (! empty($filters['date_from'])) {
            $query->whereDate('purchase_date', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('purchase_date', '<=', $filters['date_to']);
        }

        if (! empty($filters['pump_id'])) {
            $query->where('pump_id', $filters['pump_id']);
        }

        if (! empty($filters['vehicle_id'])) {
            if ($filters['vehicle_id'] === 'guest') {
                $query->whereNull('vehicle_id');
            } else {
                $query->where('vehicle_id', $filters['vehicle_id']);
            }
        }
    }

    public function activePumps(): Collection
    {
        return Pump::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']);
    }

    public function activeVehicles(): Collection
    {
        return Vehicle::query()->where('status', 'active')->orderBy('vehicle_number')->get(['id', 'vehicle_number']);
    }
}
