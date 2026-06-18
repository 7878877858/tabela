<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DailyReport;
use App\Models\Buffalo;
use App\Models\MilkEntry;
use App\Models\Employee;
use App\Services\FeedStockService;
use App\Services\MilkStockService;
use App\Services\DailyReportSyncService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Feed;
use App\Models\DailyReportStaff;
use App\Models\DailyReportMilk;
use App\Models\DailyReportFeed;
use App\Models\DailyReportPregnancy;
use App\Models\DailyReportHealth;
use App\Models\DailyReportVaccination;
use App\Models\DailyReportExpense;
use App\Models\DailyReportIncome;
use App\Models\Expense;
use App\Models\Income;


class DailyReportController extends Controller
{
    public function __construct(
        protected DailyReportSyncService $syncService
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reports = DailyReport::orderByDesc('report_date')->paginate(20);
        $totalAnimals = Buffalo::totalHeadCount(true);
        $totalStaff = Employee::where('status', 'active')->count();

        return view('Daily_Report.index', compact('reports', 'totalAnimals', 'totalStaff'));
    }

    protected function heatAnimalsCount(): int
    {
        return Buffalo::where('status', 'active')
            ->whereNotNull('heat_date')
            ->whereDate('heat_date', '>=', now()->subDays(21))
            ->count();
    }

    protected function milkAnimalsForGrid()
    {
        return Buffalo::where('status', 'active')
            ->whereIn('animal_type', Buffalo::ANIMAL_TYPES)
            ->orderBy('tag_number')
            ->get();
    }

    protected function feedAnimalsForGrid()
    {
        return Buffalo::where('status', 'active')
            ->whereIn('animal_type', Buffalo::ANIMAL_TYPES)
            ->orderBy('tag_number')
            ->get();
    }

    protected function animalTypeCountsForReport(): array
    {
        return Buffalo::activeCountsByAnimalType(true);
    }

    /**
     * Active feed types for the daily report grid.
     * Seeds common defaults when the live DB has no feed master data.
     */
    protected function feedsForDailyReport()
    {
        $feeds = Feed::where('status', 1)->withInventoryStats()->orderBy('name')->get();

        if ($feeds->isNotEmpty()) {
            return $feeds;
        }

        foreach (
            [
                ['name' => 'Napier', 'unit' => 'kg'],
                ['name' => 'Dan', 'unit' => 'kg'],
            ] as $row
        ) {
            Feed::firstOrCreate(
                ['name' => $row['name']],
                [
                    'volume'    => 0,
                    'min_stock' => 0,
                    'unit'      => $row['unit'],
                    'status'    => 1,
                ]
            );
        }

        return Feed::where('status', 1)->withInventoryStats()->orderBy('name')->get();
    }

    protected function saveMilkGrid(DailyReport $report, Request $request): float
    {
        $grid = $request->input('milk_grid', []);
        $reportDate = $request->report_date ?? $report->report_date;
        $grandTotal = 0;

        foreach ($grid as $buffaloId => $values) {
            if (!$buffaloId) {
                continue;
            }

            $morning = max(0, (float) ($values['morning'] ?? 0));
            $evening = max(0, (float) ($values['evening'] ?? 0));

            if ($morning <= 0 && $evening <= 0) {
                continue;
            }

            $total = $morning + $evening;
            $grandTotal += $total;

            DailyReportMilk::create([
                'daily_report_id' => $report->id,
                'buffalo_id'      => $buffaloId,
                'morning_milk'    => $morning,
                'evening_milk'    => $evening,
                'total_milk'      => $total,
            ]);
        }

        return $grandTotal;
    }

    public function autosaveMilk(Request $request, string $id)
    {
        $report = DailyReport::findOrFail($id);

        $request->validate([
            'milk_grid'   => 'nullable|array',
            'report_date' => 'nullable|date',
        ]);

        $summary = DB::transaction(function () use ($request, $report) {
            $grid = $request->input('milk_grid', []);
            $reportDate = $request->input('report_date', $report->report_date);

            foreach ($grid as $buffaloId => $values) {
                if (!$buffaloId) {
                    continue;
                }

                $morning = max(0, (float) ($values['morning'] ?? 0));
                $evening = max(0, (float) ($values['evening'] ?? 0));

                if ($morning <= 0 && $evening <= 0) {
                    DailyReportMilk::where('daily_report_id', $report->id)
                        ->where('buffalo_id', $buffaloId)
                        ->delete();

                    continue;
                }

                $total = $morning + $evening;

                DailyReportMilk::updateOrCreate(
                    [
                        'daily_report_id' => $report->id,
                        'buffalo_id'      => $buffaloId,
                    ],
                    [
                        'morning_milk' => $morning,
                        'evening_milk' => $evening,
                        'total_milk'   => $total,
                    ]
                );
            }

            return $this->syncService->syncMilkFromRequest($report->fresh(), $request);
        });

        return response()->json([
            'success' => true,
            'message' => 'Milk saved',
            'summary' => $summary,
        ]);
    }

