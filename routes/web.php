<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    DashboardController,
    BuffaloController,
    MilkEntryController,
    MilkTransactionController,
    ExpenseController,
    UtilityBillController,
    InsurancePolicyController,
    FarmLoanController,
    FarmOtherExpenseController,
    FeedPurchaseController,
    AnimalTransactionController,
    FarmReportController,
    IncomeController,
    AnimalSaleIncomeController,
    ManureSaleIncomeController,
    OtherIncomeController,
    FarmIncomeReportController,
    MilkSaleController,
    EmployeeController,
    ReportController,
    DynamicReportController,
    SettingController,
    AssetController,
    AssetMaintenanceController,
    AssetReportController,
    AnimalInvestmentReportController,
    BirthHistoryReportController,
    MeetingController,
    TaskController,
    DailyReportController,
    FeedController,
    MilkCustomerController,
    MilkDistributionController,
    DairyCollectionController,
    MilkFlowReportController
};

// Auth routes (Laravel Breeze/Fortify handles these)
require __DIR__ . '/auth.php';

/*
|--------------------------------------------------------------------------
| Language Switch
|--------------------------------------------------------------------------
*/
Route::get('/locale-test', function () {
    return response()->json([
        'session_locale' => session('locale'),
        'app_locale' => app()->getLocale(),
    ]);
});
Route::get('/employee-types', function () {
    return view('employee-types.index');
})->name('employee-types.index');

Route::get('/language/{locale}', function ($locale) {

    if (in_array($locale, ['gu', 'en', 'hi'])) {
        session(['locale' => $locale]);
    }

    return redirect()->back();
})->name('language.switch');

