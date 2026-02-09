<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = ExpenseCategory::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $categories = $query->latest()->paginate(10)->withQueryString();

        return view('expense_categories.index', compact('categories'));
    }

    public function create()
    {
        return view('expense_categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories,name,NULL,id,deleted_at,NULL',
            'description' => 'nullable|string',
        ]);

        ExpenseCategory::create($request->only('name', 'description'));

        return redirect()->route('expense-categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(ExpenseCategory $expenseCategory)
    {
        return view('expense_categories.edit', compact('expenseCategory'));
    }

    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories,name,' . $expenseCategory->id . ',id,deleted_at,NULL',
            'description' => 'nullable|string',
        ]);

        $expenseCategory->update($request->only('name', 'description'));

        return redirect()->route('expense-categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(ExpenseCategory $expenseCategory)
    {
        // Check if used in expenses (optional, but good practice)
        if ($expenseCategory->expenses()->exists()) {
            // Soft delete handled by model trait, but if we want to prevent deletion:
            // return back()->with('error', 'Category is used in expenses. Cannot delete.');
            // Requirement says: "Prevent delete if already used (or soft delete)".
            // Since soft usage is enabled, we can allow destroy which will soft delete.
        }

        $expenseCategory->delete();
        return redirect()->route('expense-categories.index')->with('success', 'Category deleted successfully.');
    }
}
