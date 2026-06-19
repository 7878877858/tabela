<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\Income;
use App\Support\ListPagination;
use Illuminate\Http\Request;

class OtherIncomeController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date', today()->toDateString());
        $perPage = ListPagination::resolvePerPage($request);

        $entries = Income::query()
            ->where('category', 'other_income')
            ->whereDate('income_date', $date)
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $dailyReport = DailyReport::whereDate('report_date', $date)->first();

        return view('income.other', compact('entries', 'perPage', 'date', 'dailyReport'));
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
