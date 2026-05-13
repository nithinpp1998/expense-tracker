<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ExpenseReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ReportController extends Controller
{
    public function __construct(private readonly ExpenseReportService $reports) {}

    public function monthlyCategory(Request $request): JsonResponse
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $data = $this->reports->monthlyCategoryTotals($request->user()->id, $year, $month);

        return response()->json([
            'year' => $year,
            'month' => $month,
            'data' => $data->map(fn ($row) => [
                'category' => $row->category?->name,
                'color' => $row->category?->color,
                'total' => (float) $row->total,
            ]),
        ]);
    }

    public function monthlyAverage(Request $request): JsonResponse
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $average = $this->reports->monthlyDailyAverage($request->user()->id, $year, $month);

        return response()->json([
            'year' => $year,
            'month' => $month,
            'average' => $average,
        ]);
    }

    public function lifetime(Request $request): JsonResponse
    {
        $data = $this->reports->lifetimeCategoryTotals($request->user()->id);

        return response()->json([
            'data' => $data->map(fn ($row) => [
                'category' => $row->category?->name,
                'color' => $row->category?->color,
                'total' => (float) $row->total,
            ]),
        ]);
    }
}