    protected function validateFeedConsumption(Request $request): array
    {
        $grid = $request->input('feed_grid', []);
        $feedConsumption = [];

        foreach ($grid as $buffaloId => $periods) {
            foreach (['morning', 'evening'] as $period) {
                foreach (($periods[$period] ?? []) as $feedId => $qty) {
                    $qty = (float) $qty;
                    if ($qty < 0) {
                        throw ValidationException::withMessages([
                            'feed_stock' => 'ચારો જથ્થો negative દાખલ કરી શકાતો નથી.',
                        ]);
                    }
                    if ($qty > 0) {
                        $feedConsumption[(int) $feedId] = ($feedConsumption[(int) $feedId] ?? 0) + $qty;
                    }
                }
            }
        }

        $feedsById = collect();
        if (!empty($feedConsumption)) {
            $feedsById = Feed::whereIn('id', array_keys($feedConsumption))->get()->keyBy('id');

            foreach ($feedConsumption as $feedId => $requiredQty) {
                $feed = $feedsById->get($feedId);
                $available = $feed ? FeedStockService::currentBalance($feed) : 0;

                if (!$feed || $available < $requiredQty) {
                    $feedName = $feed?->name ?? ('Feed ' . $feedId);
                    throw ValidationException::withMessages([
                        'feed_stock' => $feedName . ' માટે પૂરતો સ્ટોક નથી. ઉપલબ્ધ: ' . number_format($available, 2) . ', જરૂરી: ' . number_format($requiredQty, 2),
                    ]);
                }
            }
        }

        return compact('grid', 'feedConsumption', 'feedsById');
    }

