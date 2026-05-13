<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Expense\IndexExpenseRequest;
use App\Http\Requests\Expense\StoreExpenseRequest;
use App\Http\Requests\Expense\UpdateExpenseRequest;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Services\ExpenseReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response as HttpResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

final class ExpenseController extends Controller
{
    public function __construct(
        private readonly ExpenseRepositoryInterface $expenses,
        private readonly CategoryRepositoryInterface $categories,
        private readonly ExpenseReportService $reports,
    ) {}

    public function index(IndexExpenseRequest $request): View
    {
        $perPage = min((int) $request->input('per_page', config('constants.pagination.default_per_page')), config('constants.pagination.max_per_page'));
        $expenses = $this->expenses->paginateForUser(
            userId: $request->user()->id,
            filters: $request->validated(),
            perPage: $perPage,
        );
        $categories = $this->categories->allActive();

        return view('expenses.index', compact('expenses', 'categories'));
    }

    public function create(): View
    {
        $categories = $this->categories->allActive();

        return view('expenses.create', compact('categories'));
    }

    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        $data = array_merge($request->validated(), [
            'user_id' => $request->user()->id,
        ]);

        $this->expenses->create($data);
        $this->reports->bustCacheForUser($request->user()->id);

        return redirect()->route('expenses.index')->with('success', 'Expense added successfully.');
    }

    public function edit(string $id): View
    {
        $expense = $this->expenses->findForUser(auth()->id(), (int) $id);
        abort_unless($expense !== null, 404);
        $this->authorize('update', $expense);

        $categories = $this->categories->allActive();

        return view('expenses.edit', compact('expense', 'categories'));
    }

    public function update(UpdateExpenseRequest $request, string $id): RedirectResponse
    {
        $expense = $this->expenses->findForUser($request->user()->id, (int) $id);
        abort_unless($expense !== null, 404);
        $this->authorize('update', $expense);

        $this->expenses->update($expense, $request->validated());
        $this->reports->bustCacheForUser($request->user()->id);

        return redirect()->route('expenses.index')->with('success', 'Expense updated successfully.');
    }

    public function exportPdf(IndexExpenseRequest $request): HttpResponse
    {
        $expenses = $this->expenses->allForUser(
            userId: $request->user()->id,
            filters: $request->validated(),
        );

        $validated = $request->validated();

        $categoryName = null;
        if (! empty($validated['category_id'])) {
            $cat = $this->categories->find((int) $validated['category_id']);
            $categoryName = $cat?->name;
        }

        $filters = [
            'search'   => $validated['search'] ?? null,
            'category' => $categoryName,
            'from'     => $validated['from'] ?? null,
            'to'       => $validated['to'] ?? null,
        ];

        $pdf = Pdf::loadView('expenses.pdf', [
            'expenses' => $expenses,
            'user'     => $request->user(),
            'filters'  => $filters,
        ])->setPaper('a4', 'portrait');

        $filename = 'expenses-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    public function export(IndexExpenseRequest $request): StreamedResponse
    {
        $expenses = $this->expenses->allForUser(
            userId: $request->user()->id,
            filters: $request->validated(),
        );

        $filename = 'expenses-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($expenses): void {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM so Excel opens the file correctly
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Date', 'Description', 'Category', 'Amount']);

            foreach ($expenses as $expense) {
                fputcsv($handle, [
                    $expense->occurred_at->format('Y-m-d'),
                    $expense->description,
                    $expense->category?->name ?? '',
                    '₹' . number_format((float) $expense->amount, 2, '.', ''),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function destroy(string $id): RedirectResponse
    {
        $expense = $this->expenses->findForUser(auth()->id(), (int) $id);
        abort_unless($expense !== null, 404);
        $this->authorize('delete', $expense);

        $this->expenses->delete($expense);
        $this->reports->bustCacheForUser(auth()->id());

        return redirect()->route('expenses.index')->with('success', 'Expense deleted.');
    }
}
