<?php

namespace App\Http\Controllers;

use App\Models\Buffalo;
use App\Models\Income;
use Illuminate\Http\Request;

class IncomeController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year  = $request->get('year', now()->year);

        $incomes = Income::with('buffalo')
            ->whereYear('income_date', $year)
            ->whereMonth('income_date', $month)
            ->orderByDesc('income_date')
            ->paginate(25);

        $total = Income::whereYear('income_date', $year)
            ->whereMonth('income_date', $month)
            ->sum('amount');

        $incomeCount = Income::whereYear('income_date', $year)
            ->whereMonth('income_date', $month)
            ->count();

        $byCategory = Income::whereYear('income_date', $year)
            ->whereMonth('income_date', $month)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get();

        $buffaloes = Buffalo::where('status', 'active')->orderBy('tag_number')->get();

        return view('income.index', compact(
            'incomes',
            'total',
            'incomeCount',
            'byCategory',
            'buffaloes',
            'month',
            'year'
        ));
    }

    public function store(Request $request)
    {
        return back()->with('error', 'આવક માત્ર દૈનિક અહેવાલમાંથી દાખલ કરો.');
    }

    public function destroy(Income $income)
    {
        if ($income->daily_report_id) {
            return back()->with('error', 'આ આવક દૈનિક અહેવાલમાંથી ડિલીટ કરો.');
        }

        $income->delete();

        return back()->with('success', __('income.deleted_success'));
    }
}
