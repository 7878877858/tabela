<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\Income;
use Illuminate\Http\Request;

class AnimalSaleIncomeController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date', today()->toDateString());

        return redirect()->route('reports.animal-sales', array_filter([
            'date_from' => $request->get('date_from', $date),
            'date_to' => $request->get('date_to', $date),
            'buffalo_id' => $request->get('buffalo_id'),
            'buyer' => $request->get('buyer'),
            'per_page' => $request->get('per_page'),
        ], fn ($value) => $value !== null && $value !== ''));
    }

    public function store(Request $request)
    {
        $date = $request->input('date', today()->toDateString());
        $dailyReport = DailyReport::whereDate('report_date', $date)->first();

        if ($dailyReport) {
            return redirect()
                ->route('daily-reports.edit', $dailyReport)
                ->with('info', __('income.enter_via_daily_report'));
        }

        return redirect()
            ->route('daily-reports.create')
            ->with('info', __('income.enter_via_daily_report'));
    }

    public function destroy(Income $income)
    {
        return back()->with('info', __('income.edit_via_daily_report'));
    }
}
