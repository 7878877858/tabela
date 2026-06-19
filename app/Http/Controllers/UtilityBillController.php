<?php

namespace App\Http\Controllers;

use App\Models\UtilityBill;
use App\Support\ListPagination;
use Illuminate\Http\Request;

class UtilityBillController extends Controller
{
    public function index(Request $request)
    {
        $perPage = ListPagination::resolvePerPage($request);
        $bills = UtilityBill::orderByDesc('bill_date')->paginate($perPage)->withQueryString();

        return view('expenses.utility-bills', compact('bills', 'perPage'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'bill_type' => 'required|in:electricity,water,internet,phone,other',
            'amount' => 'required|numeric|min:0',
            'bill_date' => 'required|date',
            'due_date' => 'nullable|date',
            'paid_date' => 'nullable|date',
            'status' => 'required|in:paid,pending',
            'remarks' => 'nullable|string|max:1000',
        ]);

        UtilityBill::create($data);

        return back()->with('success', __('farm.utility_saved'));
    }

    public function destroy(UtilityBill $utilityBill)
    {
        $utilityBill->delete();

        return back()->with('success', __('farm.deleted'));
    }
}
