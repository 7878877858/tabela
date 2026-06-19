<?php

namespace App\Services;

use App\Models\DairyCollection;
use App\Models\Income;
use App\Models\MilkDistribution;
use Carbon\Carbon;

class FarmIncomeService
{
    public const MANUAL_CATEGORIES = ['manure_sale', 'animal_sale', 'other_income'];

    public function customerMilkIncome(Carbon $from, Carbon $to): float
    {
        return (float) MilkDistribution::query()
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');
    }

    public function dairyIncome(Carbon $from, Carbon $to): float
    {
        return (float) DairyCollection::query()
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->sum(fn (DairyCollection $r) => (float) $r->buffalo_amount + (float) $r->cow_amount);
    }

    public function categoryIncome(string $category, Carbon $from, Carbon $to): float
    {
        return (float) Income::query()
            ->where('category', $category)
            ->whereBetween('income_date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');
    }

    public function manureIncome(Carbon $from, Carbon $to): float
    {
        return $this->categoryIncome('manure_sale', $from, $to);
    }

    public function animalSaleIncome(Carbon $from, Carbon $to): float
    {
        return $this->categoryIncome('animal_sale', $from, $to);
    }

    public function otherIncome(Carbon $from, Carbon $to): float
    {
        return $this->categoryIncome('other_income', $from, $to);
    }

    public function manualIncome(Carbon $from, Carbon $to): float
    {
        return (float) Income::query()
            ->whereIn('category', self::MANUAL_CATEGORIES)
            ->whereBetween('income_date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');
    }

    public function summaryForPeriod(Carbon $from, Carbon $to): array
    {
        $customerMilk = $this->customerMilkIncome($from, $to);
        $dairy = $this->dairyIncome($from, $to);
        $manure = $this->manureIncome($from, $to);
        $animalSale = $this->animalSaleIncome($from, $to);
        $other = $this->otherIncome($from, $to);
        $total = round($customerMilk + $dairy + $manure + $animalSale + $other, 2);

        return [
            'customer_milk' => round($customerMilk, 2),
            'dairy' => round($dairy, 2),
            'manure' => round($manure, 2),
            'animal_sale' => round($animalSale, 2),
            'other' => round($other, 2),
            'total' => $total,
        ];
    }

    public function summaryForMonth(int $year, int $month): array
    {
        $from = Carbon::create($year, $month, 1)->startOfMonth();
        $to = $from->copy()->endOfMonth();

        return $this->summaryForPeriod($from, $to);
    }

    public function summaryForToday(): array
    {
        $today = today();

        return $this->summaryForPeriod($today, $today);
    }

    public function dailyBreakdown(Carbon $from, Carbon $to): array
    {
        $days = [];
        $cursor = $from->copy();

        while ($cursor->lte($to)) {
            $days[$cursor->toDateString()] = $this->summaryForPeriod($cursor, $cursor);
            $cursor->addDay();
        }

        return $days;
    }
}
