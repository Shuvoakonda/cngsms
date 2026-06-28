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

    public function outstanding(Request $request): View
    {
        $filters = $request->only(['date_from', 'date_to']);
        $rows = $this->reports->outstandingReport($filters);

        return view('reports.outstanding', [
            'filters' => $filters,
            'rows' => $rows,
            'totals' => [
                'entries' => $rows->sum('entries'),
                'due' => round($rows->sum('due'), 2),
                'advance' => round($rows->sum('advance'), 2),
            ],
            'chart' => [
                'labels' => $rows->where('due', '>', 0)->pluck('pump')->take(10)->values()->all(),
                'values' => $rows->where('due', '>', 0)->pluck('due')->take(10)->values()->all(),
            ],
        ]);
    }

    public function vehicleWise(Request $request): View
    {
        $filters = $request->only(['date_from', 'date_to']);
        $rows = $this->reports->vehicleWise($filters);
        $driverByPump = $this->reports->driverEntriesByPump($filters);

        return view('reports.vehicle-wise', compact('filters', 'rows', 'driverByPump'));
    }

    public function driverWise(Request $request): View
    {
        $filters = $request->only(['date_from', 'date_to']);
        $rows = $this->reports->driverWise($filters);
        $driverByPump = $this->reports->driverEntriesByPump($filters);

        return view('reports.driver-wise', compact('filters', 'rows', 'driverByPump'));
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
        ]))->concat($report['byPumpDriver']->map(fn ($row) => [
            'Pump' => 'Driver @ '.$row['pump'].': '.$row['driver'],
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

    public function exportOutstanding(Request $request): StreamedResponse
    {
        $filters = $request->only(['date_from', 'date_to']);
        $rows = $this->reports->outstandingReport($filters);
        $company = Company::current();

        return $this->excel->download(
            'pump-summary.xlsx',
            'Pump Summary Report',
            ['Pump', 'Entries', 'Purchase', 'Payment', 'Due', 'Advance'],
            $rows->map(fn ($row) => [
                $row['pump'],
                $row['entries'],
                $row['total_purchase'],
                $row['total_payment'],
                $row['due'],
                $row['advance'],
            ])->all(),
            $this->filterMeta($filters, ['As of' => now()->format('d M Y')]),
            [
                'Total Due' => number_format($rows->sum('due'), 2).' '.$company->currency,
                'Total Advance' => number_format($rows->sum('advance'), 2).' '.$company->currency,
            ],
            [3, 4, 5, 6],
        );
    }

    public function exportVehicleWise(Request $request): StreamedResponse
    {
        $filters = $request->only(['date_from', 'date_to']);
        $rows = $this->reports->vehicleWise($filters);
        $driverByPump = $this->reports->driverEntriesByPump($filters);
        $company = Company::current();

        $exportRows = $rows->map(fn ($row) => [
            'Vehicle: '.$row['vehicle'],
            $row['count'],
            $row['quantity'],
            $row['amount'],
        ])->concat($driverByPump->map(fn ($row) => [
            $row['pump'].' — '.$row['driver'],
            $row['count'],
            $row['quantity'],
            $row['amount'],
        ]))->values();

        return $this->excel->download(
            'vehicle-wise.xlsx',
            'Vehicle-wise Purchase Report',
            ['Group', 'Entries', 'Quantity', 'Amount'],
            $exportRows->all(),
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
        $driverByPump = $this->reports->driverEntriesByPump($filters);
        $company = Company::current();

        $exportRows = $rows->map(fn ($row) => [
            'Driver: '.$row['driver'],
            $row['count'],
            $row['quantity'],
            $row['amount'],
        ])->concat($driverByPump->map(fn ($row) => [
            $row['pump'].' — '.$row['driver'],
            $row['count'],
            $row['quantity'],
            $row['amount'],
        ]))->values();

        return $this->excel->download(
            'driver-wise.xlsx',
            'Driver-wise Purchase Report',
            ['Group', 'Entries', 'Quantity', 'Amount'],
            $exportRows->all(),
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
            ['Date', 'Type', 'Voucher', 'Pump', 'Method', 'Amount'],
            $report['rows']->map(fn ($row) => [
                $row->payment_date->format('Y-m-d'),
                $row->type->label(),
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
            [6],
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
