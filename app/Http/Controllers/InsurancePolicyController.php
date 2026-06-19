<?php

namespace App\Http\Controllers;

use App\Models\InsurancePolicy;
use App\Support\ListPagination;
use Illuminate\Http\Request;

class InsurancePolicyController extends Controller
{
    public function index(Request $request)
    {
        $perPage = ListPagination::resolvePerPage($request);
        $policies = InsurancePolicy::orderByDesc('start_date')->paginate($perPage)->withQueryString();

        return view('expenses.insurance', compact('policies', 'perPage'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'insurance_type' => 'required|in:animal,asset,vehicle',
            'policy_number' => 'nullable|string|max:100',
            'premium_amount' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'expiry_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:active,expired',
            'remarks' => 'nullable|string|max:1000',
        ]);

        InsurancePolicy::create($data);

        return back()->with('success', __('farm.insurance_saved'));
    }

    public function destroy(InsurancePolicy $insurancePolicy)
    {
        $insurancePolicy->delete();

        return back()->with('success', __('farm.deleted'));
    }
}
