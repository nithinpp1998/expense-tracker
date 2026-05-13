<?php

declare(strict_types=1);

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\CategoryController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ExpenseController;
use App\Http\Controllers\Web\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('expenses/export', [ExpenseController::class, 'export'])->name('expenses.export');
    Route::resource('expenses', ExpenseController::class)
        ->except(['show']);

    Route::resource('categories', CategoryController::class)
        ->except(['show', 'create', 'edit']);
    Route::patch('categories/{category}/toggle', [CategoryController::class, 'toggle'])
        ->name('categories.toggle');

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('monthly-category', [ReportController::class, 'monthlyCategory'])
            ->name('monthly-category');
        Route::get('monthly-average', [ReportController::class, 'monthlyAverage'])
            ->name('monthly-average');
        Route::get('lifetime', [ReportController::class, 'lifetime'])
            ->name('lifetime');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
