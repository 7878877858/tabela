<?php

namespace App\Services;

use App\Models\AssetMaintenance;
use App\Models\Buffalo;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\Feed;
use App\Models\Income;
use App\Models\MilkEntry;
use App\Models\MilkSale;
use App\Models\Setting;
use App\Services\AnimalAlertService;
use App\Services\FarmFinancialService;
use App\Services\FarmIncomeService;
use App\Services\MilkFlowService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardService
{
    public function build(): array
    {
        $today = today();
        $thisMonth = now();

        $animalTypeCounts = Buffalo::activeCountsByAnimalType(true);
        $totalAnimals = Buffalo::totalHeadCount(true);
        $lactatingCount = Buffalo::where('status', 'active')->where('lactation_status', 'lactating')->count();
        $pregnantCount = Buffalo::where('status', 'active')->where('lactation_status', 'pregnant')->count();

        $todayMilk = (float) MilkEntry::whereDate('entry_date', $today)->sum('total_liters');
        $todayMilkEntered = $todayMilk > 0 || MilkEntry::whereDate('entry_date', $today)->exists();

        $monthMilk = (float) MilkEntry::whereYear('entry_date', $thisMonth->year)
            ->whereMonth('entry_date', $thisMonth->month)
            ->sum('total_liters');

        $farmIncomeService = app(FarmIncomeService::class);
        $farmFinancial = app(FarmFinancialService::class);
        $incomeSummary = $farmIncomeService->summaryForMonth($thisMonth->year, $thisMonth->month);
        $financialToday = $farmFinancial->dashboardToday();
        $monthIncome = $incomeSummary['total'];
        $monthExpense = (float) Expense::whereYear('expense_date', $thisMonth->year)
            ->whereMonth('expense_date', $thisMonth->month)
            ->sum('amount');

        $netOperational = $monthIncome - $monthExpense;

        $todaySalesAmount = (float) MilkSale::whereDate('sale_date', $today)->sum('total_amount');
        $todaySoldLiters = (float) MilkSale::whereDate('sale_date', $today)->sum('liters_sold');
        $milkStock = MilkStockService::currentBalance();

        $lowFeedStock = Feed::where('status', 1)
            ->where('min_stock', '>', 0)
            ->withInventoryStats()
            ->get()
            ->filter(fn (Feed $f) => $f->isLowStock());

        $deliveryThisWeek = Buffalo::where('status', 'active')
            ->whereBetween('expected_delivery_date', [$today, $today->copy()->addDays(7)])
            ->count();

        $alertService = app(AnimalAlertService::class);
        $vaccinationDueCount = $alertService->vaccinationDueCount();
        $pregnancyCheckDueCount = $alertService->pregnancyCheckDueCount();
        $treatmentFollowUpCount = $alertService->treatmentFollowUpCount();

        $milkFlow = app(MilkFlowService::class)->todaySummary();
        $milkFlowAlert = abs($milkFlow['unaccounted']) >= 0.01 && $milkFlow['production']['total'] > 0
            ? $milkFlow['unaccounted']
            : 0;

        $heatReminders = Buffalo::where('status', 'active')
            ->whereNotNull('heat_date')
            ->whereDate('heat_date', '>=', $today->copy()->subDays(21))
            ->count();

        $upcomingAssetServices = AssetMaintenance::whereNotNull('next_service_date')
            ->whereBetween('next_service_date', [$today, $today->copy()->addDays(30)])
            ->count();

        $pendingSalary = Employee::where('status', 'active')->get()
            ->sum(fn ($e) => $e->pendingMonths() * $e->monthly_salary);

        $last7 = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $liters = (float) MilkEntry::whereDate('entry_date', $date)->sum('total_liters');
            $last7->push([
                'date' => Carbon::parse($date)->format('d/m'),
                'liters' => $liters,
            ]);
        }

        $topProducers = Buffalo::with(['milkEntries' => function ($q) use ($thisMonth) {
            $q->whereYear('entry_date', $thisMonth->year)
                ->whereMonth('entry_date', $thisMonth->month);
        }])
            ->where('status', 'active')
            ->get()
            ->map(fn ($b) => [
                'tag' => $b->tag_number,
                'name' => $b->name ?? $b->tag_number,
                'type_label' => $b->animal_type_label ?? $b->tag_number,
                'total' => (float) $b->milkEntries->sum('total_liters'),
            ])
            ->filter(fn ($b) => $b['total'] > 0)
            ->sortByDesc('total')
            ->take(5)
            ->values();

        $maxProducerLiters = $topProducers->max('total') ?: 1;

        $healthScore = $this->computeHealthScore([
            'today_milk_entered' => $todayMilkEntered,
            'low_feed_count' => $lowFeedStock->count(),
            'pending_salary' => $pendingSalary,
            'upcoming_asset_services' => $upcomingAssetServices,
            'heat_count' => $heatReminders,
        ]);

        $alerts = $this->buildAlerts(
            $todayMilkEntered,
            $lowFeedStock,
            $deliveryThisWeek,
            $heatReminders,
            $pendingSalary,
            $upcomingAssetServices,
            $vaccinationDueCount,
            $pregnancyCheckDueCount,
            $treatmentFollowUpCount,
            $milkFlowAlert
        );

        $settings = [
            'farm_name' => Setting::get('farm_name', 'મારો તબેલો'),
            'primary_color' => Setting::get('primary_color', '#1d4ed8'),
            'currency' => Setting::get('currency', '₹'),
        ];

        return compact(
            'settings',
            'today',
            'animalTypeCounts',
            'totalAnimals',
            'lactatingCount',
            'pregnantCount',
            'todayMilk',
            'todayMilkEntered',
            'monthMilk',
            'monthIncome',
            'monthExpense',
            'netOperational',
            'todaySalesAmount',
            'todaySoldLiters',
            'milkStock',
            'lowFeedStock',
            'deliveryThisWeek',
            'heatReminders',
            'vaccinationDueCount',
            'pregnancyCheckDueCount',
            'treatmentFollowUpCount',
            'upcomingAssetServices',
            'pendingSalary',
            'last7',
            'topProducers',
            'maxProducerLiters',
            'healthScore',
            'alerts',
            'milkFlow',
            'incomeSummary',
            'financialToday'
        );
    }

    private function computeHealthScore(array $factors): array
    {
        $score = 100;
        $issues = 0;

        if (!$factors['today_milk_entered']) {
            $score -= 18;
            $issues++;
        }

        $lowFeed = (int) $factors['low_feed_count'];
        if ($lowFeed > 0) {
            $score -= min(24, $lowFeed * 8);
            $issues++;
        }

        if ($factors['pending_salary'] > 0) {
            $score -= 12;
            $issues++;
        }

        $services = (int) $factors['upcoming_asset_services'];
        if ($services > 2) {
            $score -= min(10, ($services - 2) * 3);
            $issues++;
        }

        if ($factors['heat_count'] > 5) {
            $score -= 6;
        }

        $score = max(0, min(100, $score));

        $grade = match (true) {
            $score >= 85 => 'excellent',
            $score >= 70 => 'good',
            $score >= 50 => 'fair',
            default => 'critical',
        };

        return [
            'score' => $score,
            'grade' => $grade,
            'issues' => $issues,
        ];
    }

    private function buildAlerts(
        bool $todayMilkEntered,
        Collection $lowFeedStock,
        int $deliveryThisWeek,
        int $heatReminders,
        float $pendingSalary,
        int $upcomingAssetServices,
        int $vaccinationDueCount = 0,
        int $pregnancyCheckDueCount = 0,
        int $treatmentFollowUpCount = 0,
        float $milkUnaccounted = 0
    ): Collection {
        $alerts = collect();
        $currency = Setting::get('currency', '₹');

        if (abs($milkUnaccounted) >= 0.01) {
            $alerts->push([
                'level' => 'warning',
                'icon' => '⚠️',
                'message' => __('milk_flow.reconciliation_error') . ' — ' . __('milk_flow.unaccounted_alert', ['liters' => number_format(abs($milkUnaccounted), 1)]),
                'route' => route('reports.milk-reconciliation'),
            ]);
        }

        if (!$todayMilkEntered) {
            $alerts->push([
                'level' => 'danger',
                'icon' => '🥛',
                'message' => __('dashboard.alert_no_milk'),
                'route' => route('daily-reports.create'),
            ]);
        }

        foreach ($lowFeedStock->take(3) as $feed) {
            $alerts->push([
                'level' => 'warning',
                'icon' => '🌾',
                'message' => __('dashboard.alert_low_feed', [
                    'name' => $feed->name,
                    'qty' => number_format($feed->available_quantity, 1),
                    'unit' => $feed->unit,
                ]),
                'route' => route('feeds.index'),
            ]);
        }

        if ($lowFeedStock->count() > 3) {
            $alerts->push([
                'level' => 'warning',
                'icon' => '🌾',
                'message' => __('dashboard.alert_more_low_feed', ['count' => $lowFeedStock->count() - 3]),
                'route' => route('feeds.index'),
            ]);
        }

        if ($deliveryThisWeek > 0) {
            $alerts->push([
                'level' => 'info',
                'icon' => '🤰',
                'message' => __('dashboard.alert_delivery_week', ['count' => $deliveryThisWeek]),
                'route' => route('daily-reports.create'),
            ]);
        }

        if ($vaccinationDueCount > 0) {
            $alerts->push([
                'level' => 'warning',
                'icon' => '💉',
                'message' => __('dashboard.alert_vaccination_due', ['count' => $vaccinationDueCount]),
                'route' => route('daily-reports.create'),
            ]);
        }

        if ($pregnancyCheckDueCount > 0) {
            $alerts->push([
                'level' => 'warning',
                'icon' => '🩺',
                'message' => __('dashboard.alert_pregnancy_check_due', ['count' => $pregnancyCheckDueCount]),
                'route' => route('daily-reports.create'),
            ]);
        }

        if ($treatmentFollowUpCount > 0) {
            $alerts->push([
                'level' => 'warning',
                'icon' => '🏥',
                'message' => __('dashboard.alert_treatment_followup', ['count' => $treatmentFollowUpCount]),
                'route' => route('daily-reports.create'),
            ]);
        }

        if ($heatReminders > 0) {
            $alerts->push([
                'level' => 'info',
                'icon' => '🔥',
                'message' => __('dashboard.alert_heat', ['count' => $heatReminders]),
                'route' => route('buffalo.index'),
            ]);
        }

        if ($pendingSalary > 0) {
            $alerts->push([
                'level' => 'warning',
                'icon' => '💰',
                'message' => __('dashboard.alert_pending_salary', [
                    'amount' => $currency . number_format($pendingSalary, 0),
                ]),
                'route' => route('employees.index'),
            ]);
        }

        if ($upcomingAssetServices > 0) {
            $alerts->push([
                'level' => 'info',
                'icon' => '🔧',
                'message' => __('dashboard.alert_asset_service', ['count' => $upcomingAssetServices]),
                'route' => route('reports.assets', ['report' => 'upcoming']),
            ]);
        }

        if ($alerts->isEmpty()) {
            $alerts->push([
                'level' => 'success',
                'icon' => '✅',
                'message' => __('dashboard.alert_all_clear'),
                'route' => null,
            ]);
        }

        return $alerts;
    }
}
