<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class CategoryRepository implements CategoryRepositoryInterface
{
    public function __construct(private readonly Category $model) {}

    public function all(): Collection
    {
        return $this->model->newQuery()->orderBy('name')->get();
    }

    public function allActive(): Collection
    {
        return $this->model->newQuery()->active()->orderBy('name')->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->withCount('expenses')
            ->latest('created_at')
            ->paginate($perPage);
    }

    public function find(int $id): ?Category
    {
        return $this->model->newQuery()->find($id);
    }

    public function existsBySlug(string $slug, ?int $excludeId = null): bool
    {
        $query = $this->model->newQuery()->where('slug', $slug);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function create(array $data): Category
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(Category $category, array $data): Category
    {
        $category->fill($data)->save();

        return $category->fresh();
    }

    public function delete(Category $category): bool
    {
        return (bool) $category->delete();
    }

    public function toggleActive(Category $category): Category
    {
        $category->update(['is_active' => ! $category->is_active]);

        return $category->fresh();
    }
}
