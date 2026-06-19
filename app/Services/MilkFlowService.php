<?php

namespace App\Services;

use App\Models\DairyCollection;
use App\Models\MilkDistribution;
use App\Models\MilkEntry;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MilkFlowService
{
    public function productionForDate(Carbon|string $date): array
    {
        $dateStr = Carbon::parse($date)->toDateString();

        $row = MilkEntry::query()
            ->join('buffaloes', 'milk_entries.buffalo_id', '=', 'buffaloes.id')
            ->whereDate('milk_entries.entry_date', $dateStr)
            ->whereIn('buffaloes.animal_type', ['buffalo', 'cow'])
            ->selectRaw("
                COALESCE(SUM(CASE WHEN buffaloes.animal_type = 'buffalo' THEN milk_entries.total_liters ELSE 0 END), 0) as buffalo,
                COALESCE(SUM(CASE WHEN buffaloes.animal_type = 'cow' THEN milk_entries.total_liters ELSE 0 END), 0) as cow,
                COALESCE(SUM(milk_entries.total_liters), 0) as total
            ")
            ->first();

        return [
            'buffalo' => round((float) ($row->buffalo ?? 0), 2),
            'cow' => round((float) ($row->cow ?? 0), 2),
            'total' => round((float) ($row->total ?? 0), 2),
        ];
    }

    public function distributionForDate(Carbon|string $date): array
    {
        $dateStr = Carbon::parse($date)->toDateString();

        $rows = MilkDistribution::query()
            ->whereDate('date', $dateStr)
            ->selectRaw('milk_type, COALESCE(SUM(total_liter), 0) as liters, COALESCE(SUM(amount), 0) as income')
            ->groupBy('milk_type')
            ->get()
            ->keyBy('milk_type');

        $buffalo = round((float) ($rows->get('buffalo')?->liters ?? 0), 2);
        $cow = round((float) ($rows->get('cow')?->liters ?? 0), 2);
        $income = round(
            (float) MilkDistribution::whereDate('date', $dateStr)->sum('amount'),
            2
        );

        return [
            'buffalo' => $buffalo,
            'cow' => $cow,
            'total' => round($buffalo + $cow, 2),
            'customer_income' => $income,
        ];
    }

    public function dairyForDate(Carbon|string $date): array
    {
        $dateStr = Carbon::parse($date)->toDateString();

        $row = DairyCollection::query()
            ->whereDate('date', $dateStr)
            ->selectRaw('
                COALESCE(SUM(buffalo_liter), 0) as buffalo_liter,
                COALESCE(SUM(cow_liter), 0) as cow_liter,
                COALESCE(SUM(buffalo_amount), 0) as buffalo_amount,
                COALESCE(SUM(cow_amount), 0) as cow_amount
            ')
            ->first();

        $buffaloLiter = round((float) ($row->buffalo_liter ?? 0), 2);
        $cowLiter = round((float) ($row->cow_liter ?? 0), 2);
        $dairyIncome = round((float) ($row->buffalo_amount ?? 0) + (float) ($row->cow_amount ?? 0), 2);

        return [
            'buffalo_liter' => $buffaloLiter,
            'cow_liter' => $cowLiter,
            'total_liter' => round($buffaloLiter + $cowLiter, 2),
            'dairy_income' => $dairyIncome,
            'buffalo_amount' => round((float) ($row->buffalo_amount ?? 0), 2),
            'cow_amount' => round((float) ($row->cow_amount ?? 0), 2),
        ];
    }

    public function reconciliationForDate(Carbon|string $date): array
    {
        $production = $this->productionForDate($date);
        $distribution = $this->distributionForDate($date);
        $dairy = $this->dairyForDate($date);

        $buffaloRemaining = round($production['buffalo'] - $distribution['buffalo'], 2);
        $cowRemaining = round($production['cow'] - $distribution['cow'], 2);

        $buffaloDiff = round($buffaloRemaining - $dairy['buffalo_liter'], 2);
        $cowDiff = round($cowRemaining - $dairy['cow_liter'], 2);

        $unaccounted = round(
            $production['total'] - $distribution['total'] - $dairy['total_liter'],
            2
        );

        return [
            'date' => Carbon::parse($date)->toDateString(),
            'production' => $production,
            'distribution' => $distribution,
            'dairy' => $dairy,
            'remaining' => [
                'buffalo' => $buffaloRemaining,
                'cow' => $cowRemaining,
                'total' => round($buffaloRemaining + $cowRemaining, 2),
            ],
            'buffalo_diff' => $buffaloDiff,
            'cow_diff' => $cowDiff,
            'unaccounted' => $unaccounted,
            'is_balanced' => abs($buffaloDiff) < 0.01 && abs($cowDiff) < 0.01 && abs($unaccounted) < 0.01,
            'customer_income' => $distribution['customer_income'],
            'dairy_income' => $dairy['dairy_income'],
            'total_income' => round($distribution['customer_income'] + $dairy['dairy_income'], 2),
        ];
    }

    public function reconciliationRange(string $from, string $to): Collection
    {
        $dates = MilkEntry::query()
            ->whereBetween('entry_date', [$from, $to])
            ->selectRaw('DISTINCT entry_date as d')
            ->pluck('d')
            ->merge(
                MilkDistribution::whereBetween('date', [$from, $to])->selectRaw('DISTINCT date as d')->pluck('d')
            )
            ->merge(
                DairyCollection::whereBetween('date', [$from, $to])->selectRaw('DISTINCT date as d')->pluck('d')
            )
            ->unique()
            ->sort()
            ->values();

        return $dates->map(fn ($d) => $this->reconciliationForDate($d));
    }

    public function todaySummary(): array
    {
        return $this->reconciliationForDate(today());
    }

    public function validateDairyEntry(
        Carbon|string $date,
        float $buffaloLiter,
        float $cowLiter,
        ?int $excludeCollectionId = null
    ): array {
        $dateStr = Carbon::parse($date)->toDateString();
        $production = $this->productionForDate($dateStr);
        $distribution = $this->distributionForDate($dateStr);

        $expectedBuffalo = round($production['buffalo'] - $distribution['buffalo'], 2);
        $expectedCow = round($production['cow'] - $distribution['cow'], 2);

        $dairyQuery = DairyCollection::whereDate('date', $dateStr);
        if ($excludeCollectionId) {
            $dairyQuery->where('id', '!=', $excludeCollectionId);
        }

        $existingBuffalo = (float) (clone $dairyQuery)->sum('buffalo_liter');
        $existingCow = (float) (clone $dairyQuery)->sum('cow_liter');

        $totalBuffalo = round($existingBuffalo + $buffaloLiter, 2);
        $totalCow = round($existingCow + $cowLiter, 2);

        $buffaloDiff = round($expectedBuffalo - $totalBuffalo, 2);
        $cowDiff = round($expectedCow - $totalCow, 2);

        return [
            'expected_buffalo' => $expectedBuffalo,
            'expected_cow' => $expectedCow,
            'entered_buffalo' => $totalBuffalo,
            'entered_cow' => $totalCow,
            'buffalo_diff' => $buffaloDiff,
            'cow_diff' => $cowDiff,
            'has_mismatch' => abs($buffaloDiff) >= 0.01 || abs($cowDiff) >= 0.01,
        ];
    }
}
