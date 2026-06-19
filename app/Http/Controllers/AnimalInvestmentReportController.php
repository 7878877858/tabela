<?php

namespace App\Http\Controllers;

use App\Models\Buffalo;
use App\Models\Setting;
use App\Services\AnimalInvestmentService;
use App\Support\ListPagination;
use Illuminate\Http\Request;

class AnimalInvestmentReportController extends Controller
{
    public function __construct(
        protected AnimalInvestmentService $service
    ) {
    }

    public function index(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfYear()->toDateString());
        $dateTo = $request->get('date_to', now()->toDateString());
        $animalType = $request->get('animal_type', '');
        $search = trim((string) $request->get('search', ''));
        $perPage = ListPagination::resolvePerPage($request);

        $records = $this->service->paginatedReport(
            $dateFrom,
            $dateTo,
            $animalType ?: null,
            $search ?: null,
            $perPage
        );
        $totals = $this->service->totalsForFilters($dateFrom, $dateTo, $animalType ?: null, $search ?: null);
        $grandTotal = array_sum($totals);

        $currency = Setting::get('currency', '₹');
        $animalTypes = Buffalo::animalTypeOptions();
        $perPageOptions = ListPagination::OPTIONS;

        return view('reports.animal-investment', compact(
            'records',
            'totals',
            'grandTotal',
            'dateFrom',
            'dateTo',
            'animalType',
            'search',
            'perPage',
            'perPageOptions',
            'currency',
            'animalTypes'
        ));
    }
}
