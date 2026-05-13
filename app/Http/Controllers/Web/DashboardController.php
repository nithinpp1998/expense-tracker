<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Services\ExpenseReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class DashboardController extends Controller
{
    public function __construct(
        private readonly ExpenseRepositoryInterface $expenses,
        private readonly CategoryRepositoryInterface $categories,
        private readonly ExpenseReportService $reports,
    ) {}

    public function __invoke(Request $request): View
    {
        $userId = $request->user()->id;
        $now = now();

        $recent = $this->expenses->paginateForUser($userId, [], 5);
        $monthly = $this->reports->monthlyCategoryTotals($userId, (int) $now->year, (int) $now->month);
        $average = $this->reports->monthlyDailyAverage($userId, (int) $now->year, (int) $now->month);
        $lifetime = $this->reports->lifetimeCategoryTotals($userId);
        $categories = $this->categories->all();

        $monthTotal = $monthly->sum(fn ($row) => (float) $row->total);

        return view('dashboard', compact(
            'recent', 'monthly', 'average', 'lifetime', 'categories', 'monthTotal', 'now'
        ));
    }
}
