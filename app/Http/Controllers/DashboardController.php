<?php
namespace App\Http\Controllers;

use App\Models\{Buffalo, MilkEntry, MilkSale, Expense, Income, Employee, Setting, Feed};
use App\Services\MilkStockService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today     = today();
        $thisMonth = now();

        $totalBuffaloes    = Buffalo::totalHeadCount(true);
        $animalTypeCounts  = Buffalo::activeCountsByAnimalType(true);
        $animalTypeSummary = Buffalo::animalTypeSummaryLabel($animalTypeCounts);
        $lactatingCount    = Buffalo::where('status','active')->where('lactation_status','lactating')->count();

        $todayMilk = MilkEntry::whereDate('entry_date', $today)->sum('total_liters');

        $monthMilk = MilkEntry::whereYear('entry_date', $thisMonth->year)
            ->whereMonth('entry_date', $thisMonth->month)
            ->sum('total_liters');

        $milkSaleIncome = MilkSale::whereYear('sale_date', $thisMonth->year)
            ->whereMonth('sale_date', $thisMonth->month)
            ->sum('total_amount');

        $moduleIncome = Income::whereYear('income_date', $thisMonth->year)
            ->whereMonth('income_date', $thisMonth->month)
            ->sum('amount');

        $monthIncome = $milkSaleIncome + $moduleIncome;
        $incomeCount = Income::count();

        $todaySalesAmount = MilkSale::whereDate('sale_date', $today)->sum('total_amount');
        $todaySoldLiters  = MilkSale::whereDate('sale_date', $today)->sum('liters_sold');
        $remainingMilk    = max(0, (float) $todayMilk - (float) $todaySoldLiters);

        $monthExpense = Expense::whereYear('expense_date', $thisMonth->year)
            ->whereMonth('expense_date', $thisMonth->month)
            ->sum('amount');

        $netProfit = $monthIncome - $monthExpense;

        $lowFeedStock = Feed::where('status', 1)
            ->where('min_stock', '>', 0)
            ->withInventoryStats()
            ->get()
            ->filter(fn (Feed $f) => $f->isLowStock());

        $deliveryThisWeek = Buffalo::where('status', 'active')
            ->whereBetween('expected_delivery_date', [$today, $today->copy()->addDays(7)])
            ->count();

        $todayMilkEntered = MilkEntry::whereDate('entry_date', $today)->exists();

        $heatReminders = Buffalo::where('status', 'active')
            ->whereNotNull('heat_date')
            ->whereDate('heat_date', '>=', $today->copy()->subDays(21))
            ->count();

        $last7 = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $liters = MilkEntry::whereDate('entry_date', $date)->sum('total_liters');
            $last7->push(['date' => Carbon::parse($date)->format('d/m'), 'liters' => $liters]);
        }

        $expenseBreakdown = Expense::whereYear('expense_date', $thisMonth->year)
            ->whereMonth('expense_date', $thisMonth->month)
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->get();

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

        $pendingSalary = Employee::where('status','active')->get()
            ->sum(fn($e) => $e->pendingMonths() * $e->monthly_salary);

        $pregnantCount = Buffalo::where('status', 'active')
            ->where('lactation_status', 'pregnant')
            ->count();

        $userName = auth()->user()->name ?? 'Admin';

        $settings = [
            'farm_name'     => Setting::get('farm_name', 'મારો તબેલો'),
            'primary_color' => Setting::get('primary_color', '#16a34a'),
            'currency'      => Setting::get('currency', '₹'),
        ];

        return view('dashboard.index', compact(
            'totalBuffaloes','animalTypeCounts','animalTypeSummary','lactatingCount','pregnantCount','userName',
            'todayMilk','monthMilk',
            'monthIncome','moduleIncome','incomeCount','monthExpense','netProfit',
            'todaySalesAmount','todaySoldLiters','remainingMilk',
            'lowFeedStock','deliveryThisWeek','todayMilkEntered','heatReminders',
            'last7','expenseBreakdown','topBuffaloes','pendingSalary','settings'
        ));
    }
}
