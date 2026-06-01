<?php
namespace App\Http\Controllers;

use App\Models\{Buffalo, MilkEntry, MilkSale, Expense, Employee, Setting};
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today     = today();
        $thisMonth = now();

        // Buffalo stats
        $totalBuffaloes    = Buffalo::where('status','active')->count();
        $lactatingCount    = Buffalo::where('status','active')->where('lactation_status','lactating')->count();

        // Today's milk
        $todayMilk = MilkEntry::whereDate('entry_date', $today)->sum('total_liters');

        // This month milk
        $monthMilk = MilkEntry::whereYear('entry_date', $thisMonth->year)
                               ->whereMonth('entry_date', $thisMonth->month)
                               ->sum('total_liters');

        // This month income
        $monthIncome = MilkSale::whereYear('sale_date', $thisMonth->year)
                                ->whereMonth('sale_date', $thisMonth->month)
                                ->sum('total_amount');

        // This month expense
        $monthExpense = Expense::whereYear('expense_date', $thisMonth->year)
                                ->whereMonth('expense_date', $thisMonth->month)
                                ->sum('amount');

        // Net profit this month
        $netProfit = $monthIncome - $monthExpense;

        // Last 7 days milk chart data
        $last7 = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $liters = MilkEntry::whereDate('entry_date', $date)->sum('total_liters');
            $last7->push(['date' => Carbon::parse($date)->format('d/m'), 'liters' => $liters]);
        }

        // Expense breakdown this month
        $expenseBreakdown = Expense::whereYear('expense_date', $thisMonth->year)
            ->whereMonth('expense_date', $thisMonth->month)
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->get();

        // Top milk producers this month
        $topBuffaloes = Buffalo::with(['milkEntries' => function ($q) use ($thisMonth) {
            $q->whereYear('entry_date', $thisMonth->year)
              ->whereMonth('entry_date', $thisMonth->month);
        }])->where('status','active')
          ->get()
          ->map(fn($b) => [
              'tag'   => $b->tag_number,
              'name'  => $b->name ?? $b->tag_number,
              'total' => $b->milkEntries->sum('total_liters'),
          ])
          ->sortByDesc('total')
          ->take(5);

        // Pending salaries
        $pendingSalary = Employee::where('status','active')->get()
            ->sum(fn($e) => $e->pendingMonths() * $e->monthly_salary);

        $settings = [
            'farm_name'     => Setting::get('farm_name', 'મારો તબેલો'),
            'primary_color' => Setting::get('primary_color', '#16a34a'),
            'currency'      => Setting::get('currency', '₹'),
        ];

        return view('dashboard.index', compact(
            'totalBuffaloes','lactatingCount','todayMilk','monthMilk',
            'monthIncome','monthExpense','netProfit','last7',
            'expenseBreakdown','topBuffaloes','pendingSalary','settings'
        ));
    }
}