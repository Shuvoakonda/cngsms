<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Pump;
use App\Models\Purchase;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardService
{
    public function stats(): array
    {
        $today = today();
        $totalOutstanding = $this->totalOutstanding();
        $overallCreditLimit = (float) Pump::withTrashed()->sum('credit_limit');

        return [
            'today_purchase' => (float) Purchase::query()->whereDate('purchase_date', $today)->sum('amount'),
            'today_payment' => (float) Payment::query()->whereDate('payment_date', $today)->sum('amount'),
            'monthly_purchase' => (float) Purchase::query()
                ->whereYear('purchase_date', $today->year)
                ->whereMonth('purchase_date', $today->month)
                ->sum('amount'),
            'monthly_payment' => (float) Payment::query()
                ->whereYear('payment_date', $today->year)
                ->whereMonth('payment_date', $today->month)
                ->sum('amount'),
            'total_outstanding' => $totalOutstanding,
            'outstanding_over_threshold' => $overallCreditLimit > 0 && $totalOutstanding > $overallCreditLimit,
            'pumps_over_limit' => $this->pumpsOverCreditLimit()->count(),
        ];
    }

    public function totalOutstanding(): float
    {
        $opening = (float) Pump::withTrashed()->sum('opening_balance');
        $purchases = (float) Purchase::query()->sum('amount');
        $payments = (float) Payment::query()->sum('amount');

        return round($opening + $purchases - $payments, 2);
    }

    /**
     * @return array{labels: list<string>, purchases: list<float>, payments: list<float>}
     */
    public function monthlyComparison(int $months = 6): array
    {
        $labels = [];
        $purchases = [];
        $payments = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $labels[] = $date->format('M Y');

            $purchases[] = (float) Purchase::query()
                ->whereYear('purchase_date', $date->year)
                ->whereMonth('purchase_date', $date->month)
                ->sum('amount');

            $payments[] = (float) Payment::query()
                ->whereYear('payment_date', $date->year)
                ->whereMonth('payment_date', $date->month)
                ->sum('amount');
        }

        return compact('labels', 'purchases', 'payments');
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    public function pumpOutstanding(int $limit = 10): array
    {
        $pumps = Pump::withTrashed()
            ->withSum('purchases as purchases_total', 'amount')
            ->withSum('payments as payments_total', 'amount')
            ->get()
            ->map(fn (Pump $pump) => [
                'name' => $pump->name,
                'outstanding' => round((float) $pump->opening_balance + (float) $pump->purchases_total - (float) $pump->payments_total, 2),
                'trashed' => $pump->trashed(),
            ])
            ->filter(fn ($row) => ! $row['trashed'] || $row['outstanding'] != 0)
            ->sortByDesc('outstanding')
            ->take($limit)
            ->values();

        return [
            'labels' => $pumps->pluck('name')->all(),
            'values' => $pumps->pluck('outstanding')->all(),
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    public function outstandingTrend(int $months = 12): array
    {
        $labels = [];
        $values = [];
        $opening = (float) Pump::withTrashed()->sum('opening_balance');

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i)->endOfMonth();
            $labels[] = $date->format('M Y');

            $purchases = (float) Purchase::query()
                ->whereDate('purchase_date', '<=', $date)
                ->sum('amount');

            $payments = (float) Payment::query()
                ->whereDate('payment_date', '<=', $date)
                ->sum('amount');

            $values[] = round($opening + $purchases - $payments, 2);
        }

        return compact('labels', 'values');
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function recentTransactions(int $limit = 10): Collection
    {
        $purchases = Purchase::query()
            ->with(['pump:id,name', 'vehicle:id,vehicle_number'])
            ->latest('purchase_date')
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(fn (Purchase $purchase) => [
                'type' => 'purchase',
                'date' => $purchase->purchase_date->format('Y-m-d'),
                'label' => $purchase->slip_number,
                'pump' => $purchase->pump?->name,
                'vehicle' => $purchase->displayVehicle(),
                'amount' => (float) $purchase->amount,
                'direction' => 'debit',
            ]);

        $payments = Payment::query()
            ->with('pump:id,name')
            ->latest('payment_date')
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(fn (Payment $payment) => [
                'type' => 'payment',
                'date' => $payment->payment_date->format('Y-m-d'),
                'label' => $payment->voucher_number,
                'pump' => $payment->pump?->name,
                'vehicle' => null,
                'amount' => (float) $payment->amount,
                'direction' => 'credit',
            ]);

        return $purchases
            ->concat($payments)
            ->sortByDesc(fn (array $item) => $item['date'])
            ->take($limit)
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function topPumpsThisMonth(int $limit = 5): Collection
    {
        $start = now()->startOfMonth()->toDateString();
        $end = now()->endOfMonth()->toDateString();

        return Purchase::query()
            ->join('pumps', 'purchases.pump_id', '=', 'pumps.id')
            ->selectRaw('pumps.name as pump_name, SUM(purchases.amount) as total_amount, SUM(purchases.quantity) as total_quantity, COUNT(*) as purchase_count')
            ->whereBetween('purchase_date', [$start, $end])
            ->groupBy('pumps.id', 'pumps.name')
            ->orderByDesc('total_amount')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'pump' => $row->pump_name,
                'amount' => (float) $row->total_amount,
                'quantity' => (float) $row->total_quantity,
                'count' => (int) $row->purchase_count,
            ]);
    }

    /**
     * @return Collection<int, array<string, string>>
     */
    public function alerts(): Collection
    {
        $alerts = collect();

        foreach ($this->pumpsOverCreditLimit() as $pump) {
            $outstanding = round(
                (float) $pump->opening_balance + (float) $pump->purchases_total - (float) $pump->payments_total,
                2
            );

            $alerts->push([
                'type' => 'danger',
                'title' => 'Credit limit exceeded',
                'message' => sprintf(
                    '%s owes %s but the credit limit is %s.',
                    $pump->name,
                    number_format($outstanding, 2),
                    number_format((float) $pump->credit_limit, 2)
                ),
            ]);
        }

        if ($alerts->isEmpty()) {
            $alerts->push([
                'type' => 'success',
                'title' => 'All clear',
                'message' => 'No pumps are currently over their credit limit.',
            ]);
        }

        return $alerts;
    }

    /**
     * @return Collection<int, Pump>
     */
    public function pumpsOverCreditLimit(): Collection
    {
        return Pump::withTrashed()
            ->where('credit_limit', '>', 0)
            ->withSum('purchases as purchases_total', 'amount')
            ->withSum('payments as payments_total', 'amount')
            ->get()
            ->filter(function (Pump $pump) {
                $outstanding = round(
                    (float) $pump->opening_balance + (float) $pump->purchases_total - (float) $pump->payments_total,
                    2
                );

                return $outstanding > (float) $pump->credit_limit;
            })
            ->values();
    }

    public function formatMoney(float $amount): string
    {
        return number_format($amount, 2);
    }

    public function formatDate(string $date): string
    {
        return Carbon::parse($date)->format('d M Y');
    }
}
