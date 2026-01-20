<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function dashboard()
    {
        $trips = auth()->user()->trips()->latest()->limit(5)->get();
        return view('driver.dashboard', compact('trips'));
    }
}
