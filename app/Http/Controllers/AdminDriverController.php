<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminDriverController extends Controller
{
    public function index()
    {
        $drivers = \App\Models\User::where('role', 'driver')
            ->where('company_id', session('selected_company_id'))
            ->latest()
            ->paginate(10);
        return view('admin.drivers.index', compact('drivers'));
    }

    public function create()
    {
        return view('admin.drivers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        \App\Models\User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
            'role' => 'driver',
            'company_id' => session('selected_company_id'),
        ]);

        return redirect()->route('admin.drivers.index')->with('success', 'Driver created successfully.');
    }

    public function edit(\App\Models\User $driver)
    {
        // Ensure driver belongs to current company
        if ($driver->company_id != session('selected_company_id') || !$driver->isDriver()) {
            abort(403);
        }
        return view('admin.drivers.edit', compact('driver'));
    }

    public function update(Request $request, \App\Models\User $driver)
    {
        if ($driver->company_id != session('selected_company_id') || !$driver->isDriver()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $driver->id,
            'password' => 'nullable|string|min:8|confirmed',
            'payment_mode' => 'required|in:trip,pcs',
            'trip_rate' => 'nullable|numeric|min:0',
            'pcs_rate' => 'nullable|numeric|min:0',
        ]);

        $driver->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'payment_mode' => $validated['payment_mode'],
            'trip_rate' => $validated['trip_rate'] ?? 0,
            'pcs_rate' => $validated['pcs_rate'] ?? 0,
        ]);

        if ($request->filled('password')) {
            $driver->update(['password' => \Illuminate\Support\Facades\Hash::make($validated['password'])]);
        }

        return redirect()->route('admin.drivers.index')->with('success', 'Driver updated successfully.');
    }

    public function toggleBlock(\App\Models\User $driver)
    {
        if ($driver->company_id != session('selected_company_id') || !$driver->isDriver()) {
            abort(403);
        }

        $driver->update(['is_blocked' => !$driver->is_blocked]);
        
        $status = $driver->is_blocked ? 'blocked' : 'unblocked';
        return back()->with('success', "Driver has been $status.");
    }

    public function destroy(\App\Models\User $driver)
    {
        if ($driver->company_id != session('selected_company_id') || !$driver->isDriver()) {
            abort(403);
        }

        // Optional: Check if driver has trips? Usually soft delete or restrict.
        // For now, assuming standard delete (cascade handling should be in DB if trips exist, or restriction)
        // User requested "Delete driver logic". 
        // Note: Trip table migration has 'cascade' on delete user_id, so trips will be deleted.
        $driver->delete();

        return redirect()->route('admin.drivers.index')->with('success', 'Driver deleted successfully.');
    }
}
