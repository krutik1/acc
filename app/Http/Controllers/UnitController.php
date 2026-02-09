<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Company;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Unit::query();

        // If you want to filter by company (though usually tied to session/default company or all)
        // Assuming we show all units for now or filter by current company if applicable. 
        // Based on CompanyController, it seems there is a concept of "selected_company_id" in session.
        $companyId = session('selected_company_id');
        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $units = $query->latest()->paginate(10)->withQueryString();

        return view('units.index', compact('units'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('units.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $companyId = session('selected_company_id');
        if (!$companyId) {
            // Fallback or error, but for now let's assume one exists or pick first
            $companyId = Company::getDefault()->id;
        }

        Unit::create([
            'company_id' => $companyId,
            'name' => $request->name,
        ]);

        return redirect()->route('units.index')->with('success', 'Unit created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Unit $unit)
    {
        return view('units.edit', compact('unit'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Unit $unit)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $unit->update([
            'name' => $request->name,
        ]);

        return redirect()->route('units.index')->with('success', 'Unit updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Unit $unit)
    {
        $unit->delete();
        return redirect()->route('units.index')->with('success', 'Unit deleted successfully.');
    }
}
