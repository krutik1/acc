<?php

namespace App\Http\Controllers;

use App\Models\DriverPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriverPaymentController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // 1. Advance Payments Query
        $payments = \Illuminate\Support\Facades\DB::table('driver_payments')
            ->select([
                'id',
                'payment_date as date',
                \Illuminate\Support\Facades\DB::raw("'Advance' as type"),
                'remarks as description',
                // Make amount negative for visual logic in backend or keep positive and handle in view?
                // User requirement: "Advance -> Red / Minus amount". 
                // Let's keep it signed in the query for easier sorting/logic if needed, 
                // but usually better to have absolute value and type. 
                // However, user specifically asked for "Advance ... -5000". 
                // Let's multiply by -1 for the amount column.
                \Illuminate\Support\Facades\DB::raw("(amount * -1) as amount"),
                'status',
                \Illuminate\Support\Facades\DB::raw("DATE_FORMAT(payment_date, '%Y-%m') as month_year"),
                'created_at'
            ])
            ->where('user_id', $user->id)
            ->where('company_id', session('selected_company_id')); // Ensure company scope

        // 2. Monthly Salaries Query
        $salaries = \Illuminate\Support\Facades\DB::table('driver_monthly_salaries')
            ->select([
                'id',
                // Use payment_date if paid, otherwise create_at or just end of month? 
                // For sorting, if it's "Finalized" but not paid, it should show up. 
                // Let's use created_at as a fallback for the 'date' column if payment_date is null, 
                // or maybe specific logic relative to the month? 
                // Usually Salary for "March" happens in "April". 
                // Let's use COALESCE(payment_date, created_at).
                \Illuminate\Support\Facades\DB::raw("COALESCE(payment_date, created_at) as date"),
                \Illuminate\Support\Facades\DB::raw("'Salary' as type"),
                \Illuminate\Support\Facades\DB::raw("'Monthly Salary' as description"),
                'payable_amount as amount',
                'status',
                'month as month_year', // stored as Y-m string
                'created_at'
            ])
            ->where('user_id', $user->id)
            ->where('company_id', session('selected_company_id'));

        // Apply Filters to the Queries (Needs to be wrapped or applied to both)
        // Since Union requires same columns, we can't easily filter *after* union without a subquery in Laravel Query Builder easily 
        // unless we use the `union` method on the builder and then order/paginate.
        // But `where` on the unioned result isn't directly supported by Eloquent builder without subquery.
        // Easiest is to apply matching filters to both queries if applicable.

        if ($request->filled('date_from')) {
            $payments->whereDate('payment_date', '>=', $request->date_from);
            // Salary date logic? "date" column is constructed. 
            // Filtering salaries by "payment date" when it might be null (generated) is tricky.
            // Maybe filter by the computed date?
            // For now, let's filter the underlying date columns.
            $salaries->whereDate(\Illuminate\Support\Facades\DB::raw("COALESCE(payment_date, created_at)"), '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $payments->whereDate('payment_date', '<=', $request->date_to);
            $salaries->whereDate(\Illuminate\Support\Facades\DB::raw("COALESCE(payment_date, created_at)"), '<=', $request->date_to);
        }

        if ($request->filled('month')) { // Format Y-m
            $payments->where(\Illuminate\Support\Facades\DB::raw("DATE_FORMAT(payment_date, '%Y-%m')"), $request->month);
            $salaries->where('month', $request->month);
        }

        if ($request->filled('year')) {
            $payments->whereYear('payment_date', $request->year);
            $salaries->where(\Illuminate\Support\Facades\DB::raw("LEFT(month, 4)"), $request->year);
        }

        if ($request->filled('status')) {
            $payments->where('status', $request->status);
            $salaries->where('status', $request->status);
        }

        // Filter by Type (Optimized: don't union if only one type requested)
        if ($request->filled('payment_type')) {
            if ($request->payment_type === 'Advance') {
                $query = $payments;
            } elseif ($request->payment_type === 'Salary') {
                $query = $salaries;
            } else {
                $query = $payments->union($salaries);
            }
        } else {
            $query = $payments->union($salaries);
        }

        $records = $query->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('driver.payments.index', compact('records'));
    }
}
