<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ResolvesCurrentCompany;
use App\Http\Controllers\Controller;
use App\Modules\Operations\Queries\DashboardMetrics;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use ResolvesCurrentCompany;

    public function index(Request $request, DashboardMetrics $metrics): View
    {
        $company = $this->currentCompany($request);

        return view('admin.dashboard', [
            'company' => $company,
            'stats' => $metrics->counts($company),
            'recentConsultations' => $metrics->recentConsultations($company),
            'openHandovers' => $metrics->latestOpenHandovers($company),
        ]);
    }
}
