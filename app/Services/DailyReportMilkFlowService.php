<?php

namespace App\Services;

use App\Models\Buffalo;
use App\Models\DairyCollection;
use App\Models\DailyReport;
use App\Models\MilkDistribution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DailyReportMilkFlowService
{
    public const INCOME_CUSTOMER_TITLE = 'ગ્રાહક દૂધ વિતરણ';

    public const INCOME_DAIRY_TITLE = 'ડેરી કલેકશન';

    public function nextDairySlipNumber(): string
    {
        $nextId = ((int) DairyCollection::max('id')) + 1;

        return 'SLIP-' . str_pad((string) $nextId, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Production totals from milk grid payload keyed by buffalo id.
     *
     * @param  array<int, array{morning?: mixed, evening?: mixed}>  $grid
     * @param  iterable<int, Buffalo>  $animals
     */
    public function productionFromMilkGrid(array $grid, iterable $animals): array
    {
        $typeMap = [];
        foreach ($animals as $animal) {
            $typeMap[(int) $animal->id] = Buffalo::normalizeAnimalType($animal->animal_type);
        }

        $buffalo = 0.0;
        $cow = 0.0;

        foreach ($grid as $buffaloId => $values) {
            $id = (int) $buffaloId;
            if (!$id || !isset($typeMap[$id])) {
                continue;
            }

            $morning = max(0, (float) ($values['morning'] ?? 0));
            $evening = max(0, (float) ($values['evening'] ?? 0));
            $total = $morning + $evening;

            if ($typeMap[$id] === 'cow') {
                $cow += $total;
            } else {
                $buffalo += $total;
            }
        }

        return [
            'buffalo' => round($buffalo, 2),
            'cow' => round($cow, 2),
            'total' => round($buffalo + $cow, 2),
        ];
    }

    public function distributionFromRequest(Request $request): array
    {
        $buffalo = 0.0;
        $cow = 0.0;
        $income = 0.0;

        $customerIds = $request->input('dist_customer_id', []);
        $types = $request->input('dist_milk_type', []);
        $mornings = $request->input('dist_morning_liter', []);
        $evenings = $request->input('dist_evening_liter', []);
        $rates = $request->input('dist_rate_per_liter', []);

        foreach ($customerIds as $key => $customerId) {
            if (!$customerId) {
                continue;
            }

            $morning = max(0, (float) ($mornings[$key] ?? 0));
            $evening = max(0, (float) ($evenings[$key] ?? 0));
            $rate = max(0, (float) ($rates[$key] ?? 0));
            $total = round($morning + $evening, 2);

            if ($total <= 0) {
                continue;
            }

            $type = ($types[$key] ?? 'buffalo') === 'cow' ? 'cow' : 'buffalo';
            if ($type === 'cow') {
                $cow += $total;
            } else {
                $buffalo += $total;
            }

            $income += round($total * $rate, 2);
        }

        return [
            'buffalo' => round($buffalo, 2),
            'cow' => round($cow, 2),
            'total' => round($buffalo + $cow, 2),
            'customer_income' => round($income, 2),
        ];
    }

    public function saveForReport(DailyReport $report, Request $request, iterable $milkAnimals): void
    {
        $existingSlip = DairyCollection::where('daily_report_id', $report->id)->value('slip_image');

        $this->purgeForReport($report, deleteSlipFiles: false);

        $reportDate = $request->report_date ?? $report->report_date;
        $grid = $request->input('milk_grid', []);
        $production = $this->productionFromMilkGrid($grid, $milkAnimals);
        $distribution = $this->distributionFromRequest($request);

        $this->saveDistributions($report, $request, $reportDate);
        $this->saveDairyCollection($report, $request, $reportDate, $production, $distribution, $existingSlip);
    }

    public function purgeForReport(DailyReport $report, bool $deleteSlipFiles = true): void
    {
        DairyCollection::where('daily_report_id', $report->id)->get()->each(function (DairyCollection $row) use ($deleteSlipFiles) {
            if ($deleteSlipFiles && $row->slip_image) {
                Storage::disk('public')->delete($row->slip_image);
            }
            $row->delete();
        });

        MilkDistribution::where('daily_report_id', $report->id)->delete();
    }

    protected function saveDistributions(DailyReport $report, Request $request, $reportDate): void
    {
        $customerIds = $request->input('dist_customer_id', []);
        $types = $request->input('dist_milk_type', []);
        $mornings = $request->input('dist_morning_liter', []);
        $evenings = $request->input('dist_evening_liter', []);
        $rates = $request->input('dist_rate_per_liter', []);
        $notes = $request->input('dist_notes', []);

        foreach ($customerIds as $key => $customerId) {
            if (!$customerId) {
                continue;
            }

            $morning = max(0, (float) ($mornings[$key] ?? 0));
            $evening = max(0, (float) ($evenings[$key] ?? 0));
            $rate = max(0, (float) ($rates[$key] ?? 0));
            $totals = MilkDistribution::computeTotals($morning, $evening, $rate);

            if ($totals['total_liter'] <= 0) {
                continue;
            }

            MilkDistribution::create([
                'daily_report_id' => $report->id,
                'date' => $reportDate,
                'customer_id' => $customerId,
                'milk_type' => ($types[$key] ?? 'buffalo') === 'cow' ? 'cow' : 'buffalo',
                'morning_liter' => $morning,
                'evening_liter' => $evening,
                'rate_per_liter' => $rate,
                'total_liter' => $totals['total_liter'],
                'amount' => $totals['amount'],
                'notes' => $notes[$key] ?? null,
            ]);
        }
    }

    protected function saveDairyCollection(
        DailyReport $report,
        Request $request,
        $reportDate,
        array $production,
        array $distribution,
        ?string $existingSlip = null
    ): void {
        $buffaloLiter = max(0, round($production['buffalo'] - $distribution['buffalo'], 2));
        $cowLiter = max(0, round($production['cow'] - $distribution['cow'], 2));

        $hasDairyData = $buffaloLiter > 0
            || $cowLiter > 0
            || $request->filled('dairy_slip_number')
            || $request->hasFile('dairy_slip_image')
            || $request->filled('dairy_buffalo_amount')
            || $request->filled('dairy_cow_amount')
            || $request->filled('dairy_notes');

        if (!$hasDairyData) {
            return;
        }

        $slipPath = $existingSlip;
        if ($request->hasFile('dairy_slip_image')) {
            if ($existingSlip) {
                Storage::disk('public')->delete($existingSlip);
            }
            $slipPath = $request->file('dairy_slip_image')->store('dairy-slips', 'public');
        }

        DairyCollection::create([
            'daily_report_id' => $report->id,
            'date' => $reportDate,
            'buffalo_liter' => $buffaloLiter,
            'buffalo_fat' => $request->input('dairy_buffalo_fat'),
            'buffalo_snf' => $request->input('dairy_buffalo_snf'),
            'buffalo_amount' => (float) ($request->input('dairy_buffalo_amount') ?? 0),
            'cow_liter' => $cowLiter,
            'cow_fat' => $request->input('dairy_cow_fat'),
            'cow_snf' => $request->input('dairy_cow_snf'),
            'cow_amount' => (float) ($request->input('dairy_cow_amount') ?? 0),
            'slip_number' => $request->input('dairy_slip_number') ?: $this->nextDairySlipNumber(),
            'slip_image' => $slipPath,
            'notes' => $request->input('dairy_notes'),
        ]);
    }
}
