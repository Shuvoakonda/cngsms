<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(DashboardService $dashboard): View
    {
        return view('dashboard', [
            'stats' => $dashboard->stats(),
            'recentTransactions' => $dashboard->recentTransactions(),
            'topPumps' => $dashboard->topPumpsThisMonth(),
            'alerts' => $dashboard->alerts(),
            'charts' => [
                'monthlyComparison' => $dashboard->monthlyComparison(),
                'pumpOutstanding' => $dashboard->pumpOutstanding(),
                'outstandingTrend' => $dashboard->outstandingTrend(),
            ],
        ]);
    }
}
