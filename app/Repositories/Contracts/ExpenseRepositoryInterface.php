<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Expense;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ExpenseRepositoryInterface
{
    /** @see config('constants.pagination.default_per_page') — controllers must always pass this value explicitly */
    public function paginateForUser(int $userId, array $filters, int $perPage = 15): LengthAwarePaginator;

    public function findForUser(int $userId, int $expenseId): ?Expense;

    public function create(array $data): Expense;

    public function update(Expense $expense, array $data): Expense;

    public function delete(Expense $expense): bool;

    public function totalsByCategoryForMonth(int $userId, int $year, int $month): Collection;

    public function dailyAverageForMonth(int $userId, int $year, int $month): float;

    public function lifetimeTotalsByCategory(int $userId): Collection;

    public function allForUser(int $userId, array $filters): Collection;

    /** Per-category SUM(amount) for any date range (YYYY-MM-DD strings). */
    public function totalsByCategoryForRange(int $userId, string $from, string $to): Collection;
}
