<?php

namespace App\Http\Controllers;

use App\Models\AnimalTransaction;
use App\Models\Asset;
use App\Models\Buffalo;
use App\Models\DailyReportExpense;
use App\Models\Expense;
use App\Models\FarmLoan;
use App\Models\FarmOtherExpense;
use App\Models\FeedPurchase;
use App\Models\InsurancePolicy;
use App\Models\UtilityBill;
use App\Services\FarmFinancialService;
use App\Support\ListPagination;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FarmReportController extends Controller
{
    public function __construct(
        protected FarmFinancialService $financial
    ) {
    }

    private function dateRange(Request $request): array
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->get('date_to', now()->toDateString());

        return [$dateFrom, $dateTo];
    }

    public function dailyExpenses(Request $request)
    {
        [$dateFrom, $dateTo] = $this->dateRange($request);
        $perPage = ListPagination::resolvePerPage($request);

        $records = Expense::query()
            ->whereNotNull('daily_report_id')
            ->whereBetween('expense_date', [$dateFrom, $dateTo])
            ->orderByDesc('expense_date')
            ->paginate($perPage)
            ->withQueryString();

        $total = (float) Expense::query()
            ->whereNotNull('daily_report_id')
            ->whereBetween('expense_date', [$dateFrom, $dateTo])
            ->sum('amount');

        return view('reports.daily-expenses', compact('records', 'dateFrom', 'dateTo', 'perPage', 'total'));
    }

    public function feedPurchases(Request $request)
    {
        [$dateFrom, $dateTo] = $this->dateRange($request);
        $perPage = ListPagination::resolvePerPage($request);

        $records = FeedPurchase::query()
            ->whereBetween('purchase_date', [$dateFrom, $dateTo])
            ->orderByDesc('purchase_date')
            ->paginate($perPage)
            ->withQueryString();

        $total = (float) FeedPurchase::query()
            ->whereBetween('purchase_date', [$dateFrom, $dateTo])
            ->sum('amount');

        return view('reports.feed-purchases', compact('records', 'dateFrom', 'dateTo', 'perPage', 'total'));
    }

    public function utilityBills(Request $request)
    {
        [$dateFrom, $dateTo] = $this->dateRange($request);
        $perPage = ListPagination::resolvePerPage($request);

        $records = UtilityBill::query()
            ->whereBetween('bill_date', [$dateFrom, $dateTo])
            ->orderByDesc('bill_date')
            ->paginate($perPage)
            ->withQueryString();

        $total = (float) UtilityBill::query()
            ->whereBetween('bill_date', [$dateFrom, $dateTo])
            ->sum('amount');

        return view('reports.utility-bills', compact('records', 'dateFrom', 'dateTo', 'perPage', 'total'));
    }

    public function insurance(Request $request)
    {
        [$dateFrom, $dateTo] = $this->dateRange($request);
        $perPage = ListPagination::resolvePerPage($request);

        $records = InsurancePolicy::query()
            ->whereBetween('start_date', [$dateFrom, $dateTo])
            ->orderByDesc('start_date')
            ->paginate($perPage)
            ->withQueryString();

        $total = (float) InsurancePolicy::query()
            ->whereBetween('start_date', [$dateFrom, $dateTo])
            ->sum('premium_amount');

        return view('reports.insurance', compact('records', 'dateFrom', 'dateTo', 'perPage', 'total'));
    }

    public function loans(Request $request)
    {
        [$dateFrom, $dateTo] = $this->dateRange($request);
        $perPage = ListPagination::resolvePerPage($request);

        $records = FarmLoan::query()
            ->whereBetween('start_date', [$dateFrom, $dateTo])
            ->orderByDesc('start_date')
            ->paginate($perPage)
            ->withQueryString();

        $total = (float) FarmLoan::query()
            ->whereBetween('start_date', [$dateFrom, $dateTo])
            ->sum('loan_amount');

        return view('reports.loans', compact('records', 'dateFrom', 'dateTo', 'perPage', 'total'));
    }

    public function animalPurchases(Request $request)
    {
        [$dateFrom, $dateTo] = $this->dateRange($request);
        $perPage = ListPagination::resolvePerPage($request);

        $records = Buffalo::query()
            ->where('purchase_price', '>', 0)
            ->whereNotNull('purchase_date')
            ->whereBetween('purchase_date', [$dateFrom, $dateTo])
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $total = (float) Buffalo::query()
            ->where('purchase_price', '>', 0)
            ->whereNotNull('purchase_date')
            ->whereBetween('purchase_date', [$dateFrom, $dateTo])
            ->sum('purchase_price');

        return view('reports.animal-purchases', compact('records', 'dateFrom', 'dateTo', 'perPage', 'total'));
    }

    public function animalSales(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->get('date_to', now()->toDateString());

        return redirect()->route('reports.animal-sales', array_filter([
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'per_page' => $request->get('per_page'),
        ], fn ($value) => $value !== null && $value !== ''));
    }

    public function assetPurchases(Request $request)
    {
        [$dateFrom, $dateTo] = $this->dateRange($request);
        $perPage = ListPagination::resolvePerPage($request);

        $records = Asset::query()
            ->whereBetween('purchase_date', [$dateFrom, $dateTo])
            ->orderByDesc('purchase_date')
            ->paginate($perPage)
            ->withQueryString();

        $total = (float) Asset::query()
            ->whereBetween('purchase_date', [$dateFrom, $dateTo])
            ->sum('purchase_cost');

        return view('reports.asset-purchases', compact('records', 'dateFrom', 'dateTo', 'perPage', 'total'));
    }

    public function financialSummary(Request $request)
    {
        [$dateFrom, $dateTo] = $this->dateRange($request);

        $summary = $this->financial->profitLossForPeriod(
            Carbon::parse($dateFrom),
            Carbon::parse($dateTo)
        );

        return view('reports.financial-summary', compact('summary', 'dateFrom', 'dateTo'));
    }
}
