<?php

namespace App\Http\Controllers;

use App\Models\MonthlySalary;
use App\Models\User;
use App\Models\Upaad;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SalaryController extends Controller
{
    public function index(Request $request)
    {
        $query = MonthlySalary::with('user')
            ->whereHas('user', function($q) {
                $q->where('company_id', session('selected_company_id'));
            });

        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $salaries = $query->latest('month')->paginate(10);

        return view('admin.salaries.index', compact('salaries'));
    }

    public function create()
    {
        $employees = User::where('company_id', session('selected_company_id'))
            ->where('role', 'employee')
            ->whereIn('payment_mode', ['fixed', 'pcs']) // Removed trip
            ->orderBy('name')
            ->get();

        return view('admin.salaries.create', compact('employees'));
    }

    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'month' => 'required|date_format:Y-m',
            'total_pieces' => 'nullable|numeric|min:0', // Required if Piece rate
        ]);

        $user = User::findOrFail($validated['user_id']);
        $month = $validated['month'];

        // Check if salary already exists
        $exists = MonthlySalary::where('user_id', $user->id)
            ->where('month', $month)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Salary for this employee and month already exists.');
        }

        $startOfMonth = Carbon::parse($month)->startOfMonth();
        $endOfMonth = Carbon::parse($month)->endOfMonth();

        // Calculate Earnings
        $fixedSalary = 0;
        $pieceRate = 0;
        $totalPieces = 0;
        $totalAmount = 0;
        $salaryType = 'fixed';

        if ($user->payment_mode === 'fixed') {
            $fixedSalary = $user->fixed_salary;
            $totalAmount = $fixedSalary;
            $salaryType = 'fixed';
        } elseif ($user->payment_mode === 'pcs') {
            $pieceRate = $user->pcs_rate;
            $totalPieces = $request->input('total_pieces', 0);
            $totalAmount = $totalPieces * $pieceRate;
            $salaryType = 'piece';
        } elseif ($user->payment_mode === 'trip') {
            // Logic for Trip based? 
            // For now, let's treat it similar to Piece but verify requirement. 
            // The user asked for "Fixed Salary" and "Piece Rate Salary".
            // I'll skip auto-calculation for Trip here unless requested, OR handle it manually.
            // But let's check if user wants to enter amount manually or calculate.
            // For simplicity, let's allow manual override or just basic calc.
            // Existing 'trips' table exists... maybe use that?
            // "Piece Rate Salary... Upaad is deducted... Total earning: Pieces * Rate"
            // For Trip, we could count trips in that month.
             $salaryType = 'piece'; // Using 'piece' enum for variable pay? Or add 'trip' to enum?
             // Schema has enum('salary_type', ['fixed', 'piece']);
             // Maybe I should add 'trip' to migration or just map 'trip' to 'piece' logic (variable).
             // Let's stick to Fixed/Piece as per prompt. If user selects Trip employee, maybe just show warning or allow manual?
             
             // Let's try to count trips if mode is trip.
             /*
             $tripCount = \App\Models\Trip::where('driver_id', $user->id)
                 ->whereBetween('trip_date', [$startOfMonth, $endOfMonth])
                 ->count();
             $totalAmount = $tripCount * $user->trip_rate;
             */
             // I'll focus on Fixed and Piece as explicitly requested.
        }

        // Calculate Upaad
        $totalUpaad = Upaad::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $payableAmount = $totalAmount - $totalUpaad;

        return view('admin.salaries.preview', compact(
            'user', 'month', 'fixedSalary', 'pieceRate', 'totalPieces', 
            'totalAmount', 'totalUpaad', 'payableAmount', 'salaryType'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'month' => 'required',
            'salary_type' => 'required|in:fixed,piece',
            'fixed_salary' => 'numeric',
            'piece_rate' => 'numeric',
            'total_pieces' => 'numeric',
            'total_amount' => 'required|numeric',
            'total_upaad' => 'required|numeric',
            'payable_amount' => 'required|numeric',
            'remarks' => 'nullable|string',
        ]);

        MonthlySalary::create($validated);

        return redirect()->route('admin.salaries.index')->with('success', 'Salary generated successfully.');
    }
    
    public function markPaid(MonthlySalary $salary)
    {
        if ($salary->user->company_id != session('selected_company_id')) {
            abort(403);
        }
        
        $salary->update([
            'status' => 'paid',
            'payment_date' => now(),
        ]);
        
        return back()->with('success', 'Salary marked as Paid.');
    }
    
    public function destroy(MonthlySalary $salary)
    {
        if ($salary->user->company_id != session('selected_company_id')) {
            abort(403);
        }
        
        $salary->delete();
        
        return redirect()->route('admin.salaries.index')->with('success', 'Salary record deleted.');
    }
}
