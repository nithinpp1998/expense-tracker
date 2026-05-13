<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\ExpenseRepositoryInterface;
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
