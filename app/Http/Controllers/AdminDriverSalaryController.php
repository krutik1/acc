<?php

namespace App\Http\Controllers;

use App\Models\DriverMonthlySalary;
use App\Models\User;
use App\Models\Trip;
use App\Models\Upaad;
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
            ->where('status', 'completed') // Assuming only completed trips are paid? Or all? Usually completed.
            ->get();

        $totalTrips = $trips->count();
        $totalQuantity = $trips->sum('quantity'); // If relevant
        
        // Calculate Earnings from Trips
        $totalAmount = 0;
        foreach ($trips as $trip) {
            $totalAmount += $trip->calculateCommission();
        }

        // Calculate Upaad (Advances)
        $totalUpaad = Upaad::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        // Calculate Ad-hoc Driver Payments
        $totalDriverPayment = \App\Models\DriverPayment::where('user_id', $user->id)
            ->whereBetween('payment_date', [$startOfMonth, $endOfMonth])
            ->where('company_id', session('selected_company_id'))
            ->sum('amount');

        // Initial Payable
        $payableAmount = $totalAmount - $totalUpaad - $totalDriverPayment;

        // Passed to view for preview
        $bonus = 0;
        $deduction = 0;

        return view('admin.driver_salaries.preview', compact(
            'user', 'month', 'totalTrips', 'totalQuantity', 
            'totalAmount', 'totalUpaad', 'totalDriverPayment', 'payableAmount', 'bonus', 'deduction'
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
            'total_upaad' => 'required|numeric',
            'total_driver_payment' => 'required|numeric',
            'bonus' => 'required|numeric',
            'deduction' => 'required|numeric',
            'payable_amount' => 'required|numeric',
            'remarks' => 'nullable|string',
        ]);

        // Recalculate Net Payable in Backend for "Accounting-Level Accuracy"
        $netPayable = ($validated['total_amount'] - $validated['total_upaad'] - $validated['total_driver_payment']) + $validated['bonus'] - $validated['deduction'];
        
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
