<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Expense\IndexExpenseRequest;
use App\Http\Requests\Expense\StoreExpenseRequest;
use App\Http\Requests\Expense\UpdateExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Services\ExpenseReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class ExpenseController extends Controller
{
    public function __construct(
        private readonly ExpenseRepositoryInterface $expenses,
        private readonly ExpenseReportService $reports,
    ) {}

    public function index(IndexExpenseRequest $request): AnonymousResourceCollection
    {
        $perPage = min((int) $request->input('per_page', config('constants.pagination.default_per_page')), config('constants.pagination.max_per_page'));

        $expenses = $this->expenses->paginateForUser(
            userId: $request->user()->id,
            filters: $request->validated(),
            perPage: $perPage,
        );

        return ExpenseResource::collection($expenses);
    }

    public function store(StoreExpenseRequest $request): ExpenseResource
    {
        $data = array_merge($request->validated(), [
            'user_id' => $request->user()->id,
            'currency' => $request->validated('currency') ?? $request->user()->currency,
        ]);

        $expense = $this->expenses->create($data);
        $this->reports->bustCacheForUser($request->user()->id);

        return new ExpenseResource($expense->load('category'));
    }

    public function show(string $id): ExpenseResource
    {
        $expense = $this->expenses->findForUser(auth()->id(), $id);

        abort_unless($expense !== null, 404);
        $this->authorize('view', $expense);

        return new ExpenseResource($expense->load('category'));
    }

    public function update(UpdateExpenseRequest $request, string $id): ExpenseResource
    {
        $expense = $this->expenses->findForUser($request->user()->id, $id);

        abort_unless($expense !== null, 404);
        $this->authorize('update', $expense);

        $updated = $this->expenses->update($expense, $request->validated());
        $this->reports->bustCacheForUser($request->user()->id);

        return new ExpenseResource($updated);
    }

    public function destroy(string $id): JsonResponse
    {
        $expense = $this->expenses->findForUser(auth()->id(), $id);

        abort_unless($expense !== null, 404);
        $this->authorize('delete', $expense);

        $this->expenses->delete($expense);
        $this->reports->bustCacheForUser(auth()->id());

        return response()->json(null, 204);
    }
}
