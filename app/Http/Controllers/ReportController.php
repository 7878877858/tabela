<?php
namespace App\Http\Controllers;

use App\Models\{Buffalo, MilkEntry, MilkSale, Expense, Income};
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function monthly(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year  = $request->get('year', now()->year);

        // Daily milk summary
        $dailyMilk = MilkEntry::whereYear('entry_date', $year)
            ->whereMonth('entry_date', $month)
            ->selectRaw('entry_date, SUM(total_liters) as total')
            ->groupBy('entry_date')->orderBy('entry_date')->get();

        // Per-buffalo summary
        $buffaloSummary = Buffalo::with(['milkEntries' => function ($q) use ($month, $year) {
            $q->whereYear('entry_date', $year)->whereMonth('entry_date', $month);
        }])->where('status','active')->get()->map(fn($b) => [
            'tag'        => $b->tag_number,
            'name'       => $b->name ?? $b->tag_number,
            'total'      => $b->milkEntries->sum('total_liters'),
            'days'       => $b->milkEntries->count(),
            'avg'        => $b->milkEntries->count()
                ? round($b->milkEntries->sum('total_liters') / $b->milkEntries->count(), 2)
                : 0,
        ])->sortByDesc('total');

        $totalMilk    = $dailyMilk->sum('total');
        $milkSaleIncome = MilkSale::whereYear('sale_date',$year)->whereMonth('sale_date',$month)->sum('total_amount');
        $moduleIncome = Income::whereYear('income_date',$year)->whereMonth('income_date',$month)->sum('amount');
        $totalIncome  = $milkSaleIncome + $moduleIncome;
        $totalExpense = Expense::whereYear('expense_date',$year)->whereMonth('expense_date',$month)->sum('amount');
        $netProfit    = $totalIncome - $totalExpense;

        $expenseByCategory = Expense::whereYear('expense_date',$year)
            ->whereMonth('expense_date',$month)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')->get();

        return view('reports.monthly', compact(
            'dailyMilk','buffaloSummary','totalMilk',
            'totalIncome','totalExpense','netProfit',
            'expenseByCategory','month','year'
        ));
    }

    public function yearly(Request $request)
    {
        $year = $request->get('year', now()->year);

        $monthly = collect(range(1,12))->map(function ($m) use ($year) {
            $milkSale = MilkSale::whereYear('sale_date',$year)->whereMonth('sale_date',$m)->sum('total_amount');
            $otherIncome = Income::whereYear('income_date',$year)->whereMonth('income_date',$m)->sum('amount');

            return [
                'month'   => $m,
                'milk'    => MilkEntry::whereYear('entry_date',$year)->whereMonth('entry_date',$m)->sum('total_liters'),
                'income'  => $milkSale + $otherIncome,
                'expense' => Expense::whereYear('expense_date',$year)->whereMonth('expense_date',$m)->sum('amount'),
            ];
        })->map(fn($r) => array_merge($r, ['profit' => $r['income'] - $r['expense']]));

        $totalMilk    = $monthly->sum('milk');
        $totalIncome  = $monthly->sum('income');
        $totalExpense = $monthly->sum('expense');
        $totalProfit  = $totalIncome - $totalExpense;

        return view('reports.yearly', compact('monthly','year','totalMilk','totalIncome','totalExpense','totalProfit'));
    }
}