    protected function saveFeedGrid(DailyReport $report, Request $request, $feedsById, array $feedConsumption): void
    {
        $grid = $request->input('feed_grid', []);

        foreach ($grid as $buffaloId => $periods) {
            if (!$buffaloId) {
                continue;
            }

            $morningFeeds = [];
            $eveningFeeds = [];
            $rowTotal = 0;

            foreach (($periods['morning'] ?? []) as $feedId => $qty) {
                $qty = (float) $qty;
                if ($qty > 0) {
                    $morningFeeds[(string) $feedId] = $qty;
                    $rowTotal += $qty;
                }
            }

            foreach (($periods['evening'] ?? []) as $feedId => $qty) {
                $qty = (float) $qty;
                if ($qty > 0) {
                    $eveningFeeds[(string) $feedId] = $qty;
                    $rowTotal += $qty;
                }
            }

            if ($rowTotal <= 0) {
                continue;
            }

            DailyReportFeed::create([
                'daily_report_id' => $report->id,
                'buffalo_id'      => $buffaloId,
                'morning_feeds'   => $morningFeeds,
                'evening_feeds'   => $eveningFeeds,
                'total_feed'      => $rowTotal,
            ]);
        }

        FeedStockService::consumeFromDailyReportGrid(
            $grid,
            $request->report_date ?? $report->report_date->toDateString(),
            $report->id
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = Employee::all();
        $buffaloes = Buffalo::all();
        $feeds = $this->feedsForDailyReport();
        $totalAnimals = Buffalo::totalHeadCount(true);
        $animalTypeCounts = $this->animalTypeCountsForReport();

        $lactatingAnimals = Buffalo::where(
            'lactation_status',
            'lactating'
        )->count();

        $pregnantAnimals = Buffalo::where(
            'lactation_status',
            'pregnant'
        )->count();

        $dryAnimals = Buffalo::where(
            'lactation_status',
            'dry'
        )->count();

        $totalMilk = MilkEntry::sum('total_liters');

        $committeeMembers = Employee::where(
            'employee_type',
            'committee'
        )->where(
            'status',
            'active'
        )->get();

        $lastReport = DailyReport::latest('id')->first();

        if ($lastReport) {
            $nextNumber = $lastReport->id + 1;
        } else {
            $nextNumber = 1;
        }

        $reportNumber = 'REP-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        $heatAnimals = $this->heatAnimalsCount();
        $feedAnimals = $this->feedAnimalsForGrid();
        $feedRecords = collect();
        $milkAnimals = $this->milkAnimalsForGrid();
        $milkRecords = collect();

        return view(
            'Daily_Report.create',
            compact(
                'employees',
                'committeeMembers',
                'buffaloes',
                'feeds',
                'feedAnimals',
                'feedRecords',
                'milkAnimals',
                'milkRecords',
                'totalAnimals',
                'animalTypeCounts',
                'lactatingAnimals',
                'pregnantAnimals',
                'dryAnimals',
                'totalMilk',
                'reportNumber',
                'heatAnimals'
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'report_date' => 'required|date',
        ]);

        $feedData = $this->validateFeedConsumption($request);
        extract($feedData);

        $report = DB::transaction(function () use ($request, $feedData) {
        extract($feedData);

        $report = DailyReport::create([
            'report_date'   => $request->report_date,
            'shift'         => $request->shift,
            'total_animals' => $request->total_animals ?? 0,
            'total_milk'    => $request->total_milk ?? 0,
            'present_staff' => $request->present_staff ?? 0,
            'absent_staff'  => $request->absent_staff ?? 0,
            'notes'         => $request->notes,
            'report_number' => $request->report_number,
            'reporter'      => $request->reporter,
            'clean_cowshed' => $request->has('clean_cowshed'),
            'clean_cowshed_by' => $request->clean_cowshed_by,
            'clean_cowshed_note' => $request->clean_cowshed_note,

            'clean_milk_room' => $request->has('clean_milk_room'),
            'clean_milk_room_by' => $request->clean_milk_room_by,
            'clean_milk_room_note' => $request->clean_milk_room_note,

            'clean_store' => $request->has('clean_store'),
            'clean_store_by' => $request->clean_store_by,
            'clean_store_note' => $request->clean_store_note,
        ]);

        /*
    |--------------------------------------------------------------------------
    | Staff
    |--------------------------------------------------------------------------
    */

        if ($request->employee_id) {

            foreach ($request->employee_id as $key => $employeeId) {

                if (!$employeeId) {
                    continue;
                }

                DailyReportStaff::create([
                    'daily_report_id' => $report->id,
                    'employee_id'     => $employeeId,
                    'status'          => $request->status[$key] ?? 'present',
                    'remarks'         => $request->remarks[$key] ?? null,
                ]);
            }
        }

        /*
    |--------------------------------------------------------------------------
    | Milk
    |--------------------------------------------------------------------------
    */

        // Milk
        $this->saveMilkGrid($report, $request);

        /*
    |--------------------------------------------------------------------------
    | Feed
    |--------------------------------------------------------------------------
    */
        $this->saveFeedGrid($report, $request, $feedsById, $feedConsumption);

        if ($request->health_buffalo_id) {
            foreach ($request->health_buffalo_id as $key => $buffaloId) {
                if (!$buffaloId) {
                    continue;
                }

                DailyReportHealth::create([
                    'daily_report_id' => $report->id,
                    'buffalo_id'      => $buffaloId,
                    'health_issue'    => $request->health_issue[$key] ?? '',
                    'treatment'       => $request->treatment[$key] ?? '',
                    'medicine_cost'   => $request->medicine_cost[$key] ?? 0,
                ]);
            }
        }

        if ($request->pregnancy_buffalo_id) {
            foreach ($request->pregnancy_buffalo_id as $key => $buffaloId) {
                if (!$buffaloId) {
                    continue;
                }

                DailyReportPregnancy::create([
                    'daily_report_id' => $report->id,
                    'buffalo_id'      => $buffaloId,
                    'checkup_date'    => $request->pregnant_date[$key] ?? null,
                    'status'          => 'pregnant',
                    'remarks'         => null,
                ]);
            }
        }

        if ($request->vaccination_buffalo_id) {
            foreach ($request->vaccination_buffalo_id as $key => $buffaloId) {
                if (!$buffaloId) {
                    continue;
                }

                DailyReportVaccination::create([
                    'daily_report_id'  => $report->id,
                    'buffalo_id'       => $buffaloId,
                    'vaccine_name'     => $request->vaccine_name[$key] ?? '',
                    'vaccination_date' => $request->vaccination_date[$key] ?? null,
                    'remarks'          => $request->vaccination_remarks[$key] ?? '',
                ]);
            }
        }

        if ($request->expense_title) {
            foreach ($request->expense_title as $key => $title) {
                if (!$title) {
                    continue;
                }

                DailyReportExpense::create([
                    'daily_report_id' => $report->id,
                    'title'           => $title,
                    'amount'          => $request->expense_amount[$key] ?? 0,
                    'remarks'         => $request->expense_remarks[$key] ?? '',
                ]);
            }
        }

        if ($request->income_title) {
            foreach ($request->income_title as $key => $title) {
                if (!$title) {
                    continue;
                }

                DailyReportIncome::create([
                    'daily_report_id' => $report->id,
                    'title'           => $title,
                    'amount'          => $request->income_amount[$key] ?? 0,
                    'remarks'         => $request->income_remarks[$key] ?? '',
                ]);
            }
        }

        $this->syncService->syncAll($report->fresh());

        return $report;
        });

        return redirect()
            ->route('daily-reports.index')
            ->with('success', 'Report Created Successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(DailyReport $dailyReport)
    {
        $dailyReport->load([
            'staff.employee',
            'milk',
            'feed.buffalo',
            'pregnancy.buffalo',
            'health.buffalo',
            'expenses',
            'incomes',
            'vaccinations.buffalo'
        ]);

        $births = Buffalo::whereNotNull('birth_date')->get();
        $totalAnimals = Buffalo::totalHeadCount(true);
        $animalTypeCounts = $this->animalTypeCountsForReport();

        $lactatingAnimals = Buffalo::where(
            'lactation_status',
            'lactating'
        )->count();

        $pregnantAnimals = Buffalo::where(
            'lactation_status',
            'pregnant'
        )->count();

        $dryAnimals = Buffalo::where(
            'lactation_status',
            'dry'
        )->count();

        $totalMilk = MilkEntry::whereDate(
            'entry_date',
            $dailyReport->report_date
        )->sum('total_liters');

        $heatAnimals = $this->heatAnimalsCount();
        $feeds = $this->feedsForDailyReport();

        return view(
            'Daily_Report.show',
            compact(
                'dailyReport',
                'totalAnimals',
                'lactatingAnimals',
                'pregnantAnimals',
                'dryAnimals',
                'totalMilk',
                'births',
                'heatAnimals',
                'feeds'
            )
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $report = DailyReport::with([
            'staff.employee',
            'milk',
            'feed',
            'pregnancy.buffalo',
            'health.buffalo',
            'expenses',
            'incomes',
            'vaccinations.buffalo'
        ])->findOrFail($id);
        $employees = Employee::all();
        $buffaloes = Buffalo::all();
        $feeds = $this->feedsForDailyReport();

        $totalAnimals = Buffalo::totalHeadCount(true);
        $animalTypeCounts = $this->animalTypeCountsForReport();

        $lactatingAnimals = Buffalo::where(
            'lactation_status',
            'lactating'
        )->count();

        $pregnantAnimals = Buffalo::where(
            'lactation_status',
            'pregnant'
        )->count();

        $dryAnimals = Buffalo::where(
            'lactation_status',
            'dry'
        )->count();

        $committeeMembers = Employee::where(
            'employee_type',
            'committee'
        )->where(
            'status',
            'active'
        )->get();

        $totalMilk = MilkEntry::sum('total_liters');
        $heatAnimals = $this->heatAnimalsCount();
        $feedAnimals = $this->feedAnimalsForGrid();
        $feedRecords = $report->feed->keyBy('buffalo_id');
        $milkAnimals = $this->milkAnimalsForGrid();
        $milkRecords = $report->milk->keyBy('buffalo_id');

        return view(
            'Daily_Report.edit',
            compact(
                'report',
                'employees',
                'committeeMembers',
                'buffaloes',
                'feeds',
                'feedAnimals',
                'feedRecords',
                'milkAnimals',
                'milkRecords',
                'totalAnimals',
                'animalTypeCounts',
                'lactatingAnimals',
                'pregnantAnimals',
                'dryAnimals',
                'totalMilk',
                'heatAnimals'
            )
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $report = DailyReport::findOrFail($id);
        $oldDate = $report->report_date;

        FeedStockService::restoreDailyReport($report->id);
        $this->syncService->purgeSynced($report->id);

        $feedData = $this->validateFeedConsumption($request);
        extract($feedData);

        DB::transaction(function () use ($request, $report, $oldDate, $feedData) {
        extract($feedData);

        $report->update([
            'report_date'   => $request->report_date,
            'shift'         => $request->shift,
            'total_animals' => $request->total_animals ?? 0,
            'total_milk'    => $request->total_milk ?? 0,
            'present_staff' => $request->present_staff ?? 0,
            'absent_staff'  => $request->absent_staff ?? 0,
            'notes'         => $request->notes,
            'report_number' => $request->report_number,
            'reporter'      => $request->reporter,
            'clean_cowshed' => $request->has('clean_cowshed'),
            'clean_cowshed_by' => $request->clean_cowshed_by,
            'clean_cowshed_note' => $request->clean_cowshed_note,

            'clean_milk_room' => $request->has('clean_milk_room'),
            'clean_milk_room_by' => $request->clean_milk_room_by,
            'clean_milk_room_note' => $request->clean_milk_room_note,

            'clean_store' => $request->has('clean_store'),
            'clean_store_by' => $request->clean_store_by,
            'clean_store_note' => $request->clean_store_note,
        ]);

        // Delete old records
        DailyReportStaff::where('daily_report_id', $report->id)->delete();
        DailyReportMilk::where('daily_report_id', $report->id)->delete();
        DailyReportFeed::where('daily_report_id', $report->id)->delete();
        DailyReportPregnancy::where('daily_report_id', $report->id)->delete();
        DailyReportHealth::where('daily_report_id', $report->id)->delete();
        DailyReportVaccination::where('daily_report_id', $report->id)->delete();
        DailyReportExpense::where('daily_report_id', $report->id)->delete();
        DailyReportIncome::where('daily_report_id', $report->id)->delete();

        // Staff
        if ($request->employee_id) {
            foreach ($request->employee_id as $key => $employeeId) {

                if (!$employeeId) {
                    continue;
                }

                DailyReportStaff::create([
                    'daily_report_id' => $report->id,
                    'employee_id'     => $employeeId,
                    'status'          => $request->status[$key] ?? 'present',
                    'remarks'         => $request->remarks[$key] ?? null,
                ]);
            }
        }

        // Milk
        $this->saveMilkGrid($report, $request);

        $this->saveFeedGrid($report, $request, $feedsById, $feedConsumption);

        // Pregnancy
        if ($request->pregnancy_buffalo_id) {
            foreach ($request->pregnancy_buffalo_id as $key => $buffaloId) {

                if (!$buffaloId) {
                    continue;
                }

                DailyReportPregnancy::create([
                    'daily_report_id' => $report->id,
                    'buffalo_id'      => $buffaloId,
                    'checkup_date'    => $request->pregnant_date[$key] ?? null,
                    'status'          => 'pregnant',
                    'remarks'         => null,
                ]);
            }
        }

        // Health
        if ($request->health_buffalo_id) {
            foreach ($request->health_buffalo_id as $key => $buffaloId) {

                if (!$buffaloId) continue;

                DailyReportHealth::create([
                    'daily_report_id' => $report->id,
                    'buffalo_id'      => $buffaloId,
                    'health_issue'    => $request->health_issue[$key] ?? '',
                    'treatment'       => $request->treatment[$key] ?? '',
                    'medicine_cost'   => $request->medicine_cost[$key] ?? 0,
                ]);
            }
        }

        // Vaccination
        if ($request->vaccination_buffalo_id) {
            foreach ($request->vaccination_buffalo_id as $key => $buffaloId) {
                if (!$buffaloId) {
                    continue;
                }

                DailyReportVaccination::create([
                    'daily_report_id'  => $report->id,
                    'buffalo_id'       => $buffaloId,
                    'vaccine_name'     => $request->vaccine_name[$key] ?? '',
                    'vaccination_date' => $request->vaccination_date[$key] ?? null,
                    'remarks'          => $request->vaccination_remarks[$key] ?? '',
                ]);
            }
        }

        // Expense
        if ($request->expense_title) {

            foreach ($request->expense_title as $key => $title) {

                if (!$title) {
                    continue;
                }

                DailyReportExpense::create([
                    'daily_report_id' => $report->id,
                    'title'           => $title,
                    'amount'          => $request->expense_amount[$key] ?? 0,
                    'remarks'         => $request->expense_remarks[$key] ?? '',
                ]);
            }
        }

        // Income
        if ($request->income_title) {

            foreach ($request->income_title as $key => $title) {

                if (!$title) {
                    continue;
                }

                DailyReportIncome::create([
                    'daily_report_id' => $report->id,
                    'title'           => $title,
                    'amount'          => $request->income_amount[$key] ?? 0,
                    'remarks'         => $request->income_remarks[$key] ?? '',
                ]);
            }
        }

        $this->syncService->syncAll($report->fresh());

        });

        return redirect()
            ->route('daily-reports.index')
            ->with('success', 'Report Updated Successfully');
    }

    public function print(DailyReport $dailyReport)
    {
        return redirect()->route('daily-reports.show', $dailyReport)->withFragment('print');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $report = DailyReport::findOrFail($id);
        FeedStockService::restoreDailyReport($report->id);
        $this->syncService->purgeSynced($report->id);
        $report->delete();

        return redirect()
            ->route('daily-reports.index')
            ->with('success', 'Report Deleted Successfully');
    }
}
