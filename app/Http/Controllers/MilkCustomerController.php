<?php

namespace App\Http\Controllers;

use App\Models\MilkCustomer;
use App\Support\ListPagination;
use App\Support\ListingSearch;
use Illuminate\Http\Request;

class MilkCustomerController extends Controller
{
    public function index(Request $request)
    {
        $perPage = ListPagination::resolvePerPage($request);
        $search = ListingSearch::term($request->get('search'));

        $query = MilkCustomer::query()->orderBy('name');
        if ($search) {
            ListingSearch::applyTextColumns($query, $search, ['name', 'mobile', 'address']);
        }

        $customers = $query->paginate($perPage)->withQueryString();

        return view('milk-customers.index', compact('customers', 'perPage', 'search'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'status' => 'nullable|in:active,inactive',
        ]);

        MilkCustomer::create([
            ...$data,
            'status' => $data['status'] ?? 'active',
        ]);

        return redirect()
            ->back()
            ->with('success', __('milk_flow.customer_saved'));
    }

    public function update(Request $request, MilkCustomer $milkCustomer)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
        ]);

        $milkCustomer->update($data);

        return redirect()
            ->back()
            ->with('success', __('milk_flow.customer_updated'));
    }

    public function destroy(MilkCustomer $milkCustomer)
    {
        if ($milkCustomer->distributions()->exists()) {
            return redirect()
                ->back()
                ->with('error', __('milk_flow.customer_has_distributions'));
        }

        $milkCustomer->delete();

        return redirect()
            ->back()
            ->with('success', __('milk_flow.customer_deleted'));
    }
}
