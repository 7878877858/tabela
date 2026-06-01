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
        $request->validate([
            'expense_date' => 'required|date',
            'category'     => 'required|in:feed,medicine,labour,equipment,veterinary,other',
            'description'  => 'required|string',
            'amount'       => 'required|numeric|min:0',
            'buffalo_id'   => 'nullable|exists:buffaloes,id',
        ]);

        Expense::create($request->all());
        return back()->with('success', 'ખર્ચ ઉમેરાયો!');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return back()->with('success', 'ખર્ચ ડિલીટ થયો.');
    }
}