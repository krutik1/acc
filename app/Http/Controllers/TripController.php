<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Illuminate\Http\Request;

class TripController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = auth()->user()->trips()->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('trip_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('trip_date', '<=', $request->date_to);
        }

        $trips = $query->paginate(10)->withQueryString();
        return view('driver.trips.index', compact('trips'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('driver.trips.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pickup_location' => 'required|string|max:255',
            'drop_location' => 'required|string|max:255',
            'trip_date' => 'required|date',
            'description' => 'nullable|string',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
        ]);

        $user = $request->user();
        $tripData = $validated + [
            'company_id' => $user->company_id,
            'status' => 'pending',
            'payment_mode' => $user->payment_mode ?? 'trip',
            'trip_rate' => $user->trip_rate ?? 0,
            'pcs_rate' => $user->pcs_rate ?? 0,
        ];

        // Create instance to calculate commission logic (can re-use model logic or do it here)
        // Since calculateCommission uses attributes, let's create, fill, calculate, save.
        $trip = new Trip($tripData);
        $trip->user_id = $user->id; // set user_id explicitly or via relation
        $trip->driver_commission = $trip->calculateCommission(); // This requires the trip to have data. calculateCommission uses 'quantity' or 'pcs'
        // Oops, Trip model used 'quantity' in my previous edit?
        // Let's check Trip.php from previous turns. Yes, I changed it to use 'quantity'.
        // $trip->quantity is in $validated.
        
        $trip->save();

        return redirect()->route('driver.trips.index')->with('success', 'Trip created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Trip $trip)
    {
        if ($trip->user_id !== auth()->id()) {
            abort(403);
        }
        return view('driver.trips.show', compact('trip'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Trip $trip)
    {
        if ($trip->user_id !== auth()->id()) {
            abort(403);
        }
        return view('driver.trips.edit', compact('trip'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Trip $trip)
    {
        if ($trip->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'pickup_location' => 'required|string|max:255',
            'drop_location' => 'required|string|max:255',
            'trip_date' => 'required|date',
            'description' => 'nullable|string',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
        ]);

        $trip->update($validated + ['status' => 'pending']);

        return redirect()->route('driver.trips.index')->with('success', 'Trip updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Trip $trip)
    {
        //
    }
}
