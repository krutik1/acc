<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'employee')
            ->where('company_id', session('selected_company_id'));

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('salary_type')) {
            $query->where('payment_mode', $request->salary_type);
        }

        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'active') {
                $query->where('is_blocked', false);
            } elseif ($status === 'inactive') {
                $query->where('is_blocked', true);
            }
        }

        $employees = $query->latest()->paginate(10);

        return view('admin.employees.index', compact('employees'));
    }

    public function create()
    {
        return view('admin.employees.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'payment_mode' => 'required|in:fixed,pcs,trip', // Changed to payment_mode
            'fixed_salary' => 'nullable|numeric|min:0',
            'pcs_rate' => 'nullable|numeric|min:0',
            'trip_rate' => 'nullable|numeric|min:0',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make(\Illuminate\Support\Str::random(16)), 
            'company_id' => session('selected_company_id'),
            'role' => 'employee',
            'payment_mode' => $validated['payment_mode'], // Changed to payment_mode
            'fixed_salary' => $validated['fixed_salary'] ?? 0,
            'pcs_rate' => $validated['pcs_rate'] ?? 0,
            'trip_rate' => $validated['trip_rate'] ?? 0,
        ]);

        return redirect()->route('admin.employees.index')->with('success', 'Employee created successfully.');
    }

    public function edit(User $employee)
    {
        if ($employee->company_id != session('selected_company_id') || !$employee->hasRole('employee')) {
            abort(403);
        }
        
        return view('admin.employees.edit', compact('employee'));
    }

    public function update(Request $request, User $employee)
    {
        if ($employee->company_id != session('selected_company_id') || !$employee->hasRole('employee')) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $employee->id,
            'payment_mode' => 'required|in:fixed,pcs,trip', // Changed to payment_mode
            'fixed_salary' => 'nullable|numeric|min:0',
            'pcs_rate' => 'nullable|numeric|min:0',
            'trip_rate' => 'nullable|numeric|min:0',
            'is_blocked' => 'boolean',
        ]);

        $employee->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'payment_mode' => $validated['payment_mode'], // Changed to payment_mode
            'fixed_salary' => $validated['fixed_salary'] ?? 0,
            'pcs_rate' => $validated['pcs_rate'] ?? 0,
            'trip_rate' => $validated['trip_rate'] ?? 0,
            'is_blocked' => $request->has('is_blocked'),
        ]);

        return redirect()->route('admin.employees.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy(User $employee)
    {
        if ($employee->company_id != session('selected_company_id') || !$employee->hasRole('employee')) {
            abort(403);
        }

        $employee->delete();

        return redirect()->route('admin.employees.index')->with('success', 'Employee deleted successfully.');
    }
}
