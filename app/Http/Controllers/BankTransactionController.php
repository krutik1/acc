<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankTransaction;
use Illuminate\Http\Request;

class BankTransactionController extends Controller
{
    /**
     * Store a newly created transaction in storage.
     */
    public function store(Request $request, BankAccount $bankAccount)
    {
        if ($bankAccount->company_id !== $this->getCompanyId()) {
            abort(403);
        }

        $request->validate([
            'transaction_date' => 'required|date',
            'type' => 'required|in:credit,debit',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string',
            'reference_number' => 'nullable|string',
            'category' => 'nullable|string',
        ]);

        $bankAccount->transactions()->create([
            'transaction_date' => $request->transaction_date,
            'type' => $request->type,
            'amount' => $request->amount,
            'description' => $request->description,
            'reference_number' => $request->reference_number,
            'category' => $request->category,
        ]);

        return redirect()->back()->with('success', 'Entry added successfully.');
    }

    /**
     * Update the specified transaction in storage.
     */
    public function update(Request $request, BankTransaction $bankTransaction)
    {
        if ($bankTransaction->bankAccount->company_id !== $this->getCompanyId()) {
            abort(403);
        }

        // Safety Rule: Cannot edit transactions linked to finalized modules
        if ($bankTransaction->related_id) {
            return redirect()->back()->with('error', 'Cannot edit system-generated transactions. Please edit the source module.');
        }

        $request->validate([
            'transaction_date' => 'required|date',
            'type' => 'required|in:credit,debit',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string',
            'reference_number' => 'nullable|string',
            'category' => 'nullable|string',
        ]);

        $bankTransaction->update([
            'transaction_date' => $request->transaction_date,
            'type' => $request->type,
            'amount' => $request->amount,
            'description' => $request->description,
            'reference_number' => $request->reference_number,
            'category' => $request->category,
        ]);

        return redirect()->back()->with('success', 'Entry updated successfully.');
    }

    /**
     * Remove the specified transaction from storage.
     */
    public function destroy(BankTransaction $bankTransaction)
    {
        if ($bankTransaction->bankAccount->company_id !== $this->getCompanyId()) {
            abort(403);
        }

        // Safety Rule
        if ($bankTransaction->related_id) {
            return redirect()->back()->with('error', 'Cannot delete system-generated transactions.');
        }

        $bankTransaction->delete();

        return redirect()->back()->with('success', 'Entry deleted successfully.');
    }
}
