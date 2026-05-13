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

        $perPage = min((int) request()->input('per_page', 10), 100);
        $categories = $this->categories->paginate($perPage);

        return view('categories.index', compact('categories'));
    }

    public function create(): View
    {
        $this->authorize('create', Category::class);

        return view('categories.create');
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->authorize('create', Category::class);

        $validated = $request->validated();
        $validated['slug'] = $this->uniqueSlug(Str::slug($validated['name']));
        $validated['color']     = $validated['color'] ?? '#6b7280';
        $validated['is_active'] = (bool) ($validated['is_active'] ?? true);
        $validated['is_system'] = false;

        $this->categories->create($validated);

        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(string $id): View
    {
        $category = $this->categories->find($id);
        abort_unless($category !== null, 404);
        $this->authorize('update', $category);

        return view('categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, string $id): RedirectResponse
    {
        $category = $this->categories->find($id);
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
        $category = $this->categories->find($id);
        abort_unless($category !== null, 404);
        $this->authorize('delete', $category);

        $this->categories->delete($category);

        return redirect()->route('categories.index')->with('success', 'Category deleted.');
    }

    public function toggle(string $id): RedirectResponse
    {
        $category = $this->categories->find($id);
        abort_unless($category !== null, 404);
        $this->authorize('toggleActive', $category);

        $this->categories->toggleActive($category);

        $status = $category->is_active ? 'deactivated' : 'activated';

        return redirect()->route('categories.index')->with('success', "Category {$status}.");
    }

    private function uniqueSlug(string $base, ?string $excludeId = null): string
    {
        $slug = $base;
        $i = 1;

        while ($this->categories->existsBySlug($slug, $excludeId)) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
