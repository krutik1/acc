<?php

namespace App\Http\Controllers;

use App\Models\DriverMonthlySalary;
use App\Models\User;
use App\Models\Trip;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminDriverSalaryController extends Controller
{
    public function index(Request $request)
    {
        $companyId = session('selected_company_id');
        
        $query = DriverMonthlySalary::with('user')
            ->where('company_id', $companyId);

        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $salaries = $query->latest('month')->paginate(10);

        return view('admin.driver_salaries.index', compact('salaries'));
    }

    public function create()
    {
        $drivers = User::where('company_id', session('selected_company_id'))
            ->where('role', 'driver')
            ->orderBy('name')
            ->get();

        return view('admin.driver_salaries.create', compact('drivers'));
    }

    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'month' => 'required|date_format:Y-m',
        ]);

        $user = User::findOrFail($validated['user_id']);
        if ($user->company_id != session('selected_company_id')) {
            abort(403);
        }
        
        $month = $validated['month'];

        // Check if salary already exists
        $exists = DriverMonthlySalary::where('user_id', $user->id)
            ->where('month', $month)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Salary payment for this driver and month already exists. Please edit or delete the existing one.');
        }

        $startOfMonth = Carbon::parse($month)->startOfMonth();
        $endOfMonth = Carbon::parse($month)->endOfMonth();

        // Fetch Trips
        $trips = Trip::where('user_id', $user->id)
            ->whereBetween('trip_date', [$startOfMonth, $endOfMonth])
            ->where('status', 'approved')
            ->get();

        $totalTrips = $trips->count();
        $totalQuantity = $trips->sum('quantity');
        
        // Calculate Earnings from Trips (Split by Type)
        $fixedTripAmount = 0;
        $pcsTripAmount = 0;
        $totalAmount = 0;

        foreach ($trips as $trip) {
            // Use stored commission if available (preserves historical rates), otherwise calculate
            $commission = $trip->driver_commission > 0 ? $trip->driver_commission : $trip->calculateCommission();
            $totalAmount += $commission;
            
            // Determine type based on effective payment mode
            // We use the same logic as calculateCommission to determine mode
            $mode = $trip->effective_payment_mode; 
            
            if ($mode === 'trip') {
                $fixedTripAmount += $commission;
            } else {
                $pcsTripAmount += $commission;
            }
        }

        // Calculate Advance Payments (Formerly Ad-hoc / Upaad)
        // Advance Payment must be recorded with payment date and is deducted in the month it is paid
        $advanceAmount = \App\Models\DriverPayment::where('user_id', $user->id)
            ->whereBetween('payment_date', [$startOfMonth, $endOfMonth])
            ->where('company_id', session('selected_company_id'))
            ->sum('amount');

        // Initial Payable
        // Logic: (Fixed + PCS) - Advance
        $payableAmount = $totalAmount - $advanceAmount;

        // Passed to view for preview
        $bonus = 0;
        $deduction = 0;

        return view('admin.driver_salaries.preview', compact(
            'user', 'month', 'totalTrips', 'totalQuantity', 
            'totalAmount', 'fixedTripAmount', 'pcsTripAmount',
            'advanceAmount', 'payableAmount', 'bonus', 'deduction'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'month' => 'required',
            'total_trips' => 'required|integer',
            'total_quantity' => 'required|numeric',
            'total_amount' => 'required|numeric',
            'fixed_trip_amount' => 'required|numeric',
            'pcs_trip_amount' => 'required|numeric',
            'advance_amount' => 'required|numeric',
            'bonus' => 'required|numeric',
            'deduction' => 'required|numeric',
            'payable_amount' => 'required|numeric',
            'remarks' => 'nullable|string',
        ]);

        // Recalculate Net Payable in Backend for "Accounting-Level Accuracy"
        // Net = (Fixed + PCS) - Advance + Bonus - OtherDeductions
        
        $earnings = $validated['fixed_trip_amount'] + $validated['pcs_trip_amount'];
        $deductions = $validated['advance_amount'] + $validated['deduction'];
        $additions = $validated['bonus'];
        
        $netPayable = $earnings - $deductions + $additions;
        
        $monthlySalary = new DriverMonthlySalary($validated);
        $monthlySalary->company_id = session('selected_company_id');
        $monthlySalary->payable_amount = $netPayable; // Override with backend calculation
        $monthlySalary->status = 'generated';
        $monthlySalary->save();

        return redirect()->route('admin.driver-salaries.index')->with('success', 'Driver Monthly Payment generated successfully.');
    }

    public function markPaid(DriverMonthlySalary $salary)
    {
        if ($salary->company_id != session('selected_company_id')) {
            abort(403);
        }
        
        $salary->update([
            'status' => 'paid',
            'payment_date' => now(),
        ]);
        
        return back()->with('success', 'Marked as Paid.');
    }

    public function destroy(DriverMonthlySalary $driver_salary)
    {
        if ($driver_salary->company_id != session('selected_company_id')) {
            abort(403);
        }
        
        $driver_salary->delete();
        
        return redirect()->route('admin.driver-salaries.index')->with('success', 'Record deleted.');
    }
}
