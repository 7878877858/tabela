<?php
namespace App\Http\Controllers;

use App\Models\{Expense, Buffalo};
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year  = $request->get('year', now()->year);

        $expenses = Expense::with('buffalo')
            ->whereYear('expense_date', $year)
            ->whereMonth('expense_date', $month)
            ->orderByDesc('expense_date')
            ->paginate(25);

        $total = Expense::whereYear('expense_date', $year)
            ->whereMonth('expense_date', $month)
            ->sum('amount');

        $byCategory = Expense::whereYear('expense_date', $year)
            ->whereMonth('expense_date', $month)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')->get();

        $buffaloes = Buffalo::where('status','active')->orderBy('tag_number')->get();

        return view('kharch.index', compact('expenses','total','byCategory','buffaloes','month','year'));
    }

    public function store(Request $request)
    {
        return back()->with('error', 'ખર્ચ માત્ર દૈનિક અહેવાલમાંથી દાખલ કરો (Feed stock-in અલગ રહેશે).');
    }

    public function destroy(Expense $expense)
    {
        if ($expense->daily_report_id) {
            return back()->with('error', 'આ ખર્ચ દૈનિક અહેવાલમાંથી ડિલીટ કરો.');
        }

        $expense->delete();

        return back()->with('success', 'ખર્ચ ડિલીટ થયો.');
    }
}