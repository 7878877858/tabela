<?php

namespace App\Http\Controllers;

use App\Models\{MilkEntry, Buffalo};
use Illuminate\Http\Request;
use App\Services\MilkStockService;

class MilkEntryController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date', today()->toDateString());

        $buffaloes = Buffalo::where('status', 'active')
            ->where('lactation_status', 'lactating')
            ->orderBy('tag_number')
            ->get();

        // Load today's entries
        $entries = MilkEntry::whereDate('entry_date', $date)
            ->with('buffalo')
            ->get()
            ->keyBy('buffalo_id');

        $totalMorning = $entries->sum('morning_liters');
        $totalEvening = $entries->sum('evening_liters');
        $totalLiters  = $entries->sum('total_liters');

        return view('milk.index', compact('buffaloes', 'entries', 'date', 'totalMorning', 'totalEvening', 'totalLiters'));
    }

    public function store(Request $request)
    {
        return redirect()
            ->route('daily-reports.create')
            ->with('error', 'દૂધ એન્ટ્રી માત્ર દૈનિક અહેવાલમાંથી કરો. Daily Report is the only data entry source.');
    }

    public function history(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year  = $request->get('year', now()->year);

        $daily = MilkEntry::whereYear('entry_date', $year)
            ->whereMonth('entry_date', $month)
            ->selectRaw('entry_date, SUM(morning_liters) as morning, SUM(evening_liters) as evening, SUM(total_liters) as total')
            ->groupBy('entry_date')
            ->orderBy('entry_date')
            ->get();

        $monthTotal = $daily->sum('total');

        return view('milk.history', compact('daily', 'month', 'year', 'monthTotal'));
    }

    public function destroy(MilkEntry $milkEntry)
    {
        if ($milkEntry->daily_report_id) {
            return back()->with('error', 'આ એન્ટ્રી દૈનિક અહેવાલમાંથી ડિલીટ કરો.');
        }

        MilkStockService::reverseProduction($milkEntry);
        $milkEntry->delete();

        return back()->with('success', 'એન્ટ્રી ડિલીટ થઈ.');
    }
}