// Protected routes
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Buffalo
    Route::get('buffalo/next-tag', [BuffaloController::class, 'nextTag'])->name('buffalo.next-tag');
    Route::resource('buffalo', BuffaloController::class);

    // Milk entries
    Route::get('milk', [MilkEntryController::class, 'index'])->name('milk.index');
    Route::post('milk', [MilkEntryController::class, 'store'])->name('milk.store');
    Route::get('milk/history', [MilkEntryController::class, 'history'])->name('milk.history');
    Route::get('milk/transactions', [MilkTransactionController::class, 'index'])->name('milk.transactions');
    Route::delete('milk/{milkEntry}', [MilkEntryController::class, 'destroy'])->name('milk.destroy');

    // Milk distribution & dairy collection
    Route::get('milk-distribution', [MilkDistributionController::class, 'index'])->name('milk-distribution.index');
    Route::post('milk-distribution', [MilkDistributionController::class, 'store'])->name('milk-distribution.store');
    Route::delete('milk-distribution/{milkDistribution}', [MilkDistributionController::class, 'destroy'])->name('milk-distribution.destroy');
    Route::get('milk-customers', [MilkCustomerController::class, 'index'])->name('milk-customers.index');
    Route::post('milk-customers', [MilkCustomerController::class, 'store'])->name('milk-customers.store');
    Route::patch('milk-customers/{milkCustomer}', [MilkCustomerController::class, 'update'])->name('milk-customers.update');
    Route::delete('milk-customers/{milkCustomer}', [MilkCustomerController::class, 'destroy'])->name('milk-customers.destroy');
    Route::get('dairy-collections', [DairyCollectionController::class, 'index'])->name('dairy-collections.index');
    Route::post('dairy-collections', [DairyCollectionController::class, 'store'])->name('dairy-collections.store');
    Route::delete('dairy-collections/{dairyCollection}', [DairyCollectionController::class, 'destroy'])->name('dairy-collections.destroy');
    Route::get('reports/milk-distribution', [MilkFlowReportController::class, 'distribution'])->name('reports.milk-distribution');
    Route::get('reports/dairy-collection', [MilkFlowReportController::class, 'dairyCollection'])->name('reports.dairy-collection');
    Route::get('reports/milk-reconciliation', [MilkFlowReportController::class, 'reconciliation'])->name('reports.milk-reconciliation');

    // Expenses hub & accounting modules
    Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::redirect('kharch', '/expenses')->name('kharch.index');
    Route::get('expenses/daily', [FarmReportController::class, 'dailyExpenses'])->name('expenses.daily');
    Route::get('expenses/utility-bills', [UtilityBillController::class, 'index'])->name('expenses.utility-bills.index');
    Route::post('expenses/utility-bills', [UtilityBillController::class, 'store'])->name('expenses.utility-bills.store');
    Route::delete('expenses/utility-bills/{utilityBill}', [UtilityBillController::class, 'destroy'])->name('expenses.utility-bills.destroy');
    Route::get('expenses/insurance', [InsurancePolicyController::class, 'index'])->name('expenses.insurance.index');
    Route::post('expenses/insurance', [InsurancePolicyController::class, 'store'])->name('expenses.insurance.store');
    Route::delete('expenses/insurance/{insurancePolicy}', [InsurancePolicyController::class, 'destroy'])->name('expenses.insurance.destroy');
    Route::get('expenses/loans', [FarmLoanController::class, 'index'])->name('expenses.loans.index');
    Route::post('expenses/loans', [FarmLoanController::class, 'store'])->name('expenses.loans.store');
    Route::delete('expenses/loans/{farmLoan}', [FarmLoanController::class, 'destroy'])->name('expenses.loans.destroy');
    Route::get('expenses/other', [FarmOtherExpenseController::class, 'index'])->name('expenses.other.index');
    Route::post('expenses/other', [FarmOtherExpenseController::class, 'store'])->name('expenses.other.store');
    Route::delete('expenses/other/{farmOtherExpense}', [FarmOtherExpenseController::class, 'destroy'])->name('expenses.other.destroy');

    Route::get('feeds/purchase', [FeedPurchaseController::class, 'index'])->name('feeds.purchase.index');
    Route::post('feeds/purchase', [FeedPurchaseController::class, 'store'])->name('feeds.purchase.store');
    Route::delete('feeds/purchase/{feedPurchase}', [FeedPurchaseController::class, 'destroy'])->name('feeds.purchase.destroy');

    Route::get('animal-transactions', [AnimalTransactionController::class, 'index'])->name('animal-transactions.index');
    Route::get('animal-transactions/purchase', [AnimalTransactionController::class, 'createPurchase'])->name('animal-transactions.purchase');
    Route::post('animal-transactions/purchase', [AnimalTransactionController::class, 'storePurchase'])->name('animal-transactions.purchase.store');
    Route::get('animal-transactions/sale', [AnimalTransactionController::class, 'createSale'])->name('animal-transactions.sale');
    Route::post('animal-transactions/sale', [AnimalTransactionController::class, 'storeSale'])->name('animal-transactions.sale.store');

    Route::get('reports/daily-expenses', [FarmReportController::class, 'dailyExpenses'])->name('reports.daily-expenses');
    Route::get('reports/feed-purchases', [FarmReportController::class, 'feedPurchases'])->name('reports.feed-purchases');
    Route::get('reports/utility-bills', [FarmReportController::class, 'utilityBills'])->name('reports.utility-bills');
    Route::get('reports/insurance', [FarmReportController::class, 'insurance'])->name('reports.insurance');
    Route::get('reports/loans', [FarmReportController::class, 'loans'])->name('reports.loans');
    Route::get('reports/animal-purchases', [FarmReportController::class, 'animalPurchases'])->name('reports.animal-purchases');
    Route::get('reports/animal-sales-farm', [FarmReportController::class, 'animalSales'])->name('reports.animal-sales-farm');
    Route::get('reports/asset-purchases', [FarmReportController::class, 'assetPurchases'])->name('reports.asset-purchases');
    Route::get('reports/financial-summary', [FarmReportController::class, 'financialSummary'])->name('reports.financial-summary');

    Route::get('income', [IncomeController::class, 'index'])->name('income.index');
    Route::get('income/animal-sales', [AnimalSaleIncomeController::class, 'index'])->name('income.animal-sales.index');
    Route::post('income/animal-sales', [AnimalSaleIncomeController::class, 'store'])->name('income.animal-sales.store');
    Route::delete('income/animal-sales/{income}', [AnimalSaleIncomeController::class, 'destroy'])->name('income.animal-sales.destroy');
    Route::get('income/manure-sales', [ManureSaleIncomeController::class, 'index'])->name('income.manure-sales.index');
    Route::post('income/manure-sales', [ManureSaleIncomeController::class, 'store'])->name('income.manure-sales.store');
    Route::delete('income/manure-sales/{income}', [ManureSaleIncomeController::class, 'destroy'])->name('income.manure-sales.destroy');
    Route::get('income/other', [OtherIncomeController::class, 'index'])->name('income.other.index');
    Route::post('income/other', [OtherIncomeController::class, 'store'])->name('income.other.store');
    Route::delete('income/other/{income}', [OtherIncomeController::class, 'destroy'])->name('income.other.destroy');
    Route::get('reports/animal-sales', [FarmIncomeReportController::class, 'animalSales'])->name('reports.animal-sales');
    Route::get('reports/manure-sales', [FarmIncomeReportController::class, 'manureSales'])->name('reports.manure-sales');
    Route::get('reports/income-summary', [FarmIncomeReportController::class, 'summary'])->name('reports.income-summary');

    // Milk Sales
    Route::get('sale', [MilkSaleController::class, 'index'])->name('sale.index');
    Route::post('sale', [MilkSaleController::class, 'store'])->name('sale.store');
    Route::patch('sale/{milkSale}/pay', [MilkSaleController::class, 'update'])->name('sale.pay');
    Route::delete('sale/{milkSale}', [MilkSaleController::class, 'destroy'])->name('sale.destroy');

    // Employees
    Route::get('employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::post('employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::patch('employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::post('employees/{employee}/salary', [EmployeeController::class, 'paySalary'])->name('employees.salary');

    // Reports (read from synchronized tables only)
    Route::get('reports/monthly', [ReportController::class, 'monthly'])->name('reports.monthly');
    Route::get('reports/yearly', [ReportController::class, 'yearly'])->name('reports.yearly');
    Route::get('reports/animal-investment', [AnimalInvestmentReportController::class, 'index'])->name('reports.animal-investment');
    Route::get('reports/birth-history', [BirthHistoryReportController::class, 'index'])->name('reports.birth-history');
    Route::get('reports/generator', [DynamicReportController::class, 'index'])->name('reports.generator');
    Route::get('reports/generate', [DynamicReportController::class, 'generate'])->name('reports.generate');
    Route::get('reports/pdf', [DynamicReportController::class, 'pdf'])->name('reports.pdf');

    // Settings
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
    Route::resource('assets', AssetController::class);
    Route::post('assets/{asset}/maintenances', [AssetMaintenanceController::class, 'store'])->name('assets.maintenances.store');
    Route::put('assets/{asset}/maintenances/{maintenance}', [AssetMaintenanceController::class, 'update'])->name('assets.maintenances.update');
    Route::delete('assets/{asset}/maintenances/{maintenance}', [AssetMaintenanceController::class, 'destroy'])->name('assets.maintenances.destroy');
    Route::get('reports/assets', [AssetReportController::class, 'index'])->name('reports.assets');
    Route::get('employees/{employee}/portal', [EmployeeController::class, 'portal'])
        ->name('employee.portal');
    Route::resource('tasks', TaskController::class);
    Route::post('/tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete');
    Route::resource('meetings', MeetingController::class);
    Route::resource('daily-reports', DailyReportController::class);
    Route::post('daily-reports/{daily_report}/autosave-milk', [DailyReportController::class, 'autosaveMilk'])
        ->name('daily-reports.autosave-milk');
    Route::get('feeds/history', [FeedController::class, 'history'])->name('feeds.history');
    Route::resource('feeds', FeedController::class);
    Route::post('feeds/{feed}/stock-in', [FeedController::class, 'stockIn'])->name('feeds.stock-in');
});
