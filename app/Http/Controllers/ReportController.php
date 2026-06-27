<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Pump;
use App\Services\ExcelReportExportService;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        private ReportService $reports,
        private ExcelReportExportService $excel,
    ) {}

    public function index(): View
    {
        return view('reports.index');
    }

    public function dailyPurchases(Request $request): View
    {
        $filters = $request->only(['date_from', 'date_to', 'pump_id', 'vehicle_id']);
        $report = $this->reports->dailyPurchases($filters);

        return view('reports.daily-purchases', [
            'filters' => $filters,
            'rows' => $report['rows'],
            'totals' => $report['totals'],
            'pumps' => $this->reports->activePumps(),
            'vehicles' => $this->reports->activeVehicles(),
        ]);
    }

    public function monthlyPurchases(Request $request): View
    {
        $filters = ['month' => $request->input('month', now()->format('Y-m'))];
        $report = $this->reports->monthlyPurchaseSummary($filters);

        return view('reports.monthly-purchases', [
            'filters' => $filters,
            'report' => $report,
        ]);
    }

    public function pumpLedger(Request $request): View
    {
        $pumpId = (int) $request->input('pump_id', $this->reports->activePumps()->first()?->id);
        $filters = $request->only(['date_from', 'date_to', 'pump_id']);
        $filters['pump_id'] = $pumpId;

        $report = $pumpId
            ? $this->reports->pumpLedger($pumpId, $filters['date_from'] ?? null, $filters['date_to'] ?? null)
            : ['pump' => null, 'entries' => collect(), 'closing_balance' => 0];

        return view('reports.pump-ledger', [
            'filters' => $filters,
            'report' => $report,
            'pumps' => $this->reports->activePumps(),
        ]);
    }

    public function outstanding(): View
    {
        $rows = $this->reports->outstandingReport();

        return view('reports.outstanding', [
            'rows' => $rows,
            'chart' => [
                'labels' => $rows->pluck('pump')->take(10)->all(),
                'values' => $rows->pluck('due')->take(10)->all(),
            ],
        ]);
    }

    public function vehicleWise(Request $request): View
    {
        $filters = $request->only(['date_from', 'date_to']);
        $rows = $this->reports->vehicleWise($filters);

        return view('reports.vehicle-wise', compact('filters', 'rows'));
    }

    public function driverWise(Request $request): View
    {
        $filters = $request->only(['date_from', 'date_to']);
        $rows = $this->reports->driverWise($filters);

        return view('reports.driver-wise', compact('filters', 'rows'));
    }

    public function payments(Request $request): View
    {
        $filters = $request->only(['date_from', 'date_to', 'pump_id']);
        $report = $this->reports->paymentReport($filters);

        return view('reports.payments', [
            'filters' => $filters,
            'rows' => $report['rows'],
            'totals' => $report['totals'],
            'pumps' => $this->reports->activePumps(),
        ]);
    }

    public function exportDailyPurchases(Request $request): StreamedResponse
    {
        $filters = $request->only(['date_from', 'date_to', 'pump_id', 'vehicle_id']);
        $report = $this->reports->dailyPurchases($filters);
        $company = Company::current();

        return $this->excel->download(
            'daily-purchases.xlsx',
            'Daily Purchase Report',
            ['Date', 'Slip', 'Pump', 'Vehicle', 'Driver', 'Quantity', 'Rate', 'Amount'],
            $report['rows']->map(fn ($row) => [
                $row->purchase_date->format('Y-m-d'),
                $row->slip_number,
                $row->pump?->name,
                $row->displayVehicle(),
                $row->displayDriver(),
                (float) $row->quantity,
                (float) $row->rate,
                (float) $row->amount,
            ])->all(),
            $this->filterMeta($filters),
            [
                'Entries' => $report['totals']['count'],
                'Quantity' => number_format($report['totals']['quantity'], 2).' '.$company->quantity_unit,
                'Amount' => number_format($report['totals']['amount'], 2).' '.$company->currency,
            ],
            [6, 7, 8],
        );
    }

    public function exportMonthlyPurchases(Request $request): StreamedResponse
    {
        $filters = ['month' => $request->input('month', now()->format('Y-m'))];
        $report = $this->reports->monthlyPurchaseSummary($filters);
        $company = Company::current();

        $rows = $report['byPump']->map(fn ($row) => [
            'Pump' => $row['label'],
            'Entries' => $row['count'],
            'Quantity' => $row['quantity'],
            'Amount' => $row['amount'],
        ])->concat($report['byVehicle']->map(fn ($row) => [
            'Pump' => 'Vehicle: '.$row['label'],
            'Entries' => $row['count'],
            'Quantity' => $row['quantity'],
            'Amount' => $row['amount'],
        ]))->values();

        return $this->excel->download(
            'monthly-purchases.xlsx',
            'Monthly Purchase Summary',
            ['Group', 'Entries', 'Quantity', 'Amount'],
            $rows->map(fn ($row) => array_values($row))->all(),
            $this->filterMeta($filters),
            [
                'Entries' => $report['totals']['count'],
                'Quantity' => number_format($report['totals']['quantity'], 2).' '.$company->quantity_unit,
                'Amount' => number_format($report['totals']['amount'], 2).' '.$company->currency,
            ],
            [2, 3, 4],
        );
    }

    public function exportPumpLedger(Request $request): StreamedResponse
    {
        $pumpId = (int) $request->input('pump_id', $this->reports->activePumps()->first()?->id);
        $filters = $request->only(['date_from', 'date_to', 'pump_id']);
        $report = $this->reports->pumpLedger($pumpId, $filters['date_from'] ?? null, $filters['date_to'] ?? null);
        $company = Company::current();

        return $this->excel->download(
            'pump-ledger.xlsx',
            'Pump Ledger — '.($report['pump']?->name ?? 'Unknown'),
            ['Date', 'Reference', 'Description', 'Debit', 'Credit', 'Balance'],
            $report['entries']->map(fn ($entry) => [
                $entry['date'] ?? '—',
                $entry['reference'],
                $entry['description'],
                $entry['debit'] ?: '',
                $entry['credit'] ?: '',
                $entry['balance'],
            ])->all(),
            $this->filterMeta($filters, ['Pump' => $report['pump']?->name]),
            ['Closing Balance' => number_format($report['closing_balance'], 2).' '.$company->currency],
            [4, 5, 6],
        );
    }

    public function exportOutstanding(): StreamedResponse
    {
        $rows = $this->reports->outstandingReport();
        $company = Company::current();

        return $this->excel->download(
            'outstanding-due.xlsx',
            'Outstanding Due Report',
            ['Pump', 'Opening', 'Purchase', 'Payment', 'Due'],
            $rows->map(fn ($row) => [
                $row['pump'],
                $row['opening_balance'],
                $row['total_purchase'],
                $row['total_payment'],
                $row['due'],
            ])->all(),
            $this->filterMeta([], ['As of' => now()->format('d M Y')]),
            ['Total Due' => number_format($rows->sum('due'), 2).' '.$company->currency],
            [2, 3, 4, 5],
        );
    }

    public function exportVehicleWise(Request $request): StreamedResponse
    {
        $filters = $request->only(['date_from', 'date_to']);
        $rows = $this->reports->vehicleWise($filters);
        $company = Company::current();

        return $this->excel->download(
            'vehicle-wise.xlsx',
            'Vehicle-wise Purchase Report',
            ['Vehicle', 'Entries', 'Quantity', 'Amount'],
            $rows->map(fn ($row) => [$row['vehicle'], $row['count'], $row['quantity'], $row['amount']])->all(),
            $this->filterMeta($filters),
            [
                'Entries' => $rows->sum(fn ($row) => $row['count']),
                'Amount' => number_format($rows->sum(fn ($row) => $row['amount']), 2).' '.$company->currency,
            ],
            [2, 3, 4],
        );
    }

    public function exportDriverWise(Request $request): StreamedResponse
    {
        $filters = $request->only(['date_from', 'date_to']);
        $rows = $this->reports->driverWise($filters);
        $company = Company::current();

        return $this->excel->download(
            'driver-wise.xlsx',
            'Driver-wise Purchase Report',
            ['Driver', 'Entries', 'Quantity', 'Amount'],
            $rows->map(fn ($row) => [$row['driver'], $row['count'], $row['quantity'], $row['amount']])->all(),
            $this->filterMeta($filters),
            [
                'Entries' => $rows->sum(fn ($row) => $row['count']),
                'Amount' => number_format($rows->sum(fn ($row) => $row['amount']), 2).' '.$company->currency,
            ],
            [2, 3, 4],
        );
    }

    public function exportPayments(Request $request): StreamedResponse
    {
        $filters = $request->only(['date_from', 'date_to', 'pump_id']);
        $report = $this->reports->paymentReport($filters);
        $company = Company::current();

        return $this->excel->download(
            'payments.xlsx',
            'Payment Report',
            ['Date', 'Voucher', 'Pump', 'Method', 'Amount'],
            $report['rows']->map(fn ($row) => [
                $row->payment_date->format('Y-m-d'),
                $row->voucher_number,
                $row->pump?->name,
                $row->payment_method->label(),
                (float) $row->amount,
            ])->all(),
            $this->filterMeta($filters),
            [
                'Payments' => $report['totals']['count'],
                'Amount' => number_format($report['totals']['amount'], 2).' '.$company->currency,
            ],
            [5],
        );
    }

    /**
     * @param  array<string, mixed>  $filters
     * @param  array<string, mixed>  $extra
     * @return array<int, string>
     */
    protected function filterMeta(array $filters, array $extra = []): array
    {
        $meta = [];

        foreach ($extra as $label => $value) {
            if ($value) {
                $meta[] = "{$label}: {$value}";
            }
        }

        if (! empty($filters['date_from']) || ! empty($filters['date_to'])) {
            $from = $filters['date_from'] ?? 'Start';
            $to = $filters['date_to'] ?? 'Today';
            $meta[] = "Period: {$from} to {$to}";
        }

        if (! empty($filters['month'])) {
            $meta[] = 'Month: '.$filters['month'];
        }

        if (! empty($filters['pump_id'])) {
            $pump = Pump::withTrashed()->find($filters['pump_id']);
            $meta[] = 'Pump: '.($pump?->name ?? $filters['pump_id']);
        }

        if (! empty($filters['vehicle_id'])) {
            $meta[] = 'Vehicle ID: '.$filters['vehicle_id'];
        }

        return $meta;
    }
}
