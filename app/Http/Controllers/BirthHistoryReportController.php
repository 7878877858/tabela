<?php

namespace App\Http\Controllers;

use App\Services\CalfBirthReportService;
use App\Support\ListPagination;
use Illuminate\Http\Request;

class BirthHistoryReportController extends Controller
{
    public function __construct(
        protected CalfBirthReportService $service
    ) {
    }

    public function index(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfYear()->toDateString());
        $dateTo = $request->get('date_to', now()->toDateString());
        $search = trim((string) $request->get('search', ''));
        $perPage = ListPagination::resolvePerPage($request);

        $births = $this->service->paginated(
            $dateFrom,
            $dateTo,
            $search ?: null,
            $perPage
        );

        $perPageOptions = ListPagination::OPTIONS;

        return view('reports.birth-history', compact(
            'births',
            'dateFrom',
            'dateTo',
            'search',
            'perPage',
            'perPageOptions'
        ));
    }
}
