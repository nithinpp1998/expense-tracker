<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class CategoryController extends Controller
{
    public function __construct(private readonly CategoryRepositoryInterface $categories) {}

    public function index(): AnonymousResourceCollection
    {
        return CategoryResource::collection($this->categories->allActive());
    }

    public function show(string $id): CategoryResource
    {
        $category = $this->categories->find($id);
        abort_unless($category !== null, 404);

        return new CategoryResource($category);
    }
}
