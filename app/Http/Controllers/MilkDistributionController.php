<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\MilkCustomer;
use App\Models\MilkDistribution;
use App\Services\MilkFlowService;
use App\Support\ListPagination;
use Illuminate\Http\Request;

class MilkDistributionController extends Controller
{
    public function __construct(
        protected MilkFlowService $milkFlow
    ) {
    }

    public function index(Request $request)
    {
        $date = $request->get('date', today()->toDateString());
        $perPage = ListPagination::resolvePerPage($request);

        $distributions = MilkDistribution::with('customer')
            ->whereDate('date', $date)
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $customers = MilkCustomer::active()->orderBy('name')->get();
        $summary = $this->milkFlow->reconciliationForDate($date);
        $dailyReport = DailyReport::whereDate('report_date', $date)->first();

        return view('milk-distribution.index', compact(
            'distributions',
            'customers',
            'summary',
            'date',
            'perPage',
            'dailyReport'
        ));
    }

    public function store(Request $request)
    {
        $date = $request->input('date', today()->toDateString());
        $dailyReport = DailyReport::whereDate('report_date', $date)->first();

        if ($dailyReport) {
            return redirect()
                ->route('daily-reports.edit', $dailyReport)
                ->with('info', __('milk_flow.enter_via_daily_report'));
        }

        return redirect()
            ->route('daily-reports.create')
            ->with('info', __('milk_flow.enter_via_daily_report'));
    }

    public function destroy(MilkDistribution $milkDistribution)
    {
        $date = $milkDistribution->date->toDateString();
        $milkDistribution->delete();

        return redirect()
            ->route('milk-distribution.index', ['date' => $date])
            ->with('success', __('milk_flow.distribution_deleted'));
    }
}
