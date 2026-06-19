<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Buffalo;
use App\Models\DailyReportExpense;
use App\Models\Expense;
use App\Models\FarmLoan;
use App\Models\FarmOtherExpense;
use App\Models\FeedPurchase;
use App\Models\InsurancePolicy;
use App\Models\UtilityBill;
use Carbon\Carbon;

class FarmFinancialService
{
    public function __construct(
        protected FarmIncomeService $income
    ) {
    }

    public function expenseSummaryForPeriod(Carbon $from, Carbon $to): array
    {
        $daily = (float) Expense::query()
            ->whereNotNull('daily_report_id')
            ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');

        $feed = (float) FeedPurchase::query()
            ->whereBetween('purchase_date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');

        $feed += (float) Expense::query()
            ->where('category', 'feed')
            ->whereNull('daily_report_id')
            ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');

        $utility = (float) UtilityBill::query()
            ->whereBetween('bill_date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');

        $insurance = (float) InsurancePolicy::query()
            ->whereBetween('start_date', [$from->toDateString(), $to->toDateString()])
            ->sum('premium_amount');

        $loanEmi = (float) FarmLoan::query()
            ->where('emi_amount', '>', 0)
            ->sum('emi_amount');

        $other = (float) FarmOtherExpense::query()
            ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');

        $animalPurchase = (float) Buffalo::query()
            ->where('purchase_price', '>', 0)
            ->whereNotNull('purchase_date')
            ->whereBetween('purchase_date', [$from->toDateString(), $to->toDateString()])
            ->sum('purchase_price');

        $assets = (float) Asset::query()
            ->whereBetween('purchase_date', [$from->toDateString(), $to->toDateString()])
            ->sum('purchase_cost');

        $equipment = (float) Expense::query()
            ->where('category', 'equipment')
            ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');

        $total = round(
            $daily + $feed + $utility + $insurance + $loanEmi + $other + $animalPurchase + $assets + $equipment,
            2
        );

        return compact(
            'daily',
            'feed',
            'utility',
            'insurance',
            'loanEmi',
            'other',
            'animalPurchase',
            'assets',
            'equipment',
            'total'
        );
    }

    public function profitLossForPeriod(Carbon $from, Carbon $to): array
    {
        $income = $this->income->summaryForPeriod($from, $to);
        $expense = $this->expenseSummaryForPeriod($from, $to);
        $net = round($income['total'] - $expense['total'], 2);

        return [
            'income' => $income,
            'expense' => $expense,
            'net_profit' => $net,
        ];
    }

    public function dashboardToday(): array
    {
        $today = today();
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();

        return [
            'today_expenses' => (float) DailyReportExpense::query()
                ->whereHas('dailyReport', fn ($q) => $q->whereDate('report_date', $today))
                ->sum('amount'),
            'month_feed_purchase' => $this->expenseSummaryForPeriod($monthStart, $monthEnd)['feed'],
            'month_utility' => $this->expenseSummaryForPeriod($monthStart, $monthEnd)['utility'],
            'month_insurance' => $this->expenseSummaryForPeriod($monthStart, $monthEnd)['insurance'],
            'month_loan_emi' => $this->expenseSummaryForPeriod($monthStart, $monthEnd)['loanEmi'],
            'month_animal_purchase' => $this->expenseSummaryForPeriod($monthStart, $monthEnd)['animalPurchase'],
            'month_animal_sale' => $this->income->animalSaleIncome($monthStart, $monthEnd),
            'month_asset_purchase' => $this->expenseSummaryForPeriod($monthStart, $monthEnd)['assets'],
        ];
    }
}
