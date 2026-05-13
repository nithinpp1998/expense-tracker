<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\ExpenseRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class ExpenseReportService
{
    public function __construct(
        private readonly ExpenseRepositoryInterface $expenses,
    ) {}

    public function monthlyCategoryTotals(int $userId, int $year, int $month): Collection
    {
        $key = "report:monthly-category:{$userId}:{$year}:{$month}";

        return Cache::remember($key, config('constants.reports.cache_ttl_seconds'), fn () => $this->expenses->totalsByCategoryForMonth($userId, $year, $month)
        );
    }

    public function monthlyDailyAverage(int $userId, int $year, int $month): float
    {
        $key = "report:daily-average:{$userId}:{$year}:{$month}";

        return Cache::remember($key, config('constants.reports.cache_ttl_seconds'), fn () => $this->expenses->dailyAverageForMonth($userId, $year, $month)
        );
    }

    public function lifetimeCategoryTotals(int $userId): Collection
    {
        $key = "report:lifetime-category:{$userId}";

        return Cache::remember($key, config('constants.reports.cache_ttl_seconds'), fn () => $this->expenses->lifetimeTotalsByCategory($userId)
        );
    }

    /**
     * Per-category totals for any date range — powers the dashboard period filter.
     * Short TTL (5 min): these are interactive queries, not heavy scheduled reports.
     */
    public function categoryTotalsForRange(int $userId, string $from, string $to): Collection
    {
        $key = "report:range-category:{$userId}:{$from}:{$to}";

        return Cache::remember($key, 300, fn () =>
            $this->expenses->totalsByCategoryForRange($userId, $from, $to)
        );
    }

    /**
     * Per-category comparison: current month vs the preceding month.
     * Reuses the existing cached monthly totals — zero extra queries when both
     * months are already warm in cache.
     */
    public function monthOverMonthComparison(int $userId, int $year, int $month): Collection
    {
        $prevDate  = Carbon::create($year, $month, 1)->subMonth();
        $prevYear  = (int) $prevDate->year;
        $prevMonth = (int) $prevDate->month;

        $current  = $this->monthlyCategoryTotals($userId, $year, $month);
        $previous = $this->monthlyCategoryTotals($userId, $prevYear, $prevMonth);

        $currentMap  = $current->keyBy('category_id');
        $previousMap = $previous->keyBy('category_id');

        // Union of both months' category IDs so we catch categories that
        // disappeared or appeared for the first time.
        $allIds = $currentMap->keys()->merge($previousMap->keys())->unique();

        return $allIds->map(function ($catId) use ($currentMap, $previousMap) {
            $curr = $currentMap->get($catId);
            $prev = $previousMap->get($catId);

            $currTotal = (float) ($curr?->total ?? 0);
            $prevTotal = (float) ($prev?->total ?? 0);

            if ($prevTotal > 0) {
                $changePct = (($currTotal - $prevTotal) / $prevTotal) * 100;
                $direction = match (true) {
                    $changePct > 0.5  => 'up',
                    $changePct < -0.5 => 'down',
                    default           => 'same',
                };
            } else {
                $changePct = null;
                $direction = $currTotal > 0 ? 'new' : 'same';
            }

            return (object) [
                'category'    => $curr?->category ?? $prev?->category,
                'category_id' => $catId,
                'this_month'  => $currTotal,
                'last_month'  => $prevTotal,
                'change_pct'  => $changePct,
                'direction'   => $direction,
            ];
        })->sortByDesc('this_month')->values();
    }

    public function bustCacheForUser(int $userId): void
    {
        Cache::forget("report:lifetime-category:{$userId}");

        $now = now();
        for ($i = 0; $i < config('constants.reports.cache_bust_months'); $i++) {
            $date = $now->copy()->subMonths($i);
            $y = (int) $date->format('Y');
            $m = (int) $date->format('n');
            Cache::forget("report:monthly-category:{$userId}:{$y}:{$m}");
            Cache::forget("report:daily-average:{$userId}:{$y}:{$m}");
        }
    }
}
