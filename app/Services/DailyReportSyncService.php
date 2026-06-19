<?php

namespace App\Services;

use App\Models\DailyReport;
use App\Models\DailyReportExpense;
use App\Models\Expense;
use App\Support\DailyExpenseType;
use App\Models\FeedEntry;
use App\Models\HealthRecord;
use App\Models\Income;
use App\Models\MilkEntry;
use App\Models\VaccinationRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DailyReportSyncService
{
    public function syncAll(DailyReport $report): void
    {
        $report->loadMissing(['milk', 'feed', 'health', 'vaccinations', 'expenses', 'incomes']);

        $this->syncMilk($report);
        $this->syncFeed($report);
        $this->syncHealth($report);
        $this->syncVaccination($report);
        $this->syncExpense($report);
        $this->syncIncome($report);
    }

    public function syncMilk(DailyReport $report): float
    {
        $reportDate = $report->report_date;
        $grandTotal = 0;
        $syncedBuffaloIds = [];

        foreach ($report->milk as $row) {
            if (!$row->buffalo_id) {
                continue;
            }

            $morning = (float) $row->morning_milk;
            $evening = (float) $row->evening_milk;

            if ($morning <= 0 && $evening <= 0) {
                continue;
            }

            $syncedBuffaloIds[] = (int) $row->buffalo_id;
            $grandTotal += $morning + $evening;

            $entry = MilkEntry::updateOrCreate(
                [
                    'buffalo_id' => $row->buffalo_id,
                    'entry_date' => $reportDate,
                ],
                [
                    'morning_liters'  => $morning,
                    'evening_liters'  => $evening,
                    'notes'           => 'Daily Report #' . $report->id,
                    'daily_report_id' => $report->id,
                ]
            );

            MilkStockService::syncProduction($entry->fresh(['buffalo']));
        }

        MilkEntry::where('daily_report_id', $report->id)
            ->when(
                count($syncedBuffaloIds) > 0,
                fn ($q) => $q->whereNotIn('buffalo_id', $syncedBuffaloIds),
                fn ($q) => $q
            )
            ->get()
            ->each(function (MilkEntry $entry) {
                MilkStockService::reverseProduction($entry);
                $entry->delete();
            });

        $report->update(['total_milk' => $grandTotal]);

        return $grandTotal;
    }

    public function syncFeed(DailyReport $report): void
    {
        $reportDate = $report->report_date;
        $syncedKeys = [];

        foreach ($report->feed as $feedRow) {
            if (!$feedRow->buffalo_id) {
                continue;
            }

            foreach (['morning' => $feedRow->morning_feeds ?? [], 'evening' => $feedRow->evening_feeds ?? []] as $period => $feeds) {
                foreach ($feeds as $feedId => $qty) {
                    $qty = (float) $qty;
                    if ($qty <= 0) {
                        continue;
                    }

                    $syncedKeys[] = $feedRow->buffalo_id . ':' . $feedId . ':' . $period;

                    FeedEntry::updateOrCreate(
                        [
                            'daily_report_id' => $report->id,
                            'buffalo_id'      => $feedRow->buffalo_id,
                            'feed_id'         => (int) $feedId,
                            'feed_time'       => $period,
                        ],
                        [
                            'entry_date' => $reportDate,
                            'quantity'   => $qty,
                        ]
                    );
                }
            }
        }

        FeedEntry::where('daily_report_id', $report->id)
            ->get()
            ->each(function (FeedEntry $entry) use ($syncedKeys) {
                $key = $entry->buffalo_id . ':' . $entry->feed_id . ':' . $entry->feed_time;
                if (!in_array($key, $syncedKeys, true)) {
                    $entry->delete();
                }
            });
    }

    public function syncHealth(DailyReport $report): void
    {
        $reportDate = $report->report_date;
        $syncedIds = [];

        foreach ($report->health as $row) {
            if (!$row->buffalo_id || !$row->health_issue) {
                continue;
            }

            $record = HealthRecord::updateOrCreate(
                [
                    'daily_report_id' => $report->id,
                    'buffalo_id'      => $row->buffalo_id,
                    'health_issue'    => $row->health_issue,
                ],
                [
                    'record_date'   => $reportDate,
                    'treatment'     => $row->treatment,
                    'medicine_cost' => $row->medicine_cost ?? 0,
                ]
            );

            $syncedIds[] = $record->id;
        }

        HealthRecord::where('daily_report_id', $report->id)
            ->whereNotIn('id', $syncedIds ?: [0])
            ->delete();
    }

    public function syncVaccination(DailyReport $report): void
    {
        $syncedIds = [];

        foreach ($report->vaccinations as $row) {
            if (!$row->buffalo_id || !$row->vaccine_name) {
                continue;
            }

            $record = VaccinationRecord::updateOrCreate(
                [
                    'daily_report_id' => $report->id,
                    'buffalo_id'      => $row->buffalo_id,
                    'vaccine_name'    => $row->vaccine_name,
                ],
                [
                    'vaccination_date' => $row->vaccination_date ?? $report->report_date,
                    'remarks'          => $row->remarks,
                ]
            );

            $syncedIds[] = $record->id;
        }

        VaccinationRecord::where('daily_report_id', $report->id)
            ->whereNotIn('id', $syncedIds ?: [0])
            ->delete();
    }

    public function syncExpense(DailyReport $report): void
    {
        $syncedIds = [];

        foreach ($report->expenses as $row) {
            $type = $row->expense_type ?: 'other_daily';
            $label = DailyExpenseType::label($type);

            $expense = Expense::updateOrCreate(
                [
                    'daily_report_id' => $report->id,
                    'description'     => $label,
                ],
                [
                    'expense_date' => $report->report_date,
                    'category'     => DailyExpenseType::ledgerCategory($type),
                    'amount'       => $row->amount ?? 0,
                    'notes'        => $row->remarks,
                ]
            );

            $syncedIds[] = $expense->id;
        }

        Expense::where('daily_report_id', $report->id)
            ->whereNotIn('id', $syncedIds ?: [0])
            ->delete();
    }

    public function syncIncome(DailyReport $report): void
    {
        // Manual incomes (manure, animal sale, other) are saved by DailyReportIncomeService.
        // Milk income is derived at report time from milk_distributions and dairy_collections.
    }

    public function purgeSynced(int $dailyReportId): void
    {
        DB::transaction(function () use ($dailyReportId) {
            MilkEntry::where('daily_report_id', $dailyReportId)
                ->get()
                ->each(function (MilkEntry $entry) {
                    MilkStockService::reverseProduction($entry);
                    $entry->delete();
                });

            FeedEntry::where('daily_report_id', $dailyReportId)->delete();
            HealthRecord::where('daily_report_id', $dailyReportId)->delete();
            VaccinationRecord::where('daily_report_id', $dailyReportId)->delete();
            Expense::where('daily_report_id', $dailyReportId)->delete();
            Income::where('daily_report_id', $dailyReportId)->delete();
        });
    }

    public function syncMilkFromRequest(DailyReport $report, Request $request): array
    {
        $grid = $request->input('milk_grid', []);
        $reportDate = $request->input('report_date', $report->report_date);
        $syncedBuffaloIds = [];

        foreach ($grid as $buffaloId => $values) {
            if (!$buffaloId) {
                continue;
            }

            $morning = max(0, (float) ($values['morning'] ?? 0));
            $evening = max(0, (float) ($values['evening'] ?? 0));

            if ($morning <= 0 && $evening <= 0) {
                continue;
            }

            $syncedBuffaloIds[] = (int) $buffaloId;

            $entry = MilkEntry::updateOrCreate(
                [
                    'buffalo_id' => $buffaloId,
                    'entry_date' => $reportDate,
                ],
                [
                    'morning_liters'  => $morning,
                    'evening_liters'  => $evening,
                    'notes'           => 'Daily Report #' . $report->id,
                    'daily_report_id' => $report->id,
                ]
            );

            MilkStockService::syncProduction($entry->fresh(['buffalo']));
        }

        MilkEntry::where('daily_report_id', $report->id)
            ->when(
                count($syncedBuffaloIds) > 0,
                fn ($q) => $q->whereNotIn('buffalo_id', $syncedBuffaloIds),
                fn ($q) => $q
            )
            ->get()
            ->each(function (MilkEntry $entry) {
                MilkStockService::reverseProduction($entry);
                $entry->delete();
            });

        $reportMilk = $report->milk();
        $grandTotal = (float) $reportMilk->sum('total_milk');

        $report->update(['total_milk' => $grandTotal]);

        return [
            'animals' => $reportMilk->count(),
            'morning' => (float) $reportMilk->sum('morning_milk'),
            'evening' => (float) $reportMilk->sum('evening_milk'),
            'total'   => $grandTotal,
        ];
    }
}
