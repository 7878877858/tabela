<?php

namespace App\Http\Controllers;

use App\Services\FarmFinancialService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function __construct(
        protected FarmFinancialService $financial
    ) {
    }

    public function index(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);
        $from = Carbon::create($year, $month, 1)->startOfMonth();
        $to = $from->copy()->endOfMonth();

        $summary = $this->financial->expenseSummaryForPeriod($from, $to);
        $dashboard = $this->financial->dashboardToday();

        return view('expenses.index', compact('summary', 'dashboard', 'month', 'year'));
    }

    public function store(Request $request)
    {
        return back()->with('error', __('farm.daily_expense_note'));
    }

    public function destroy()
    {
        return back()->with('error', __('farm.daily_expense_note'));
    }
}
