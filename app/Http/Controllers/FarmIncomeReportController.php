<?php

namespace App\Http\Controllers;

use App\Models\Buffalo;
use App\Models\Income;
use App\Services\FarmIncomeService;
use App\Support\ListPagination;
use Illuminate\Http\Request;

class FarmIncomeReportController extends Controller
{
    public function __construct(
        protected FarmIncomeService $farmIncome
    ) {
    }

    public function animalSales(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->get('date_to', now()->toDateString());
        $buffaloId = $request->get('buffalo_id');
        $buyer = $request->get('buyer');
        $perPage = ListPagination::resolvePerPage($request);

        $query = Income::with('buffalo')
            ->where('category', 'animal_sale')
            ->whereBetween('income_date', [$dateFrom, $dateTo]);

        if ($buffaloId) {
            $query->where('buffalo_id', $buffaloId);
        }

        if ($buyer) {
            $query->where('buyer_name', 'like', '%' . $buyer . '%');
        }

        $records = (clone $query)
            ->orderByDesc('income_date')
            ->paginate($perPage)
            ->withQueryString();

        $total = (float) (clone $query)->sum('amount');
        $animals = Buffalo::orderBy('tag_number')->get();

        return view('reports.animal-sales', compact(
            'records',
            'dateFrom',
            'dateTo',
            'buffaloId',
            'buyer',
            'perPage',
            'total',
            'animals'
        ));
    }

    public function manureSales(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->get('date_to', now()->toDateString());
        $buyer = $request->get('buyer');
        $perPage = ListPagination::resolvePerPage($request);

        $query = Income::query()
            ->where('category', 'manure_sale')
            ->whereBetween('income_date', [$dateFrom, $dateTo]);

        if ($buyer) {
            $query->where('buyer_name', 'like', '%' . $buyer . '%');
        }

        $records = (clone $query)
            ->orderByDesc('income_date')
            ->paginate($perPage)
            ->withQueryString();

        $total = (float) (clone $query)->sum('amount');
        $totalWeight = (float) (clone $query)->sum('weight_kg');

        return view('reports.manure-sales', compact(
            'records',
            'dateFrom',
            'dateTo',
            'buyer',
            'perPage',
            'total',
            'totalWeight'
        ));
    }

    public function summary(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->get('date_to', now()->toDateString());

        $summary = $this->farmIncome->summaryForPeriod(
            \Carbon\Carbon::parse($dateFrom),
            \Carbon\Carbon::parse($dateTo)
        );

        return view('reports.income-summary', compact('summary', 'dateFrom', 'dateTo'));
    }
}
