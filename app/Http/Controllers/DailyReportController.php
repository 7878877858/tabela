<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DailyReport;
use App\Models\Buffalo;
use App\Models\MilkEntry;
use App\Models\employee;
use App\Models\Feed;
use App\Models\DailyReportStaff;
use App\Models\DailyReportMilk;
use App\Models\DailyReportFeed;
use App\Models\DailyReportPregnancy;
use App\Models\DailyReportHealth;
use App\Models\DailyReportVaccination;
use App\Models\DailyReportExpense;
use App\Models\DailyReportIncome;


class DailyReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //$reports = DailyReport::latest()->paginate(10);
        $reports = DailyReport::orderBy('report_date', 'desc')->get();

        return view(
            'Daily_Report.index',
            compact('reports')
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = Employee::all();
        $buffaloes = Buffalo::all();
        $feeds = Feed::where('status', 1)->get();
        $totalAnimals = Buffalo::count();

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

        return view(
            'Daily_Report.create',
            compact(
                'employees',
                'committeeMembers',
                'buffaloes',
                'feeds',
                'totalAnimals',
                'lactatingAnimals',
                'pregnantAnimals',
                'dryAnimals',
                'totalMilk',
                'reportNumber'
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'report_date' => 'required',
        ]);

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
        if ($request->buffalo_id) {

            foreach ($request->buffalo_id as $key => $buffaloId) {

                $morning = $request->morning_milk[$key] ?? 0;
                $evening = $request->evening_milk[$key] ?? 0;

                // Daily Report Milk
                DailyReportMilk::create([
                    'daily_report_id' => $report->id,
                    'buffalo_id'      => $buffaloId,
                    'morning_milk'    => $morning,
                    'evening_milk'    => $evening,
                    'total_milk'      => $morning + $evening,
                ]);

                // Milk History mate
                MilkEntry::updateOrCreate(
                    [
                        'buffalo_id' => $buffaloId,
                        'entry_date' => $request->report_date,
                    ],
                    [
                        'morning_liters' => $morning,
                        'evening_liters' => $evening,
                        'total_liters'   => $morning + $evening,
                        'notes'          => 'Daily Report',
                    ]
                );
            }
        }

        /*
    |--------------------------------------------------------------------------
    | Feed
    |--------------------------------------------------------------------------
    */

        if ($request->morning_feed_type) {

            foreach ($request->morning_feed_type as $key => $feed) {

                DailyReportFeed::create([
                    'daily_report_id' => $report->id,
                    'buffalo_id'      => $request->feed_buffalo_id[$key] ?? null,
                    'feed_name'       => $request->morning_feed_type[$key] ?? null,
                    'quantity'        => $request->morning_qty[$key] ?? 0,
                    'feed_time'       => 'morning',
                    'unit'            => 'Kg',
                ]);

                DailyReportFeed::create([
                    'daily_report_id' => $report->id,
                    'buffalo_id'      => $request->feed_buffalo_id[$key] ?? null,
                    'feed_name'       => $request->evening_feed_type[$key] ?? null,
                    'quantity'        => $request->evening_qty[$key] ?? 0,
                    'feed_time'       => 'evening',
                    'unit'            => 'Kg',
                ]);
            }
        }

        /*
    |--------------------------------------------------------------------------
    | Health section
    |--------------------------------------------------------------------------
    */

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

        /*
    |--------------------------------------------------------------------------
    | Pregnancy
    |--------------------------------------------------------------------------
    */

        if ($request->pregnancy_buffalo_id) {

            foreach ($request->pregnancy_buffalo_id as $key => $buffaloId) {

                DailyReportPregnancy::create([
                    'daily_report_id' => $report->id,
                    'buffalo_id'      => $buffaloId,
                    'checkup_date'    => $request->pregnant_date[$key] ?? null,
                    'status'          => 'pregnant',
                    'remarks'         => null,
                ]);
            }
        }

        /*
|--------------------------------------------------------------------------
| Vaccination
|--------------------------------------------------------------------------
*/

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

        /*
|--------------------------------------------------------------------------
| Expense
|--------------------------------------------------------------------------
*/

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

        /*
|--------------------------------------------------------------------------
| Income
|--------------------------------------------------------------------------
*/

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
            'feed',
            'pregnancy.buffalo',
            'health.buffalo',
            'expenses',
            'incomes',
            'vaccinations.buffalo'
        ]);

        $births = Buffalo::whereNotNull('birth_date')->get();
        $totalAnimals = Buffalo::count();

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
        return view(
            'Daily_Report.show',
            compact(
                'dailyReport',
                'totalAnimals',
                'lactatingAnimals',
                'pregnantAnimals',
                'dryAnimals',
                'totalMilk',
                'births'
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
        $feeds = Feed::where('status', 1)->get();

        $totalAnimals = Buffalo::count();

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
        return view(
            'Daily_Report.edit',
            compact(
                'report',
                'employees',
                'committeeMembers',
                'buffaloes',
                'feeds',
                'totalAnimals',
                'lactatingAnimals',
                'pregnantAnimals',
                'dryAnimals',
                'totalMilk'
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
        // Main Report
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
        MilkEntry::whereDate(
            'entry_date',
            $oldDate
        )->whereIn(
            'buffalo_id',
            $request->buffalo_id ?? []
        )->delete();
        // Staff
        if ($request->employee_id) {
            foreach ($request->employee_id as $key => $employeeId) {

                DailyReportStaff::create([
                    'daily_report_id' => $report->id,
                    'employee_id'     => $employeeId,
                    'status'          => $request->status[$key] ?? 'present',
                    'remarks'         => $request->remarks[$key] ?? null,
                ]);
            }
        }

        // Milk
        if ($request->buffalo_id) {
            foreach ($request->buffalo_id as $key => $buffaloId) {

                $morning = $request->morning_milk[$key] ?? 0;
                $evening = $request->evening_milk[$key] ?? 0;

                DailyReportMilk::create([
                    'daily_report_id' => $report->id,
                    'buffalo_id'      => $buffaloId,
                    'morning_milk'    => $morning,
                    'evening_milk'    => $evening,
                    'total_milk'      => $morning + $evening,
                ]);

                // Milk History update
                MilkEntry::updateOrCreate(
                    [
                        'buffalo_id' => $buffaloId,
                        'entry_date' => $report->report_date,
                    ],
                    [
                        'morning_liters' => $morning,
                        'evening_liters' => $evening,
                        'total_liters'   => $morning + $evening,
                    ]
                );
            }
        }

        // Feed
        if ($request->morning_feed_type) {
            foreach ($request->morning_feed_type as $key => $feed) {

                // Morning Feed
                if (!empty($request->morning_feed_type[$key])) {
                    DailyReportFeed::create([
                        'daily_report_id' => $report->id,
                        'buffalo_id'      => $request->feed_buffalo_id[$key],
                        'feed_name'       => $request->morning_feed_type[$key],
                        'quantity'        => $request->morning_qty[$key] ?? 0,
                        'feed_time'       => 'morning',
                        'unit'            => 'Kg',
                    ]);
                }

                // Evening Feed
                if (!empty($request->evening_feed_type[$key])) {
                    DailyReportFeed::create([
                        'daily_report_id' => $report->id,
                        'buffalo_id'      => $request->feed_buffalo_id[$key],
                        'feed_name'       => $request->evening_feed_type[$key],
                        'quantity'        => $request->evening_qty[$key] ?? 0,
                        'feed_time'       => 'evening',
                        'unit'            => 'Kg',
                    ]);
                }
            }
        }

        // Pregnancy
        if ($request->pregnancy_buffalo_id) {
            foreach ($request->pregnancy_buffalo_id as $key => $buffaloId) {

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

        // Expanse
        DailyReportExpense::where('daily_report_id', $report->id)->delete();

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

        //Income
        DailyReportIncome::where('daily_report_id', $report->id)->delete();
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

        return redirect()
            ->route('daily-reports.index')
            ->with('success', 'Report Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DailyReport::findOrFail($id)->delete();

        return redirect()
            ->route('daily-reports.index')
            ->with('success', 'Report Deleted Successfully');
    }
}
