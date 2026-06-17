<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    DashboardController,
    BuffaloController,
    MilkEntryController,
    MilkTransactionController,
    ExpenseController,
    IncomeController,
    MilkSaleController,
    EmployeeController,
    ReportController,
    DynamicReportController,
    SettingController,
    AssetController,
    MeetingController,
    TaskController,
    DailyReportController,
    FeedController
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

    // Kharch / Expenses
    Route::get('kharch', [ExpenseController::class, 'index'])->name('kharch.index');
    Route::post('kharch', [ExpenseController::class, 'store'])->name('kharch.store');
    Route::delete('kharch/{expense}', [ExpenseController::class, 'destroy'])->name('kharch.destroy');

    Route::resource('income', IncomeController::class)->only(['index', 'store', 'destroy']);

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
    Route::get('reports/generator', [DynamicReportController::class, 'index'])->name('reports.generator');
    Route::get('reports/generate', [DynamicReportController::class, 'generate'])->name('reports.generate');
    Route::get('reports/pdf', [DynamicReportController::class, 'pdf'])->name('reports.pdf');

    // Settings
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
    Route::resource('assets', AssetController::class);
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
