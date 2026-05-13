<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Expense;
use App\Repositories\Contracts\ExpenseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class ExpenseRepository implements ExpenseRepositoryInterface
{
    public function __construct(private readonly Expense $model) {}

    public function paginateForUser(int $userId, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->with('category')
            ->when($filters['category_id'] ?? null, fn ($q, $id) => $q->where('category_id', $id))
            ->when($filters['from'] ?? null, fn ($q, $from) => $q->where('occurred_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($q, $to) => $q->where('occurred_at', '<=', $to))
            ->when($filters['search'] ?? null, fn ($q, $term) => $q->where('description', 'like', '%'.$term.'%')
            )
            ->latest('occurred_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findForUser(int $userId, int $expenseId): ?Expense
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->where('id', $expenseId)
            ->first();
    }

    public function create(array $data): Expense
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(Expense $expense, array $data): Expense
    {
        $expense->fill($data)->save();

        return $expense->fresh(['category']);
    }

    public function delete(Expense $expense): bool
    {
        return (bool) $expense->delete();
    }

    public function totalsByCategoryForMonth(int $userId, int $year, int $month): Collection
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->whereYear('occurred_at', $year)
            ->whereMonth('occurred_at', $month)
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->with('category')
            ->get();
    }

    public function dailyAverageForMonth(int $userId, int $year, int $month): float
    {
        $total = (float) $this->model->newQuery()
            ->where('user_id', $userId)
            ->whereYear('occurred_at', $year)
            ->whereMonth('occurred_at', $month)
            ->sum('amount');

        $daysInMonth = (int) date('t', strtotime("{$year}-{$month}-01"));

        return $daysInMonth > 0 ? round($total / $daysInMonth, 2) : 0.0;
    }

    public function lifetimeTotalsByCategory(int $userId): Collection
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->with('category')
            ->get();
    }

    public function allForUser(int $userId, array $filters): Collection
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->with('category')
            ->when($filters['category_id'] ?? null, fn ($q, $id) => $q->where('category_id', $id))
            ->when($filters['from'] ?? null, fn ($q, $from) => $q->where('occurred_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($q, $to) => $q->where('occurred_at', '<=', $to))
            ->when($filters['search'] ?? null, fn ($q, $term) => $q->where('description', 'like', '%'.$term.'%'))
            ->latest('occurred_at')
            ->get();
    }
}
