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

    public function monthlyCategoryTotals(string $userId, int $year, int $month): Collection
    {
        $key = "report:monthly-category:{$userId}:{$year}:{$month}";

        return Cache::remember($key, 3600, fn () => $this->expenses->totalsByCategoryForMonth($userId, $year, $month)
        );
    }

    public function monthlyDailyAverage(string $userId, int $year, int $month): float
    {
        $key = "report:daily-average:{$userId}:{$year}:{$month}";

        return Cache::remember($key, 3600, fn () => $this->expenses->dailyAverageForMonth($userId, $year, $month)
        );
    }

    public function lifetimeCategoryTotals(string $userId): Collection
    {
        $key = "report:lifetime-category:{$userId}";

        return Cache::remember($key, 3600, fn () => $this->expenses->lifetimeTotalsByCategory($userId)
        );
    }

    public function bustCacheForUser(string $userId): void
    {
        Cache::forget("report:lifetime-category:{$userId}");

        $now = now();
        for ($i = 0; $i < 3; $i++) {
            $date = $now->copy()->subMonths($i);
            $y = (int) $date->format('Y');
            $m = (int) $date->format('n');
            Cache::forget("report:monthly-category:{$userId}:{$y}:{$m}");
            Cache::forget("report:daily-average:{$userId}:{$y}:{$m}");
        }
    }
}
