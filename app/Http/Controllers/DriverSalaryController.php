<?php

namespace App\Http\Controllers;

use App\Models\DriverMonthlySalary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriverSalaryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = DriverMonthlySalary::where('user_id', $user->id);

        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $salaries = $query->latest('month')->paginate(10);

        return view('driver.salaries.index', compact('salaries'));
    }
}
