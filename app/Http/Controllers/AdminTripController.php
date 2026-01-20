<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminTripController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\Trip::where('company_id', session('selected_company_id'))
            ->with('user');

        if ($request->filled('driver_id')) {
            $query->where('user_id', $request->driver_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('trip_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('trip_date', '<=', $request->date_to);
        }

        $trips = $query->latest()->paginate(5)->withQueryString();
        
        $drivers = \App\Models\User::where('company_id', session('selected_company_id'))
            ->where('role', 'driver')
            ->orderBy('name')
            ->get();

        return view('admin.trips.index', compact('trips', 'drivers'));
    }

    public function create()
    {
        // Get all users with driver role for the current company
        $drivers = \App\Models\User::where('company_id', session('selected_company_id'))
            ->where('role', 'driver')
            ->orderBy('name')
            ->get();
            
        return view('admin.trips.create', compact('drivers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => [
                'required',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    $driver = \App\Models\User::find($value);
                    if ($driver && $driver->company_id != session('selected_company_id')) {
                        $fail('The selected driver does not belong to the current company.');
                    }
                },
            ],
            'pickup_location' => 'required|string|max:255',
            'drop_location' => 'required|string|max:255',
            'trip_date' => 'required|date',
            'description' => 'nullable|string',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'payment_mode' => 'nullable|in:trip,pcs',
            'trip_rate' => 'nullable|numeric|min:0',
            'pcs_rate' => 'nullable|numeric|min:0',
        ]);

        $trip = \App\Models\Trip::create([
            'company_id' => session('selected_company_id'),
            'user_id' => $request->user_id,
            'pickup_location' => $request->pickup_location,
            'drop_location' => $request->drop_location,
            'trip_date' => $request->trip_date,
            'description' => $request->description,
            'quantity' => $request->quantity,
            'unit' => $request->unit,
            'status' => 'approved',
            'payment_mode' => $request->payment_mode,
            'trip_rate' => $request->trip_rate,
            'pcs_rate' => $request->pcs_rate,
        ]);

        // Calculate and save commission
        $trip->driver_commission = $trip->calculateCommission();
        $trip->save();

        return redirect()->route('admin.trips.index')->with('success', 'Trip created successfully.');
    }

    public function edit(\App\Models\Trip $trip)
    {
        if ($trip->company_id != session('selected_company_id')) {
            abort(403);
        }

        $drivers = \App\Models\User::where('company_id', session('selected_company_id'))
            ->where('role', 'driver')
            ->orderBy('name')
            ->get();

        return view('admin.trips.edit', compact('trip', 'drivers'));
    }

    public function update(Request $request, \App\Models\Trip $trip)
    {
        if ($trip->company_id != session('selected_company_id')) {
            abort(403);
        }

        $request->validate([
            'user_id' => [
                'required',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    $driver = \App\Models\User::find($value);
                    if ($driver && $driver->company_id != session('selected_company_id')) {
                        $fail('The selected driver does not belong to the current company.');
                    }
                },
            ],
            'pickup_location' => 'required|string|max:255',
            'drop_location' => 'required|string|max:255',
            'trip_date' => 'required|date',
            'description' => 'nullable|string',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'status' => 'required|in:pending,approved,rejected',
            'payment_mode' => 'nullable|in:trip,pcs',
            'trip_rate' => 'nullable|numeric|min:0',
            'pcs_rate' => 'nullable|numeric|min:0',
        ]);

        $trip->fill($request->all());
        
        // Recalculate commission
        $trip->driver_commission = $trip->calculateCommission();
        $trip->save();

        return redirect()->route('admin.trips.index')->with('success', 'Trip updated successfully.');
    }

    public function destroy(\App\Models\Trip $trip)
    {
        if ($trip->company_id != session('selected_company_id')) {
            abort(403);
        }
        $trip->delete();
        return redirect()->route('admin.trips.index')->with('success', 'Trip deleted successfully.');
    }

    public function updateStatus(Request $request, \App\Models\Trip $trip)
    {
        if ($trip->company_id != session('selected_company_id')) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $trip->update(['status' => $request->status]);

        return back()->with('success', 'Trip status updated.');
    }
}
