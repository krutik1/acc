<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ExpenseReportController extends Controller
{
    public function index()
    {
        // Simple dashboard or redirect to monthly
        return redirect()->route('expenses.reports.monthly');
    }

    public function monthly(Request $request)
    {
        $year = $request->input('year', now()->year);
        $companyId = session('selected_company_id');

        $monthlyExpenses = Expense::whereHas('unit', function ($q) use ($companyId) {
            if ($companyId) {
                $q->where('company_id', $companyId);
            }
        })
            ->whereYear('date', $year)
            ->select(
                DB::raw('MONTH(date) as month'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $chartData = [
            'labels' => [],
            'data' => [],
        ];
        $totalExpensesYear = 0;

        for ($m = 1; $m <= 12; $m++) {
            $monthName = Carbon::create()->month($m)->format('F');
            $chartData['labels'][] = $monthName;

            if (isset($monthlyExpenses[$m])) {
                $amount = $monthlyExpenses[$m]->total_amount;
            } else {
                $amount = 0;
            }

            $chartData['data'][] = $amount;
            $totalExpensesYear += $amount;
        }

        // Available years
        $years = Expense::whereHas('unit', function ($q) use ($companyId) {
            if ($companyId) {
                $q->where('company_id', $companyId);
            }
        })
            ->select(DB::raw('YEAR(date) as year'))
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        if ($years->isEmpty()) {
            $years = [now()->year];
        }

        return view('expenses.reports.monthly', compact('chartData', 'totalExpensesYear', 'year', 'years'));
    }

    public function unitWise(Request $request)
    {
        $fromDate = $request->input('from_date', now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->input('to_date', now()->endOfMonth()->format('Y-m-d'));
        $companyId = session('selected_company_id');

        $unitExpenses = Unit::where('company_id', $companyId)
            ->withSum([
                'expenses' => function ($q) use ($fromDate, $toDate) {
                    $q->whereBetween('date', [$fromDate, $toDate]);
                }
            ], 'amount')
            ->withCount([
                'expenses' => function ($q) use ($fromDate, $toDate) {
                    $q->whereBetween('date', [$fromDate, $toDate]);
                }
            ])
            ->get()
            ->sortByDesc('expenses_sum_amount');

        return view('expenses.reports.unit', compact('unitExpenses', 'fromDate', 'toDate'));
    }

    public function categoryWise(Request $request)
    {
        $fromDate = $request->input('from_date', now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->input('to_date', now()->endOfMonth()->format('Y-m-d'));
        $companyId = session('selected_company_id');

        // Categories don't have company_id, but expenses do (via unit)
        // We want all categories, but sum expenses only for this company
        $categoryExpenses = ExpenseCategory::withSum([
            'expenses' => function ($q) use ($fromDate, $toDate, $companyId) {
                $q->whereBetween('date', [$fromDate, $toDate]);
                if ($companyId) {
                    $q->whereHas('unit', function ($uq) use ($companyId) {
                        $uq->where('company_id', $companyId);
                    });
                }
            }
        ], 'amount')
            ->withCount([
                'expenses' => function ($q) use ($fromDate, $toDate, $companyId) {
                    $q->whereBetween('date', [$fromDate, $toDate]);
                    if ($companyId) {
                        $q->whereHas('unit', function ($uq) use ($companyId) {
                            $uq->where('company_id', $companyId);
                        });
                    }
                }
            ])
            ->get()
            ->sortByDesc('expenses_sum_amount');

        return view('expenses.reports.category', compact('categoryExpenses', 'fromDate', 'toDate'));
    }

    public function export(Request $request)
    {
        $type = $request->input('type'); // monthly, unit, category
        // Re-use logic or extract to service. For now, simple implementation.

        $companyId = session('selected_company_id');
        $company = \App\Models\Company::find($companyId);

        if ($type == 'monthly') {
            $year = $request->input('year', now()->year);
            // ... fetch data similar to monthly() ...
            $monthlyExpenses = Expense::whereHas('unit', function ($q) use ($companyId) {
                if ($companyId) {
                    $q->where('company_id', $companyId);
                }
            })
                ->whereYear('date', $year)
                ->select(
                    DB::raw('MONTH(date) as month'),
                    DB::raw('SUM(amount) as total_amount')
                )
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            $data = $monthlyExpenses;
            $pdf = Pdf::loadView('expenses.reports.exports.monthly_pdf', compact('data', 'year', 'company'));
            return $pdf->download('monthly_expenses_' . $year . '.pdf');
        }

        if ($type == 'unit') {
            $fromDate = $request->input('from_date');
            $toDate = $request->input('to_date');

            $unitExpenses = Unit::where('company_id', $companyId)
                ->withSum([
                    'expenses' => function ($q) use ($fromDate, $toDate) {
                        $q->whereBetween('date', [$fromDate, $toDate]);
                    }
                ], 'amount')
                ->get()
                ->sortByDesc('expenses_sum_amount');

            $pdf = Pdf::loadView('expenses.reports.exports.unit_pdf', compact('unitExpenses', 'fromDate', 'toDate', 'company'));
            return $pdf->download('unit_expenses.pdf');
        }

        if ($type == 'category') {
            $fromDate = $request->input('from_date');
            $toDate = $request->input('to_date');

            $categoryExpenses = ExpenseCategory::withSum([
                'expenses' => function ($q) use ($fromDate, $toDate, $companyId) {
                    $q->whereBetween('date', [$fromDate, $toDate]);
                    if ($companyId) {
                        $q->whereHas('unit', function ($uq) use ($companyId) {
                            $uq->where('company_id', $companyId);
                        });
                    }
                }
            ], 'amount')
                ->get()
                ->sortByDesc('expenses_sum_amount');

            $pdf = Pdf::loadView('expenses.reports.exports.category_pdf', compact('categoryExpenses', 'fromDate', 'toDate', 'company'));
            return $pdf->download('category_expenses.pdf');
        }

        return redirect()->back();
    }
}
