<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ExpenseController;
use App\Http\Controllers\Api\V1\ReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', fn (Request $request) => $request->user());

Route::prefix('v1')->name('api.v1.')->middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::apiResource('expenses', ExpenseController::class);
    Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);

    Route::prefix('reports')->middleware('throttle:reports')->group(function () {
        Route::get('monthly-category', [ReportController::class, 'monthlyCategory'])
            ->name('reports.monthly-category');
        Route::get('monthly-average', [ReportController::class, 'monthlyAverage'])
            ->name('reports.monthly-average');
        Route::get('lifetime', [ReportController::class, 'lifetime'])
            ->name('reports.lifetime');
    });
});
