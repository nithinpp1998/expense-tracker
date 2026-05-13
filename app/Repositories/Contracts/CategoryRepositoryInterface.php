<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CategoryRepositoryInterface
{
    public function all(): Collection;

    public function allActive(): Collection;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function find(int $id): ?Category;

    public function existsBySlug(string $slug, ?int $excludeId = null): bool;

    public function create(array $data): Category;

    public function update(Category $category, array $data): Category;

    public function delete(Category $category): bool;

    public function toggleActive(Category $category): Category;
}
