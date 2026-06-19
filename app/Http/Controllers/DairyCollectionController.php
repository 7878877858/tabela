<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\DairyCollection;
use App\Services\MilkFlowService;
use App\Support\ListPagination;
use Illuminate\Http\Request;

class DairyCollectionController extends Controller
{
    public function __construct(
        protected MilkFlowService $milkFlow
    ) {
    }

    public function index(Request $request)
    {
        $date = $request->get('date', today()->toDateString());
        $perPage = ListPagination::resolvePerPage($request);

        $collections = DairyCollection::query()
            ->whereDate('date', $date)
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $summary = $this->milkFlow->reconciliationForDate($date);
        $dailyReport = DailyReport::whereDate('report_date', $date)->first();

        return view('dairy-collections.index', compact(
            'collections',
            'summary',
            'date',
            'perPage',
            'dailyReport'
        ));
    }

    public function store(Request $request)
    {
        $date = $request->input('date', today()->toDateString());
        $dailyReport = DailyReport::whereDate('report_date', $date)->first();

        if ($dailyReport) {
            return redirect()
                ->route('daily-reports.edit', $dailyReport)
                ->with('info', __('milk_flow.enter_via_daily_report'));
        }

        return redirect()
            ->route('daily-reports.create')
            ->with('info', __('milk_flow.enter_via_daily_report'));
    }

    public function destroy(DairyCollection $dairyCollection)
    {
        $date = $dairyCollection->date->toDateString();

        if ($dairyCollection->slip_image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($dairyCollection->slip_image);
        }

        $dairyCollection->delete();

        return redirect()
            ->route('dairy-collections.index', ['date' => $date])
            ->with('success', __('milk_flow.dairy_deleted'));
    }
}
