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
            'byPumpDriver' => $this->driverEntriesByPump(['month' => $month]),
            'totals' => [
                'count' => $purchases->count(),
                'quantity' => round((float) $purchases->sum('quantity'), 2),
                'amount' => round((float) $purchases->sum('amount'), 2),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function driverEntriesByPump(array $filters): Collection
    {
        $query = Purchase::query()
            ->with(['pump:id,name', 'driver:id,name']);

        if (! empty($filters['month'])) {
            [$year, $monthNum] = explode('-', $filters['month']);
            $query->whereYear('purchase_date', (int) $year)
                ->whereMonth('purchase_date', (int) $monthNum);
        } else {
            $this->applyPurchaseFilters($query, $filters);
        }

        return $query->get()
            ->groupBy(fn (Purchase $purchase) => $purchase->pump_id.'|'.($purchase->driver_id ?? 'guest'))
            ->map(function (Collection $items) {
                $first = $items->first();

                return [
                    'pump' => $first?->pump?->name ?? 'Unknown',
                    'driver' => $first?->displayDriver() ?? 'Guest',
                    'count' => $items->count(),
                    'quantity' => round((float) $items->sum('quantity'), 2),
                    'amount' => round((float) $items->sum('amount'), 2),
                ];
            })
            ->sortBy([
                ['pump', 'asc'],
                ['driver', 'asc'],
            ])
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    public function pumpLedger(int $pumpId, ?string $dateFrom, ?string $dateTo): array
    {
        $pump = Pump::withTrashed()->findOrFail($pumpId);
        $running = 0.0;
        $entries = collect();

        if ((float) $pump->opening_balance > 0) {
            $running = round($running + (float) $pump->opening_balance, 2);
            $entries->push([
                'date' => null,
                'reference' => 'OPENING',
                'description' => 'Opening due',
                'debit' => (float) $pump->opening_balance,
                'credit' => 0.0,
                'balance' => $running,
            ]);
        }

        if ((float) $pump->opening_advance > 0) {
            $running = round($running - (float) $pump->opening_advance, 2);
            $entries->push([
                'date' => null,
                'reference' => 'OPENING',
                'description' => 'Opening advance',
                'debit' => 0.0,
                'credit' => (float) $pump->opening_advance,
                'balance' => $running,
            ]);
        }

        if ($entries->isEmpty()) {
            $entries->push([
                'date' => null,
                'reference' => 'OPENING',
                'description' => 'Opening balance',
                'debit' => 0.0,
                'credit' => 0.0,
                'balance' => 0.0,
            ]);
        }

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
                'description' => $payment->type->label().' — '.$payment->payment_method->label(),
                'debit' => 0.0,
                'credit' => (float) $payment->amount,
                'type' => $payment->type->value,
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
     * @param  array<string, mixed>  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function outstandingReport(array $filters = []): Collection
    {
        $hasDateFilter = ! empty($filters['date_from']) || ! empty($filters['date_to']);

        return Pump::withTrashed()
            ->withSum(['purchases as purchases_total' => fn ($query) => $this->applyTransactionDateFilters($query, $filters, 'purchase_date')], 'amount')
            ->withSum(['payments as payments_total' => fn ($query) => $this->applyTransactionDateFilters($query, $filters, 'payment_date')], 'amount')
            ->withCount(['purchases as entry_count' => fn ($query) => $this->applyTransactionDateFilters($query, $filters, 'purchase_date')])
            ->orderBy('name')
            ->get()
            ->map(function (Pump $pump) {
                $purchases = (float) $pump->purchases_total;
                $payments = (float) $pump->payments_total;

                return [
                    'pump' => $pump->name,
                    'entries' => (int) $pump->entry_count,
                    'opening_balance' => (float) $pump->opening_balance,
                    'opening_advance' => (float) $pump->opening_advance,
                    'total_purchase' => $purchases,
                    'total_payment' => $payments,
                    'due' => $pump->dueAmount($purchases, $payments),
                    'advance' => $pump->advanceBalance($purchases, $payments),
                    'credit_limit' => (float) $pump->credit_limit,
                    'over_limit' => $pump->credit_limit > 0 && $pump->dueAmount($purchases, $payments) > $pump->credit_limit,
                    'trashed' => $pump->trashed(),
                ];
            })
            ->filter(function (array $row) use ($hasDateFilter) {
                if ($row['trashed']) {
                    return $row['due'] != 0 || $row['advance'] != 0;
                }

                if (! $hasDateFilter) {
                    return true;
                }

                return $row['entries'] > 0
                    || $row['total_payment'] > 0
                    || $row['due'] > 0
                    || $row['advance'] > 0;
            })
            ->sortByDesc(fn ($row) => $row['due'] > 0 ? $row['due'] : -$row['advance'])
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
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @param  array<string, mixed>  $filters
     */
    protected function applyTransactionDateFilters($query, array $filters, string $dateColumn): void
    {
        if (! empty($filters['date_from'])) {
            $query->whereDate($dateColumn, '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate($dateColumn, '<=', $filters['date_to']);
        }
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
