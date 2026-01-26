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
    public function index(Request $request)
    {
        $query = Company::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('gst_number', 'like', "%{$search}%")
                  ->orWhere('mobile_numbers', 'like', "%{$search}%");
            });
        }

        $companies = $query->orderBy('is_default', 'desc')
                           ->orderBy('name')
                           ->paginate(10)
                           ->withQueryString();

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
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,svg', 'max:2048'],
            'favicon' => ['nullable', 'mimes:png,ico,svg', 'max:512'],
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

            $companyData = $validated;
            
            // Handle Logo Upload
            if ($request->hasFile('logo')) {
                $path = $request->file('logo')->store('uploads/companies/logos', 'public');
                $companyData['logo_path'] = $path;
                unset($companyData['logo']);
            }
            
            // Handle Favicon Upload
            if ($request->hasFile('favicon')) {
                $path = $request->file('favicon')->store('uploads/companies/favicons', 'public');
                $companyData['favicon_path'] = $path;
                unset($companyData['favicon']);
            }

            Company::create($companyData);

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
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,svg', 'max:2048'],
            'favicon' => ['nullable', 'mimes:png,ico,svg', 'max:512'],
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

            $updateData = $validated;
            
            // Handle Logo Upload
            if ($request->hasFile('logo')) {
                // Delete old logo
                if ($company->logo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($company->logo_path)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($company->logo_path);
                }
                
                $path = $request->file('logo')->store('uploads/companies/logos', 'public');
                $updateData['logo_path'] = $path;
                unset($updateData['logo']);
            }

            // Handle Favicon Upload
            if ($request->hasFile('favicon')) {
                // Delete old favicon
                if ($company->favicon_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($company->favicon_path)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($company->favicon_path);
                }

                $path = $request->file('favicon')->store('uploads/companies/favicons', 'public');
                $updateData['favicon_path'] = $path;
                unset($updateData['favicon']);
            }

            $company->update($updateData);

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
        if ($company->logo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($company->logo_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($company->logo_path);
        }
        
        if ($company->favicon_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($company->favicon_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($company->favicon_path);
        }

        $company->delete();

        return redirect()
            ->route('companies.index')
            ->with('success', 'Company deleted successfully.');
    }
}
