<?php

namespace App\Http\Controllers;

use App\Models\DairyCollection;
use App\Models\MilkCustomer;
use App\Models\MilkDistribution;
use App\Services\MilkFlowService;
use App\Support\ListPagination;
use Illuminate\Http\Request;

class MilkFlowReportController extends Controller
{
    public function __construct(
        protected MilkFlowService $milkFlow
    ) {
    }

    public function distribution(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->get('date_to', now()->toDateString());
        $customerId = $request->get('customer_id');
        $milkType = $request->get('milk_type');
        $perPage = ListPagination::resolvePerPage($request);

        $query = MilkDistribution::query()
            ->whereBetween('date', [$dateFrom, $dateTo]);

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        if ($milkType && in_array($milkType, ['buffalo', 'cow'], true)) {
            $query->where('milk_type', $milkType);
        }

        $records = (clone $query)
            ->with('customer')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $customers = MilkCustomer::orderBy('name')->get();

        $totals = (clone $query)->selectRaw('
            COALESCE(SUM(total_liter), 0) as liters,
            COALESCE(SUM(amount), 0) as amount
        ')->first();

        return view('reports.milk-distribution', compact(
            'records',
            'customers',
            'dateFrom',
            'dateTo',
            'customerId',
            'milkType',
            'perPage',
            'totals'
        ));
    }

    public function dairyCollection(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->get('date_to', now()->toDateString());
        $slipNumber = trim((string) $request->get('slip_number', ''));
        $perPage = ListPagination::resolvePerPage($request);

        $query = DairyCollection::query()
            ->whereBetween('date', [$dateFrom, $dateTo]);

        if ($slipNumber !== '') {
            $query->where('slip_number', 'like', '%' . $slipNumber . '%');
        }

        $records = (clone $query)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $totals = (clone $query)->selectRaw('
            COALESCE(SUM(buffalo_liter), 0) as buffalo_liter,
            COALESCE(SUM(cow_liter), 0) as cow_liter,
            COALESCE(SUM(buffalo_amount + cow_amount), 0) as amount
        ')->first();

        return view('reports.dairy-collection', compact(
            'records',
            'dateFrom',
            'dateTo',
            'slipNumber',
            'perPage',
            'totals'
        ));
    }

    public function reconciliation(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->get('date_to', now()->toDateString());

        $rows = $this->milkFlow->reconciliationRange($dateFrom, $dateTo);

        return view('reports.milk-reconciliation', compact('rows', 'dateFrom', 'dateTo'));
    }
}
