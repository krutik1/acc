<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanySelectionController extends Controller
{
    /**
     * Show company selection page.
     */
    public function select()
    {
        if (auth()->user()->hasRole('driver')) {
            return redirect()->route('driver.dashboard');
        }

        $companies = Company::orderBy('name')->get();
        return view('companies.select', compact('companies'));
    }

    /**
     * Store selected company in session.
     */
    public function store(Request $request)
    {
        $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
        ]);

        $company = Company::findOrFail($request->company_id);
        
        session([
            'selected_company_id' => $company->id,
            'company_name' => $company->name
        ]);

        return redirect()->route('dashboard');
    }

    /**
     * Switch company.
     */
    public function switch()
    {
        if (auth()->user()->hasRole('driver')) {
            return redirect()->route('driver.dashboard');
        }

        session()->forget('selected_company_id');
        return redirect()->route('companies.select');
    }
}
