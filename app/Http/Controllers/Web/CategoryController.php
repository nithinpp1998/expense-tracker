<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class CategoryController extends Controller
{
    public function __construct(private readonly CategoryRepositoryInterface $categories) {}

    public function index(): View
    {
        $this->authorize('viewAny', Category::class);

        $perPage = min((int) request()->input('per_page', config('constants.pagination.default_per_page')), config('constants.pagination.max_per_page'));
        $categories = $this->categories->paginate($perPage);

        return view('categories.index', compact('categories'));
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->authorize('create', Category::class);

        $validated = $request->validated();
        $validated['slug']      = $this->uniqueSlug(Str::slug($validated['name']));
        $validated['color']     = $validated['color'] ?? config('constants.category.default_color');
        $validated['is_active'] = (bool) ($validated['is_active'] ?? true);
        $validated['is_system'] = false;

        $this->categories->create($validated);

        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    public function update(UpdateCategoryRequest $request, string $id): RedirectResponse
    {
        $category = $this->categories->find((int) $id);
        abort_unless($category !== null, 404);
        $this->authorize('update', $category);

        $validated = $request->validated();

        if (isset($validated['name'])) {
            $newSlug = Str::slug($validated['name']);
            if ($newSlug !== $category->slug) {
                $validated['slug'] = $this->uniqueSlug($newSlug, $category->id);
            }
        }

        $this->categories->update($category, $validated);

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $category = $this->categories->find((int) $id);
        abort_unless($category !== null, 404);
        $this->authorize('delete', $category);

        $category->loadCount('expenses');

        if ($category->expenses_count > 0) {
            return redirect()->route('categories.index')
                ->with('error', "Cannot delete \"{$category->name}\" — it is used by {$category->expenses_count} expense(s). Deactivate it instead.");
        }

        $this->categories->delete($category);

        return redirect()->route('categories.index')->with('success', 'Category deleted.');
    }

    public function toggle(string $id): RedirectResponse
    {
        $category = $this->categories->find((int) $id);
        abort_unless($category !== null, 404);
        $this->authorize('toggleActive', $category);

        $wasActive = $category->is_active;
        $this->categories->toggleActive($category);
        $status = $wasActive ? 'deactivated' : 'activated';

        return redirect()->route('categories.index')->with('success', "Category {$status}.");
    }

    private function uniqueSlug(string $base, ?int $excludeId = null): string
    {
        $slug = $base;
        $i    = 1;

        while ($this->categories->existsBySlug($slug, $excludeId)) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
