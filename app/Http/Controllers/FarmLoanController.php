<?php

namespace App\Http\Controllers;

use App\Models\FarmLoan;
use App\Support\ListPagination;
use Illuminate\Http\Request;

class FarmLoanController extends Controller
{
    public function index(Request $request)
    {
        $perPage = ListPagination::resolvePerPage($request);
        $loans = FarmLoan::orderByDesc('start_date')->paginate($perPage)->withQueryString();

        return view('expenses.loans', compact('loans', 'perPage'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'loan_name' => 'required|string|max:150',
            'bank_name' => 'nullable|string|max:150',
            'loan_amount' => 'required|numeric|min:0',
            'emi_amount' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'outstanding_balance' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string|max:1000',
        ]);

        FarmLoan::create($data);

        return back()->with('success', __('farm.loan_saved'));
    }

    public function destroy(FarmLoan $farmLoan)
    {
        $farmLoan->delete();

        return back()->with('success', __('farm.deleted'));
    }
}
