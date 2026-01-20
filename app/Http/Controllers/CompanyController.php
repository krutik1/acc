<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    /**
     * Display a listing of companies.
     */
    public function index()
    {
        $companies = Company::orderBy('is_default', 'desc')->orderBy('name')->paginate(10);
        return view('companies.index', compact('companies'));
    }

    /**
     * Show the form for creating a new company.
     */
    public function create()
    {
        return view('companies.create');
    }

    /**
     * Store a newly created company in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'gst_number' => ['nullable', 'string', 'max:20'],
            'state_code' => ['nullable', 'string', 'max:10'],
            'mobile_numbers' => ['nullable', 'string'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'ifsc_code' => ['nullable', 'string', 'max:20'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'terms_conditions' => ['nullable', 'string'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        DB::beginTransaction();
        
        try {
            // If this is set as default, unset other defaults
            if ($request->has('is_default') && $request->is_default) {
                Company::where('is_default', true)->update(['is_default' => false]);
            }

            Company::create($validated);

            DB::commit();

            return redirect()
                ->route('companies.index')
                ->with('success', 'Company created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to create company. Please try again.');
        }
    }

    /**
     * Display the specified company.
     */
    public function show(Company $company)
    {
        return view('companies.show', compact('company'));
    }

    /**
     * Show the form for editing the specified company.
     */
    public function edit(Company $company)
    {
        return view('companies.edit', compact('company'));
    }

    /**
     * Update the specified company in storage.
     */
    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'gst_number' => ['nullable', 'string', 'max:20'],
            'state_code' => ['nullable', 'string', 'max:10'],
            'mobile_numbers' => ['nullable', 'string'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'ifsc_code' => ['nullable', 'string', 'max:20'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'terms_conditions' => ['nullable', 'string'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        DB::beginTransaction();
        
        try {
            // If this is set as default, unset other defaults
            if ($request->has('is_default') && $request->is_default) {
                Company::where('is_default', true)->where('id', '!=', $company->id)->update(['is_default' => false]);
            }

            $company->update($validated);

            DB::commit();

            return redirect()
                ->route('companies.index')
                ->with('success', 'Company updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to update company. Please try again.');
        }
    }

    /**
     * Remove the specified company from storage.
     */
    public function destroy(Company $company)
    {
        $company->delete();

        return redirect()
            ->route('companies.index')
            ->with('success', 'Company deleted successfully.');
    }
}
