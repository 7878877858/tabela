<?php

namespace App\Services;

use App\Models\Buffalo;
use App\Models\DailyReport;
use App\Models\Income;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DailyReportIncomeService
{
    public function viewData(?DailyReport $report = null): array
    {
        $manureIncomes = collect();
        $animalIncomes = collect();
        $otherIncomes = collect();

        if ($report) {
            $rows = Income::where('daily_report_id', $report->id)->get();
            $manureIncomes = $rows->where('category', Income::CATEGORY_MANURE)->values();
            $animalIncomes = $rows->where('category', Income::CATEGORY_ANIMAL)->values();
            $otherIncomes = $rows->where('category', Income::CATEGORY_OTHER)->values();
        }

        $soldViaReport = $animalIncomes->pluck('buffalo_id')->filter();
        $saleAnimals = Buffalo::query()
            ->where(function ($q) use ($soldViaReport) {
                $q->where('status', 'active');
                if ($soldViaReport->isNotEmpty()) {
                    $q->orWhereIn('id', $soldViaReport);
                }
            })
            ->orderBy('tag_number')
            ->get();

        return compact('manureIncomes', 'animalIncomes', 'otherIncomes', 'saleAnimals');
    }

    public function saveForReport(DailyReport $report, Request $request): void
    {
        $reportDate = $request->report_date ?? $report->report_date;

        DB::transaction(function () use ($report, $request, $reportDate) {
            $this->purgeForReport($report);

            $this->saveManureRows($report, $request, $reportDate);
            $this->saveAnimalSaleRows($report, $request, $reportDate);
            $this->saveOtherRows($report, $request, $reportDate);
        });
    }

    public function purgeForReport(DailyReport $report, bool $revertAnimals = true): void
    {
        $animalSaleIds = Income::where('daily_report_id', $report->id)
            ->where('category', Income::CATEGORY_ANIMAL)
            ->pluck('buffalo_id')
            ->filter();

        Income::where('daily_report_id', $report->id)->delete();

        if ($revertAnimals && $animalSaleIds->isNotEmpty()) {
            Buffalo::whereIn('id', $animalSaleIds)
                ->where('status', 'sold')
                ->update([
                    'status' => 'active',
                    'sold_date' => null,
                    'sale_price' => null,
                    'buyer_name' => null,
                ]);
        }
    }

    protected function saveManureRows(DailyReport $report, Request $request, $reportDate): void
    {
        $weights = $request->input('manure_weight_kg', []);
        $rates = $request->input('manure_rate_per_kg', []);
        $buyers = $request->input('manure_buyer_name', []);
        $remarks = $request->input('manure_remarks', []);

        foreach ($weights as $key => $weight) {
            $weight = (float) $weight;
            $rate = (float) ($rates[$key] ?? 0);
            if ($weight <= 0 || $rate <= 0) {
                continue;
            }

            Income::create([
                'daily_report_id' => $report->id,
                'income_date' => $reportDate,
                'category' => Income::CATEGORY_MANURE,
                'description' => __('income.manure_sale'),
                'amount' => round($weight * $rate, 2),
                'weight_kg' => $weight,
                'rate_per_kg' => $rate,
                'buyer_name' => $buyers[$key] ?? null,
                'remarks' => $remarks[$key] ?? null,
            ]);
        }
    }

    protected function saveAnimalSaleRows(DailyReport $report, Request $request, $reportDate): void
    {
        $animalIds = $request->input('animal_sale_buffalo_id', []);
        $amounts = $request->input('animal_sale_amount', []);
        $buyers = $request->input('animal_sale_buyer_name', []);
        $remarks = $request->input('animal_sale_remarks', []);

        foreach ($animalIds as $key => $animalId) {
            if (!$animalId) {
                continue;
            }

            $amount = (float) ($amounts[$key] ?? 0);
            if ($amount <= 0) {
                continue;
            }

            $animal = Buffalo::find($animalId);
            if (!$animal) {
                continue;
            }

            Income::create([
                'daily_report_id' => $report->id,
                'income_date' => $reportDate,
                'category' => Income::CATEGORY_ANIMAL,
                'description' => __('income.animal_sale') . ' — ' . $animal->display_label,
                'amount' => $amount,
                'buffalo_id' => $animal->id,
                'buyer_name' => $buyers[$key] ?? null,
                'remarks' => $remarks[$key] ?? null,
            ]);

            $animal->update([
                'status' => 'sold',
                'sold_date' => $reportDate,
                'sale_price' => $amount,
                'buyer_name' => $buyers[$key] ?? null,
            ]);
        }
    }

    protected function saveOtherRows(DailyReport $report, Request $request, $reportDate): void
    {
        $titles = $request->input('other_income_title', []);
        $amounts = $request->input('other_income_amount', []);
        $remarks = $request->input('other_income_remarks', []);

        foreach ($titles as $key => $title) {
            if (!$title) {
                continue;
            }

            $amount = (float) ($amounts[$key] ?? 0);
            if ($amount <= 0) {
                continue;
            }

            Income::create([
                'daily_report_id' => $report->id,
                'income_date' => $reportDate,
                'category' => Income::CATEGORY_OTHER,
                'description' => $title,
                'amount' => $amount,
                'remarks' => $remarks[$key] ?? null,
            ]);
        }
    }
}
