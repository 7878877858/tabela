<?php
namespace App\Http\Controllers;

use App\Models\{MilkEntry, Buffalo};
use Illuminate\Http\Request;

class MilkEntryController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date', today()->toDateString());

        $buffaloes = Buffalo::where('status','active')
            ->where('lactation_status','lactating')
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

        return view('milk.index', compact('buffaloes','entries','date','totalMorning','totalEvening','totalLiters'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'entry_date'  => 'required|date',
            'entries'     => 'required|array',
            'entries.*.buffalo_id'      => 'required|exists:buffaloes,id',
            'entries.*.morning_liters'  => 'nullable|numeric|min:0',
            'entries.*.evening_liters'  => 'nullable|numeric|min:0',
        ]);

        foreach ($request->entries as $row) {
            if (($row['morning_liters'] ?? 0) > 0 || ($row['evening_liters'] ?? 0) > 0) {
                MilkEntry::updateOrCreate(
                    ['buffalo_id' => $row['buffalo_id'], 'entry_date' => $request->entry_date],
                    [
                        'morning_liters' => $row['morning_liters'] ?? 0,
                        'evening_liters' => $row['evening_liters'] ?? 0,
                        'notes'          => $row['notes'] ?? null,
                    ]
                );
            }
        }

        return redirect()->route('milk.index', ['date' => $request->entry_date])
            ->with('success', 'દૂધ એન્ટ્રી સેવ થઈ!');
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

        return view('milk.history', compact('daily','month','year','monthTotal'));
    }

    public function destroy(MilkEntry $milkEntry)
    {
        $milkEntry->delete();
        return back()->with('success', 'એન્ટ્રી ડિલીટ થઈ.');
    }
}