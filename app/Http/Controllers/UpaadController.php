<?php

namespace App\Http\Controllers;

use App\Models\Upaad;
use App\Models\User;
use Illuminate\Http\Request;

class UpaadController extends Controller
{
    public function index(Request $request)
    {
        $query = Upaad::with('user')
            ->whereHas('user', function($q) {
                $q->where('company_id', session('selected_company_id'));
            });

        if ($request->filled('employee_id')) {
            $query->where('user_id', $request->employee_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $upaads = $query->latest('date')->paginate(10);
        
        // Get employees for filter and for create modal
        $employees = User::where('company_id', session('selected_company_id'))
            ->where('role', 'employee')
            ->orderBy('name')
            ->get();

        return view('admin.upaads.index', compact('upaads', 'employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'remarks' => 'nullable|string|max:255',
        ]);

        // Verify user belongs to company
        $user = User::findOrFail($validated['user_id']);
        if ($user->company_id != session('selected_company_id')) {
            abort(403);
        }

        Upaad::create($validated);

        return redirect()->back()->with('success', 'Upaad recorded successfully.');
    }

    public function edit(Upaad $upaad)
    {
        if ($upaad->user->company_id != session('selected_company_id')) {
            abort(403);
        }

        $employees = User::where('company_id', session('selected_company_id'))
            ->where('role', 'employee')
            ->orderBy('name')
            ->get();

        return view('admin.upaads.edit', compact('upaad', 'employees'));
    }

    public function update(Request $request, Upaad $upaad)
    {
        if ($upaad->user->company_id != session('selected_company_id')) {
            abort(403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'remarks' => 'nullable|string|max:255',
        ]);
        
        // Verify user (if changed) belongs to company
        $user = User::findOrFail($validated['user_id']);
        if ($user->company_id != session('selected_company_id')) {
            abort(403);
        }

        $upaad->update($validated);

        return redirect()->route('admin.upaads.index')->with('success', 'Upaad updated successfully.');
    }

    public function destroy(Upaad $upaad)
    {
        if ($upaad->user->company_id != session('selected_company_id')) {
            abort(403);
        }

        $upaad->delete();

        return redirect()->route('admin.upaads.index')->with('success', 'Upaad deleted successfully.');
    }
}
