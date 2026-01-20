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
        
        $query = DriverPayment::where('user_id', $user->id);

        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('payment_mode')) {
            $query->where('payment_mode', $request->payment_mode);
        }

        $payments = $query->latest('payment_date')->paginate(10)->withQueryString();

        return view('driver.payments.index', compact('payments'));
    }
}
