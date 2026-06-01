<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    DashboardController,
    BuffaloController,
    MilkEntryController,
    ExpenseController,
    MilkSaleController,
    EmployeeController,
    ReportController,
    SettingController,
};

// Auth routes (Laravel Breeze/Fortify handles these)
require __DIR__.'/auth.php';

// Protected routes
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Buffalo
    Route::resource('buffalo', BuffaloController::class);

    // Milk entries
    Route::get('milk', [MilkEntryController::class, 'index'])->name('milk.index');
    Route::post('milk', [MilkEntryController::class, 'store'])->name('milk.store');
    Route::get('milk/history', [MilkEntryController::class, 'history'])->name('milk.history');
    Route::delete('milk/{milkEntry}', [MilkEntryController::class, 'destroy'])->name('milk.destroy');

    // Kharch / Expenses
    Route::get('kharch', [ExpenseController::class, 'index'])->name('kharch.index');
    Route::post('kharch', [ExpenseController::class, 'store'])->name('kharch.store');
    Route::delete('kharch/{expense}', [ExpenseController::class, 'destroy'])->name('kharch.destroy');

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

    // Reports
    Route::get('reports/monthly', [ReportController::class, 'monthly'])->name('reports.monthly');
    Route::get('reports/yearly', [ReportController::class, 'yearly'])->name('reports.yearly');

    // Settings
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
});