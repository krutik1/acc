<?php

namespace App\Http\Controllers;

use App\Models\DriverPayment;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDriverPaymentController extends Controller
{
    public function index(Request $request)
    {
        $companyId = session('selected_company_id');
        
        $query = DriverPayment::with('user')
            ->where('company_id', $companyId);

        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }
        if ($request->filled('drivers')) {
            // Allows multiple drivers selection if passed as array, or single
            $drivers = is_array($request->drivers) ? $request->drivers : [$request->drivers];
            $query->whereIn('user_id', $drivers);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('payment_mode')) {
            $query->where('payment_mode', $request->payment_mode);
        }

        $payments = $query->latest('payment_date')->paginate(10)->withQueryString();
        
        $drivers = User::where('company_id', $companyId)
            ->where('role', 'driver')
            ->orderBy('name')
            ->get();

        return view('admin.driver_payments.index', compact('payments', 'drivers'));
    }

    public function create()
    {
        $companyId = session('selected_company_id');
        $drivers = User::where('company_id', $companyId)
            ->where('role', 'driver')
            ->orderBy('name')
            ->get();

        return view('admin.driver_payments.create', compact('drivers'));
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
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_mode' => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        DriverPayment::create([
            'company_id' => session('selected_company_id'),
            'user_id' => $request->user_id,
            'amount' => $request->amount,
            'payment_date' => $request->payment_date,
            'payment_mode' => $request->payment_mode,
            'status' => 'completed',
            'remarks' => $request->remarks,
        ]);

        return redirect()->route('admin.driver-payments.index')
            ->with('success', 'Driver payment recorded successfully.');
    }

    public function edit(DriverPayment $driverPayment)
    {
        if ($driverPayment->company_id != session('selected_company_id')) {
            abort(403);
        }

        $companyId = session('selected_company_id');
        $drivers = User::where('company_id', $companyId)
            ->where('role', 'driver')
            ->orderBy('name')
            ->get();

        return view('admin.driver_payments.edit', compact('driverPayment', 'drivers'));
    }

    public function update(Request $request, DriverPayment $driverPayment)
    {
        if ($driverPayment->company_id != session('selected_company_id')) {
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
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_mode' => 'required|string',
            'status' => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        $driverPayment->update([
            'user_id' => $request->user_id,
            'amount' => $request->amount,
            'payment_date' => $request->payment_date,
            'payment_mode' => $request->payment_mode,
            'status' => $request->status,
            'remarks' => $request->remarks,
        ]);

        return redirect()->route('admin.driver-payments.index')
            ->with('success', 'Payment updated successfully.');
    }

    public function destroy(DriverPayment $driverPayment)
    {
        if ($driverPayment->company_id != session('selected_company_id')) {
            abort(403);
        }

        $driverPayment->delete();

        return redirect()->route('admin.driver-payments.index')
            ->with('success', 'Payment deleted successfully.');
    }
}
