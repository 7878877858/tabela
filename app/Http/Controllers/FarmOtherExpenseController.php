<?php

namespace App\Http\Controllers;

use App\Models\FarmOtherExpense;
use App\Support\ListPagination;
use Illuminate\Http\Request;

class FarmOtherExpenseController extends Controller
{
    public function index(Request $request)
    {
        $perPage = ListPagination::resolvePerPage($request);
        $expenses = FarmOtherExpense::orderByDesc('expense_date')->paginate($perPage)->withQueryString();

        return view('expenses.other', compact('expenses', 'perPage'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category' => 'required|string|max:150',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'remarks' => 'nullable|string|max:1000',
        ]);

        FarmOtherExpense::create($data);

        return back()->with('success', __('farm.other_expense_saved'));
    }

    public function destroy(FarmOtherExpense $farmOtherExpense)
    {
        $farmOtherExpense->delete();

        return back()->with('success', __('farm.deleted'));
    }
}
