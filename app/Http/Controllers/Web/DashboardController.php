<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Services\ExpenseReportService;
use Carbon\Carbon;
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
        $now    = now();

        // ── Resolve the active date range ─────────────────────────────────
        // Default: start of current month → today.
        // User overrides via ?from=YYYY-MM-DD&to=YYYY-MM-DD.
        $fromDate = $request->filled('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : $now->copy()->startOfMonth()->startOfDay();

        $toDate = $request->filled('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : $now->copy()->endOfDay();

        // Guard: "to" must not exceed today; "from" must not exceed "to".
        if ($toDate->gt($now->copy()->endOfDay())) {
            $toDate = $now->copy()->endOfDay();
        }
        if ($fromDate->gt($toDate)) {
            $fromDate = $toDate->copy()->startOfDay();
        }

        $fromStr = $fromDate->toDateString();
        $toStr   = $toDate->toDateString();

        // ── Period stats ──────────────────────────────────────────────────
        $monthly    = $this->reports->categoryTotalsForRange($userId, $fromStr, $toStr);
        $monthTotal = $monthly->sum(fn ($r) => (float) $r->total);
        $days       = max(1, (int) $fromDate->diffInDays($toDate) + 1);
        $average    = round($monthTotal / $days, 2);

        // ── Full-history totals (unaffected by the period filter) ─────────
        $lifetime = $this->reports->lifetimeCategoryTotals($userId);

        // ── Recent expenses: always the latest 5, regardless of filter ────
        $recent     = $this->expenses->paginateForUser($userId, [], 5);
        $categories = $this->categories->all();

        return view('dashboard', compact(
            'recent', 'monthly', 'average', 'lifetime', 'categories',
            'monthTotal', 'now', 'fromStr', 'toStr', 'days',
        ));
    }
}
