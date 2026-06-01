<?php
namespace App\Http\Controllers;

use App\Models\Buffalo;
use Illuminate\Http\Request;

class BuffaloController extends Controller
{
    public function index()
    {
        $buffaloes = Buffalo::withCount('milkEntries')
            ->orderBy('tag_number')
            ->paginate(20);
        return view('buffalo.index', compact('buffaloes'));
    }

    public function create()
    {
        return view('buffalo.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tag_number'       => 'required|unique:buffaloes',
            'name'             => 'nullable|string|max:100',
            'dob'              => 'nullable|date',
            'purchase_date'    => 'nullable|date',
            'purchase_price'   => 'nullable|numeric|min:0',
            'status'           => 'required|in:active,sold,dead',
            'lactation_status' => 'required|in:lactating,dry,pregnant',
            'notes'            => 'nullable|string',
        ]);

        Buffalo::create($validated);
        return redirect()->route('buffalo.index')
            ->with('success', 'ભેંસ ઉમેરવામાં આવી!');
    }

    public function show(Buffalo $buffalo)
    {
        $milkHistory = $buffalo->milkEntries()
            ->orderByDesc('entry_date')
            ->paginate(30);

        $expenses = $buffalo->expenses()
            ->orderByDesc('expense_date')
            ->paginate(20);

        $monthlyMilk = $buffalo->milkEntries()
            ->selectRaw('YEAR(entry_date) as yr, MONTH(entry_date) as mo, SUM(total_liters) as total')
            ->groupBy('yr','mo')
            ->orderByDesc('yr')->orderByDesc('mo')
            ->take(6)->get();

        return view('buffalo.show', compact('buffalo','milkHistory','expenses','monthlyMilk'));
    }

    public function edit(Buffalo $buffalo)
    {
        return view('buffalo.edit', compact('buffalo'));
    }

    public function update(Request $request, Buffalo $buffalo)
    {
        $validated = $request->validate([
            'tag_number'       => 'required|unique:buffaloes,tag_number,'.$buffalo->id,
            'name'             => 'nullable|string|max:100',
            'dob'              => 'nullable|date',
            'purchase_date'    => 'nullable|date',
            'purchase_price'   => 'nullable|numeric|min:0',
            'status'           => 'required|in:active,sold,dead',
            'lactation_status' => 'required|in:lactating,dry,pregnant',
            'notes'            => 'nullable|string',
        ]);

        $buffalo->update($validated);
        return redirect()->route('buffalo.show', $buffalo)
            ->with('success', 'માહિતી અપડેટ થઈ!');
    }

    public function destroy(Buffalo $buffalo)
    {
        $buffalo->delete();
        return redirect()->route('buffalo.index')
            ->with('success', 'ભેંસ દૂર કરવામાં આવી.');
    }
}