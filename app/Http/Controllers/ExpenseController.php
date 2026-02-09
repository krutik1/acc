<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Unit;
use App\Models\ExpenseCategory;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with(['unit', 'category', 'creator']);

        // Filter by Company (via Unit)
        $companyId = session('selected_company_id');
        if ($companyId) {
            $query->whereHas('unit', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            });
        }

        // Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('amount', 'like', "%{$search}%")
                    ->orWhere('reference_no', 'like', "%{$search}%");
            });
        }

        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        if ($request->filled('category_id')) {
            $query->where('expense_category_id', $request->category_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        if ($request->filled('payment_mode')) {
            $query->where('payment_mode', $request->payment_mode);
        }

        $expenses = $query->latest('date')->paginate(10)->withQueryString();
        $units = Unit::when($companyId, fn($q) => $q->where('company_id', $companyId))->get();
        $categories = ExpenseCategory::all();

        return view('expenses.index', compact('expenses', 'units', 'categories'));
    }

    public function create()
    {
        $companyId = session('selected_company_id');
        $units = Unit::when($companyId, fn($q) => $q->where('company_id', $companyId))->get();
        $categories = ExpenseCategory::all();

        return view('expenses.create', compact('units', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'unit_id' => 'required|exists:units,id',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0',
            'payment_mode' => 'required|string',
            'bank_name' => 'nullable|required_if:payment_mode,Bank,Cheque|string',
            'reference_no' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'description' => 'nullable|string',
        ]);

        $data = $request->except(['attachment']);
        $data['created_by'] = Auth::id();

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('uploads/expenses', 'public');
        }

        Expense::create($data);

        return redirect()->route('expenses.index')->with('success', 'Expense added successfully.');
    }

    public function edit(Expense $expense)
    {
        $companyId = session('selected_company_id');
        $units = Unit::when($companyId, fn($q) => $q->where('company_id', $companyId))->get();
        $categories = ExpenseCategory::all();

        return view('expenses.edit', compact('expense', 'units', 'categories'));
    }

    public function update(Request $request, Expense $expense)
    {
        $request->validate([
            'date' => 'required|date',
            'unit_id' => 'required|exists:units,id',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0',
            'payment_mode' => 'required|string',
            'bank_name' => 'nullable|required_if:payment_mode,Bank,Cheque|string',
            'reference_no' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'description' => 'nullable|string',
        ]);

        $data = $request->except(['attachment']);

        if ($request->hasFile('attachment')) {
            // Delete old
            if ($expense->attachment_path && Storage::disk('public')->exists($expense->attachment_path)) {
                Storage::disk('public')->delete($expense->attachment_path);
            }
            $data['attachment_path'] = $request->file('attachment')->store('uploads/expenses', 'public');
        }

        $expense->update($data);

        return redirect()->route('expenses.index')->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        if ($expense->attachment_path && Storage::disk('public')->exists($expense->attachment_path)) {
            // Soft delete, so maybe don't delete file immediately? 
            // Standard practice for soft deletes is to keep files. 
            // If hard deleting, then delete file.
            // Requirement says "Soft delete preferred".
        }

        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'Expense deleted successfully.');
    }
}
