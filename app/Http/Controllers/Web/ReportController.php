<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ExpenseReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ReportController extends Controller
{
    public function __construct(private readonly ExpenseReportService $reports) {}

    public function monthlyCategory(Request $request): View
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);
        $data = $this->reports->monthlyCategoryTotals($request->user()->id, $year, $month);

        return view('reports.monthly-category', compact('data', 'year', 'month'));
    }

    public function monthlyAverage(Request $request): View
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);
        $average = $this->reports->monthlyDailyAverage($request->user()->id, $year, $month);

        return view('reports.monthly-average', compact('average', 'year', 'month'));
    }

    public function lifetime(Request $request): View
    {
        $data = $this->reports->lifetimeCategoryTotals($request->user()->id);

        return view('reports.lifetime', compact('data'));
    }

    public function momComparison(Request $request): View
    {
        $year  = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $data = $this->reports->monthOverMonthComparison($request->user()->id, $year, $month);

        $prevDate  = Carbon::create($year, $month, 1)->subMonth();
        $prevYear  = (int) $prevDate->year;
        $prevMonth = (int) $prevDate->month;

        $thisMonthTotal = $data->sum('this_month');
        $lastMonthTotal = $data->sum('last_month');
        $overallChange  = $lastMonthTotal > 0
            ? (($thisMonthTotal - $lastMonthTotal) / $lastMonthTotal) * 100
            : null;

        return view('reports.mom-comparison', compact(
            'data', 'year', 'month', 'prevYear', 'prevMonth',
            'thisMonthTotal', 'lastMonthTotal', 'overallChange',
        ));
    }
}
