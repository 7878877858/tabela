<?php

namespace App\Http\Controllers;

use App\Models\Buffalo;
use App\Services\AnimalTagService;
use App\Services\CalfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BuffaloController extends Controller
{
    public function __construct(
        protected CalfService $calfService
    ) {
    }

    public function index()
    {
        $animals = Buffalo::withCount('milkEntries')
            ->orderBy('tag_number')
            ->get();

        $animalTypeCounts = Buffalo::activeCountsByAnimalType(false);

        $animalsJson = $animals->map(function (Buffalo $b) {
            return [
                'id' => $b->id,
                'tag' => $b->tag_number,
                'name' => $b->name ?? '',
                'animal_type' => Buffalo::normalizeAnimalType($b->animal_type),
                'type_label' => $b->animal_type_label,
                'status' => $b->status,
                'status_label' => $b->status_label,
                'lactation_label' => $b->lactation_label,
                'milk_entries_count' => (int) $b->milk_entries_count,
                'month_milk' => (float) $b->totalMilkThisMonth(),
                'show_url' => route('buffalo.show', $b),
                'edit_url' => route('buffalo.edit', $b),
                'destroy_url' => route('buffalo.destroy', $b),
            ];
        })->values();

        return view('buffalo.index', compact('animalsJson', 'animalTypeCounts'));
    }

    public function create()
    {
        $nextTags = collect(AnimalTagService::TYPES)
            ->mapWithKeys(fn ($type) => [$type => AnimalTagService::preview($type)])
            ->all();

        return view('buffalo.create', compact('nextTags'));
    }

    public function nextTag(Request $request)
    {
        $request->validate([
            'animal_type' => 'required|in:' . implode(',', AnimalTagService::TYPES),
        ]);

        return response()->json([
            'tag_number' => AnimalTagService::preview($request->animal_type),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'animal_type'      => 'required|in:' . implode(',', AnimalTagService::TYPES),
            'name'             => 'nullable|string|max:100',
            'dob'              => 'nullable|date',
            'purchase_date'    => 'nullable|date',
            'purchase_price'   => 'nullable|numeric|min:0',
            'status'           => 'required|in:active,sold,dead',
            'lactation_status' => 'required|in:lactating,dry,pregnant',
            'notes'            => 'nullable|string',
            'heat_date' => 'nullable|date',
            'ai_date' => 'nullable|date',
            'pregnancy_check_date' => 'nullable|date',
            'expected_delivery_date' => 'nullable|date',
            'birth_date' => 'nullable|date',
            'calf_gender' => 'nullable|in:male,female',
            'calf_weight' => 'nullable|numeric',
        ]);

        $parent = DB::transaction(function () use ($validated) {
            $validated['tag_number'] = AnimalTagService::generate($validated['animal_type']);
            $parent = Buffalo::create($validated);
            $this->calfService->syncFromParent($parent);

            return $parent->fresh(['birthCalf']);
        });

        $message = 'પશુ ઉમેરાયો! ટેગ: ' . $parent->tag_number;
        if ($parent->birthCalf) {
            $message .= ' · બચ્ચા ટેગ: ' . $parent->birthCalf->tag_number;
        }

        return redirect()->route('buffalo.index')->with('success', $message);
    }

    public function show(Buffalo $buffalo)
    {
        $buffalo->load('birthCalf', 'mother');

        $milkHistory = $buffalo->milkEntries()
            ->orderByDesc('entry_date')
            ->paginate(30);

        $expenses = $buffalo->expenses()
            ->orderByDesc('expense_date')
            ->paginate(20);

        $monthlyMilk = $buffalo->milkEntries()
            ->selectRaw('YEAR(entry_date) as yr, MONTH(entry_date) as mo, SUM(total_liters) as total')
            ->groupBy('yr', 'mo')
            ->orderByDesc('yr')->orderByDesc('mo')
            ->take(6)->get();

        return view('buffalo.show', compact('buffalo', 'milkHistory', 'expenses', 'monthlyMilk'));
    }

    public function edit(Buffalo $buffalo)
    {
        $buffalo->load('birthCalf');

        return view('buffalo.edit', compact('buffalo'));
    }

    public function update(Request $request, Buffalo $buffalo)
    {
        $validated = $request->validate([
            'name'             => 'nullable|string|max:100',
            'dob'              => 'nullable|date',
            'purchase_date'    => 'nullable|date',
            'purchase_price'   => 'nullable|numeric|min:0',
            'status'           => 'required|in:active,sold,dead',
            'lactation_status' => 'required|in:lactating,dry,pregnant',
            'notes'            => 'nullable|string',
            'heat_date' => 'nullable|date',
            'ai_date' => 'nullable|date',
            'pregnancy_check_date' => 'nullable|date',
            'expected_delivery_date' => 'nullable|date',
            'birth_date' => 'nullable|date',
            'calf_gender' => 'nullable|in:male,female',
            'calf_weight' => 'nullable|numeric',
        ]);

        DB::transaction(function () use ($buffalo, $validated) {
            $buffalo->update($validated);
            $this->calfService->syncFromParent($buffalo->fresh());
        });

        $buffalo->refresh()->load('birthCalf');

        $message = 'માહિતી અપડેટ થઈ!';
        if ($buffalo->birthCalf) {
            $message .= ' બચ્ચા ટેગ: ' . $buffalo->birthCalf->tag_number;
        }

        return redirect()->route('buffalo.show', $buffalo)->with('success', $message);
    }

    public function destroy(Buffalo $buffalo)
    {
        $buffalo->delete();

        return redirect()->route('buffalo.index')
            ->with('success', 'પશુ દૂર કરવામાં આવ્યો.');
    }
}
