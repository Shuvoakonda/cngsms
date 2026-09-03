<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Pump;
use App\Services\ExcelReportExportService;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
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
        $filters = $request->only(['date_from', 'date_to', 'pump_id', 'vehicle_id', 'fuel_type', 'status']);
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
        $filters = [
            'month' => $request->input('month', now()->format('Y-m')),
            'fuel_type' => $request->input('fuel_type'),
            'status' => $request->input('status'),
        ];
        $report = $this->reports->monthlyPurchaseSummary($filters);

        return view('reports.monthly-purchases', [
            'filters' => $filters,
            'report' => [
                ...$report,
                'byPump' => $report['byPump'],
                'byVehicle' => $report['byVehicle'],
                'byPumpDriver' => $report['byPumpDriver'],
            ],
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
            'report' => [
                ...$report,
                'entries' => $report['entries'],
            ],
            'pumps' => $this->reports->activePumps(),
        ]);
    }

    public function outstanding(Request $request): View
    {
        $filters = $request->only(['date_from', 'date_to']);
        $rows = $this->reports->outstandingReport($filters);

        return view('reports.outstanding', [
            'filters' => $filters,
            // send full collection (no pagination) so the view can render all rows with scrolling
            'rows' => $rows,
            'totals' => [
                'entries' => $rows->sum('entries'),
                'discount' => round($rows->sum('discount'), 2),
                'bonus' => round($rows->sum('bonus'), 2),
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
        $totals = [
            'amount' => round($rows->sum(fn ($row) => $row['amount']), 2),
        ];
        $rows = $rows; // keep full collection for scrolling/printing
        $driverByPump = $driverByPump; // keep full collection for scrolling/printing

        return view('reports.vehicle-wise', compact('filters', 'rows', 'driverByPump', 'totals'));
    }

    public function driverWise(Request $request): View
    {
        $filters = $request->only(['date_from', 'date_to']);
        $rows = $this->reports->driverWise($filters);
        $driverByPump = $this->reports->driverEntriesByPump($filters);
        $totals = [
            'amount' => round($rows->sum(fn ($row) => $row['amount']), 2),
        ];

        return view('reports.driver-wise', compact('filters', 'rows', 'driverByPump', 'totals'));
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
        $filters = $request->only(['date_from', 'date_to', 'pump_id', 'vehicle_id', 'fuel_type', 'status']);
        $report = $this->reports->dailyPurchases($filters);
        $company = Company::current();

        return $this->excel->download(
            'daily-purchases.xlsx',
            'Daily Purchase Report',
            ['Date', 'Slip', 'Fuel', 'Status', 'Pump', 'Vehicle', 'Driver', 'Quantity', 'Rate', 'Discount', 'Bonus', 'Amount'],
            $report['rows']->map(fn ($row) => [
                $row->purchase_date->format('Y-m-d'),
                $row->slip_number,
                strtoupper($row->fuel_type?->value ?? 'cng'),
                ucfirst($row->status?->value ?? 'unsold'),
                $row->pump?->name,
                $row->displayVehicle(),
                $row->displayDriver(),
                (float) $row->quantity,
                (float) $row->rate,
                $row->discountAmount(),
                $row->bonusAmount(),
                (float) $row->amount,
            ])->all(),
            $this->filterMeta($filters),
            [
                'Entries' => $report['totals']['count'],
                'Quantity' => number_format($report['totals']['quantity'], 2).' '.$company->quantity_unit,
                'Discount' => number_format($report['totals']['discount'], 2).' '.$company->currency,
                'Bonus' => number_format($report['totals']['bonus'], 2).' '.$company->currency,
                'Amount' => number_format($report['totals']['amount'], 2).' '.$company->currency,
            ],
            [7, 8, 9, 10, 11, 12],
        );
    }

    public function exportDailyPurchasesPdf(Request $request): Response
    {
        $filters = $request->only(['date_from', 'date_to', 'pump_id', 'vehicle_id', 'fuel_type', 'status']);
        $report = $this->reports->dailyPurchases($filters);

        $pdf = Pdf::loadView('reports.pdf.report', [
            'title' => 'Daily Purchase Report',
            'meta' => $this->filterMeta($filters),
            'summary' => 'Total amount: '.number_format($report['totals']['amount'], 2).' '.Company::current()->currency,
            'tables' => [
                [
                    'title' => 'Daily Purchases',
                    'columns' => ['Date', 'Slip', 'Fuel', 'Status', 'Pump', 'Vehicle', 'Driver', 'Qty', 'Rate', 'Discount', 'Bonus', 'Amount'],
                    'rows' => $report['rows']->map(fn ($row) => [
                        $row->purchase_date->format('d M Y'),
                        $row->slip_number,
                        strtoupper($row->fuel_type?->value ?? 'cng'),
                        ucfirst($row->status?->value ?? 'unsold'),
                        $row->pump?->name,
                        $row->displayVehicle(),
                        $row->displayDriver(),
                        number_format((float) $row->quantity, 2),
                        number_format((float) $row->rate, 2),
                        number_format($row->discountAmount(), 2),
                        number_format($row->bonusAmount(), 2),
                        number_format((float) $row->amount, 2),
                    ])->all(),
                ],
            ],
        ]);

        return $pdf->download('daily-purchases.pdf');
    }

    public function exportMonthlyPurchases(Request $request): StreamedResponse
    {
        $filters = [
            'month' => $request->input('month', now()->format('Y-m')),
            'fuel_type' => $request->input('fuel_type'),
            'status' => $request->input('status'),
        ];
        $report = $this->reports->monthlyPurchaseSummary($filters);
        $company = Company::current();

        $rows = $report['byPump']->map(fn ($row) => [
            'Pump: '.$row['label'],
            $row['count'],
            $row['quantity'],
            $row['rate'],
            $row['discount'],
            $row['bonus'],
            $row['amount'],
        ])->concat($report['byVehicle']->map(fn ($row) => [
            'Vehicle: '.$row['label'],
            $row['count'],
            $row['quantity'],
            $row['rate'],
            $row['discount'],
            $row['bonus'],
            $row['amount'],
        ]))->values();

        return $this->excel->download(
            'monthly-purchases.xlsx',
            'Monthly Purchase Summary',
            ['Group', 'Entries', 'Quantity', 'Rate', 'Discount', 'Bonus', 'Amount'],
            $rows->all(),
            $this->filterMeta($filters),
            [
                'Entries' => $report['totals']['count'],
                'Sold' => $report['totals']['sold_count'],
                'Unsold' => $report['totals']['unsold_count'],
                'Quantity' => number_format($report['totals']['quantity'], 2).' '.$company->quantity_unit,
                'Amount' => number_format($report['totals']['amount'], 2).' '.$company->currency,
            ],
            [2, 3, 4, 5, 6, 7],
        );
    }

    public function exportMonthlyPurchasesPdf(Request $request): Response
    {
        $filters = [
            'month' => $request->input('month', now()->format('Y-m')),
            'fuel_type' => $request->input('fuel_type'),
            'status' => $request->input('status'),
        ];
        $report = $this->reports->monthlyPurchaseSummary($filters);

        $tables = [
            [
                'title' => 'By Pump',
                'columns' => ['Pump', 'Entries', 'Qty', 'Rate', 'Discount', 'Bonus', 'Amount'],
                'rows' => $report['byPump']->map(fn ($row) => [
                    $row['label'],
                    $row['count'],
                    number_format($row['quantity'], 2),
                    number_format($row['rate'], 2),
                    number_format($row['discount'], 2),
                    number_format($row['bonus'], 2),
                    number_format($row['amount'], 2),
                ])->all(),
            ],
            [
                'title' => 'By Vehicle',
                'columns' => ['Vehicle', 'Entries', 'Qty', 'Rate', 'Discount', 'Bonus', 'Amount'],
                'rows' => $report['byVehicle']->map(fn ($row) => [
                    $row['label'],
                    $row['count'],
                    number_format($row['quantity'], 2),
                    number_format($row['rate'], 2),
                    number_format($row['discount'], 2),
                    number_format($row['bonus'], 2),
                    number_format($row['amount'], 2),
                ])->all(),
            ],
            [
                'title' => 'Driver Entries by Pump',
                'columns' => ['Pump', 'Driver', 'Entries', 'Qty', 'Rate', 'Discount', 'Bonus', 'Amount'],
                'rows' => $report['byPumpDriver']->map(fn ($row) => [
                    $row['pump'],
                    $row['driver'],
                    $row['count'],
                    number_format($row['quantity'], 2),
                    number_format($row['rate'], 2),
                    number_format($row['discount'], 2),
                    number_format($row['bonus'], 2),
                    number_format($row['amount'], 2),
                ])->all(),
            ],
        ];

        $pdf = Pdf::loadView('reports.pdf.report', [
            'title' => 'Monthly Purchase Summary',
            'meta' => $this->filterMeta($filters),
            'summary' => 'Total amount: '.number_format($report['totals']['amount'], 2).' '.Company::current()->currency
                .' | CNG: '.number_format($report['totals']['cng_amount'], 2).' '.Company::current()->currency
                .' | Diesel: '.number_format($report['totals']['diesel_amount'], 2).' '.Company::current()->currency,
            'tables' => $tables,
        ]);

        return $pdf->download('monthly-purchases.pdf');
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
            ['Date', 'Reference', 'Description', 'Discount', 'Bonus', 'Debit', 'Credit', 'Balance'],
            $report['entries']->map(fn ($entry) => [
                $entry['date'] ?? '—',
                $entry['reference'],
                $entry['description'],
                $entry['discount'] ?? '',
                $entry['bonus'] ?? '',
                $entry['debit'] ?: '',
                $entry['credit'] ?: '',
                $entry['balance'],
            ])->all(),
            $this->filterMeta($filters, ['Pump' => $report['pump']?->name]),
            ['Closing Balance' => number_format($report['closing_balance'], 2).' '.$company->currency],
            [6, 7, 8],
        );
    }

    public function exportPumpLedgerPdf(Request $request): Response
    {
        $pumpId = (int) $request->input('pump_id', $this->reports->activePumps()->first()?->id);
        $filters = $request->only(['date_from', 'date_to', 'pump_id']);
        $report = $this->reports->pumpLedger($pumpId, $filters['date_from'] ?? null, $filters['date_to'] ?? null);

        $pdf = Pdf::loadView('reports.pdf.report', [
            'title' => 'Pump Ledger',
            'meta' => $this->filterMeta($filters, ['Pump' => $report['pump']?->name]),
            'summary' => 'Closing balance: '.number_format($report['closing_balance'], 2).' '.Company::current()->currency,
            'tables' => [
                [
                    'title' => 'Ledger Entries',
                    'columns' => ['Date', 'Reference', 'Description', 'Discount', 'Bonus', 'Debit', 'Credit', 'Balance'],
                    'rows' => collect($report['entries'])->map(fn ($entry) => [
                        $entry['date'] ?? '—',
                        $entry['reference'],
                        $entry['description'],
                        $entry['discount'] ?? '',
                        $entry['bonus'] ?? '',
                        $entry['debit'] ? number_format($entry['debit'], 2) : '',
                        $entry['credit'] ? number_format($entry['credit'], 2) : '',
                        number_format($entry['balance'], 2),
                    ])->all(),
                ],
            ],
        ]);

        return $pdf->download('pump-ledger.pdf');
    }

    public function exportOutstanding(Request $request): StreamedResponse
    {
        $filters = $request->only(['date_from', 'date_to']);
        $rows = $this->reports->outstandingReport($filters);
        $company = Company::current();

        return $this->excel->download(
            'pump-summary.xlsx',
            'Pump Summary Report',
            ['Pump', 'Entries', 'Purchase', 'Discount', 'Bonus', 'Payment', 'Due', 'Advance'],
            $rows->map(fn ($row) => [
                $row['pump'],
                $row['entries'],
                $row['total_purchase'],
                $row['discount'],
                $row['bonus'],
                $row['total_payment'],
                $row['due'],
                $row['advance'],
            ])->all(),
            $this->filterMeta($filters, ['As of' => now()->format('d M Y')]),
            [
                'Total Discount' => number_format($rows->sum('discount'), 2).' '.$company->currency,
                'Total Bonus' => number_format($rows->sum('bonus'), 2).' '.$company->currency,
                'Total Due' => number_format($rows->sum('due'), 2).' '.$company->currency,
                'Total Advance' => number_format($rows->sum('advance'), 2).' '.$company->currency,
            ],
            [3, 4, 5, 6, 7, 8],
        );
    }

    public function exportOutstandingPdf(Request $request): Response
    {
        $filters = $request->only(['date_from', 'date_to']);
        $rows = $this->reports->outstandingReport($filters);

        $pdf = Pdf::loadView('reports.pdf.report', [
            'title' => 'Pump Summary Report',
            'meta' => $this->filterMeta($filters, ['As of' => now()->format('d M Y')]),
            'summary' => 'Total due: '.number_format($rows->sum('due'), 2).' '.Company::current()->currency.' | Total advance: '.number_format($rows->sum('advance'), 2).' '.Company::current()->currency,
            'tables' => [
                [
                    'title' => 'Pump Summary',
                    'columns' => ['Pump', 'Entries', 'Purchase', 'Discount', 'Bonus', 'Payment', 'Due', 'Advance'],
                    'rows' => $rows->map(fn ($row) => [
                        $row['pump'],
                        $row['entries'],
                        number_format($row['total_purchase'], 2),
                        number_format($row['discount'], 2),
                        number_format($row['bonus'], 2),
                        number_format($row['total_payment'], 2),
                        number_format($row['due'], 2),
                        number_format($row['advance'], 2),
                    ])->all(),
                ],
            ],
        ]);

        return $pdf->download('pump-summary.pdf');
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
            $row['rate'],
            $row['discount'],
            $row['bonus'],
            $row['amount'],
        ])->concat($driverByPump->map(fn ($row) => [
            $row['pump'].' — '.$row['driver'],
            $row['count'],
            $row['quantity'],
            $row['rate'],
            $row['discount'],
            $row['bonus'],
            $row['amount'],
        ]))->values();

        return $this->excel->download(
            'vehicle-wise.xlsx',
            'Vehicle-wise Purchase Report',
            ['Group', 'Entries', 'Quantity', 'Rate', 'Discount', 'Bonus', 'Amount'],
            $exportRows->all(),
            $this->filterMeta($filters),
            [
                'Entries' => $rows->sum(fn ($row) => $row['count']),
                'Discount' => number_format($rows->sum(fn ($row) => $row['discount']), 2).' '.$company->currency,
                'Bonus' => number_format($rows->sum(fn ($row) => $row['bonus']), 2).' '.$company->currency,
                'Amount' => number_format($rows->sum(fn ($row) => $row['amount']), 2).' '.$company->currency,
            ],
            [2, 3, 4, 5, 6, 7],
        );
    }

    public function exportVehicleWisePdf(Request $request): Response
    {
        $filters = $request->only(['date_from', 'date_to']);
        $rows = $this->reports->vehicleWise($filters);
        $driverByPump = $this->reports->driverEntriesByPump($filters);

        $tables = [
            [
                'title' => 'By Vehicle',
                'columns' => ['Vehicle', 'Entries', 'Qty', 'Rate', 'Discount', 'Bonus', 'Amount'],
                'rows' => $rows->map(fn ($row) => [
                    $row['vehicle'],
                    $row['count'],
                    number_format($row['quantity'], 2),
                    number_format($row['rate'], 2),
                    number_format($row['discount'], 2),
                    number_format($row['bonus'], 2),
                    number_format($row['amount'], 2),
                ])->all(),
            ],
            [
                'title' => 'Driver Entries by Pump',
                'columns' => ['Pump', 'Driver', 'Entries', 'Qty', 'Rate', 'Discount', 'Bonus', 'Amount'],
                'rows' => $driverByPump->map(fn ($row) => [
                    $row['pump'],
                    $row['driver'],
                    $row['count'],
                    number_format($row['quantity'], 2),
                    number_format($row['rate'], 2),
                    number_format($row['discount'], 2),
                    number_format($row['bonus'], 2),
                    number_format($row['amount'], 2),
                ])->all(),
            ],
        ];

        $pdf = Pdf::loadView('reports.pdf.report', [
            'title' => 'Vehicle-wise Purchase Report',
            'meta' => $this->filterMeta($filters),
            'summary' => 'Total amount: '.number_format($rows->sum(fn ($row) => $row['amount']), 2).' '.Company::current()->currency,
            'tables' => $tables,
        ]);

        return $pdf->download('vehicle-wise.pdf');
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
            $row['rate'],
            $row['discount'],
            $row['bonus'],
            $row['amount'],
        ])->concat($driverByPump->map(fn ($row) => [
            $row['pump'].' — '.$row['driver'],
            $row['count'],
            $row['quantity'],
            $row['rate'],
            $row['discount'],
            $row['bonus'],
            $row['amount'],
        ]))->values();

        return $this->excel->download(
            'driver-wise.xlsx',
            'Driver-wise Purchase Report',
            ['Group', 'Entries', 'Quantity', 'Rate', 'Discount', 'Bonus', 'Amount'],
            $exportRows->all(),
            $this->filterMeta($filters),
            [
                'Entries' => $rows->sum(fn ($row) => $row['count']),
                'Discount' => number_format($rows->sum(fn ($row) => $row['discount']), 2).' '.$company->currency,
                'Bonus' => number_format($rows->sum(fn ($row) => $row['bonus']), 2).' '.$company->currency,
                'Amount' => number_format($rows->sum(fn ($row) => $row['amount']), 2).' '.$company->currency,
            ],
            [2, 3, 4, 5, 6, 7],
        );
    }

    public function exportDriverWisePdf(Request $request): Response
    {
        $filters = $request->only(['date_from', 'date_to']);
        $rows = $this->reports->driverWise($filters);
        $driverByPump = $this->reports->driverEntriesByPump($filters);

        $tables = [
            [
                'title' => 'By Driver',
                'columns' => ['Driver', 'Entries', 'Qty', 'Rate', 'Discount', 'Bonus', 'Amount'],
                'rows' => $rows->map(fn ($row) => [
                    $row['driver'],
                    $row['count'],
                    number_format($row['quantity'], 2),
                    number_format($row['rate'], 2),
                    number_format($row['discount'], 2),
                    number_format($row['bonus'], 2),
                    number_format($row['amount'], 2),
                ])->all(),
            ],
            [
                'title' => 'Driver Entries by Pump',
                'columns' => ['Pump', 'Driver', 'Entries', 'Qty', 'Discount', 'Bonus', 'Amount'],
                'rows' => $driverByPump->map(fn ($row) => [
                    $row['pump'],
                    $row['driver'],
                    $row['count'],
                    number_format($row['quantity'], 2),
                    number_format($row['discount'], 2),
                    number_format($row['bonus'], 2),
                    number_format($row['amount'], 2),
                ])->all(),
            ],
        ];

        $pdf = Pdf::loadView('reports.pdf.report', [
            'title' => 'Driver-wise Purchase Report',
            'meta' => $this->filterMeta($filters),
            'summary' => 'Total amount: '.number_format($rows->sum(fn ($row) => $row['amount']), 2).' '.Company::current()->currency,
            'tables' => $tables,
        ]);

        return $pdf->download('driver-wise.pdf');
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

    public function exportPaymentsPdf(Request $request): Response
    {
        $filters = $request->only(['date_from', 'date_to', 'pump_id']);
        $report = $this->reports->paymentReport($filters);

        $pdf = Pdf::loadView('reports.pdf.report', [
            'title' => 'Payment Report',
            'meta' => $this->filterMeta($filters),
            'summary' => 'Total amount: '.number_format($report['totals']['amount'], 2).' '.Company::current()->currency,
            'tables' => [
                [
                    'title' => 'Payments',
                    'columns' => ['Date', 'Type', 'Voucher', 'Pump', 'Method', 'Amount'],
                    'rows' => $report['rows']->map(fn ($row) => [
                        $row->payment_date->format('d M Y'),
                        $row->type->label(),
                        $row->voucher_number,
                        $row->pump?->name,
                        $row->payment_method->label(),
                        number_format((float) $row->amount, 2),
                    ])->all(),
                ],
            ],
        ]);

        return $pdf->download('payments.pdf');
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

        if (! empty($filters['fuel_type'])) {
            $meta[] = 'Fuel: '.strtoupper($filters['fuel_type']);
        }

        if (! empty($filters['status'])) {
            $meta[] = 'Status: '.ucfirst($filters['status']);
        }

        return $meta;
    }

    /**
     * @template TKey of array-key
     * @template TValue
     *
     * @param  Collection<TKey, TValue>  $rows
     * @return LengthAwarePaginator<TValue>
     */
    private function paginateCollection(Collection $rows, Request $request, string $pageName, int $perPage = 20): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage($pageName);

        return new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
                'pageName' => $pageName,
            ],
        );
    }
}
