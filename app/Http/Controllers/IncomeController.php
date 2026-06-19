<?php

namespace App\Http\Controllers;

use App\Models\Income;

class IncomeController extends Controller
{
    public function __construct(
        protected \App\Services\FarmIncomeService $farmIncome
    ) {
    }

    public function index(\Illuminate\Http\Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);
        $from = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $to = $from->copy()->endOfMonth();

        $summary = $this->farmIncome->summaryForPeriod($from, $to);

        $recentManual = Income::manual()
            ->orderByDesc('income_date')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('income.index', compact('summary', 'recentManual', 'month', 'year'));
    }
}
