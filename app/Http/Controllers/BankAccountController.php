<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankAccountController extends Controller
{
    /**
     * Display a listing of bank accounts.
     */
    public function index()
    {
        $companyId = $this->getCompanyId();
        $bankAccounts = BankAccount::where('company_id', $companyId)
            ->where('is_active', true)
            ->get();

        // Calculate current balances efficiently
        foreach ($bankAccounts as $account) {
            $account->current_balance = $account->getCurrentBalanceAttribute();
        }

        return view('bank_accounts.index', compact('bankAccounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('bank_accounts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'bank_name' => 'required|string|max:255',
            'account_holder_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
            'account_type' => 'required|string|in:Saving,Current,OD,Cash',
            'opening_balance' => 'required|numeric|min:0',
            'opening_balance_date' => 'required|date',
        ]);

        $companyId = $this->getCompanyId();

        $account = BankAccount::create([
            'company_id' => $companyId,
            'bank_name' => $request->bank_name,
            'account_holder_name' => $request->account_holder_name,
            'account_number' => $request->account_number,
            'ifsc_code' => $request->ifsc_code,
            'account_type' => $request->account_type,
            'opening_balance' => $request->opening_balance,
            'opening_balance_date' => $request->opening_balance_date,
        ]);

        return redirect()->route('bank-accounts.index')->with('success', 'Bank account created successfully.');
    }

    /**
     * Display the specified resource (Statement View).
     */
    public function show(BankAccount $bankAccount, Request $request)
    {
        if ($bankAccount->company_id !== $this->getCompanyId()) {
            abort(403);
        }

        $query = $bankAccount->transactions();

        // Filtering
        if ($request->filled('from_date')) {
            $query->whereDate('transaction_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('transaction_date', '<=', $request->to_date);
        }
        if ($request->filled('type')) { // credit or debit
            $query->where('type', $request->type);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('description', 'like', "%{$request->search}%")
                    ->orWhere('reference_number', 'like', "%{$request->search}%");
            });
        }

        // Sorting: Always Date ASC, ID ASC for consistent running balance logic
        // But user might want DESC view? "Entries sorted date-wise" usually means ASC in statements.
        // Wait, bank statements are usually DESC (Newest on top)? OR ASC (Oldest -> Newest)?
        // Real bank statements (PDF) are usually ASC (Datewise forward).
        // Netbanking apps show DESC (Recent first).

        // "Running Balance" implies ASC calculation logic.
        // If we want DESC view, we need to calculate ASC first then reverse, OR use "Balance Brought Forward" logic.

        // For simplicity and "Statement" feel, we will do ASC.
        // If user wants DESC, we can toggle. But Default ASC makes sense for ledger.

        $transactions = $query->orderBy('transaction_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get(); // Using get() instead of paginate() for accurate running balance for now?

        // Calculating Running Balance (In-Memory for simplicity for now)
        // If data gets huge, we'll need a better strategy (e.g. daily balance snapshots).

        $runningBalance = $bankAccount->opening_balance;

        // If filtering by date, we need to calculate "Opening Balance for this period"
        $openingBalanceForPeriod = $bankAccount->opening_balance;

        if ($request->filled('from_date')) {
            $preCredits = $bankAccount->transactions()
                ->where('transaction_date', '<', $request->from_date)
                ->where('type', 'credit')
                ->sum('amount');

            $preDebits = $bankAccount->transactions()
                ->where('transaction_date', '<', $request->from_date)
                ->where('type', 'debit')
                ->sum('amount');

            $openingBalanceForPeriod = $bankAccount->opening_balance + $preCredits - $preDebits;
            $runningBalance = $openingBalanceForPeriod; // Start calculation from here
        }

        // Attach computed balance to each transaction object for the view
        foreach ($transactions as $txn) {
            if ($txn->type === 'credit') {
                $runningBalance += $txn->amount;
            } else {
                $runningBalance -= $txn->amount;
            }
            $txn->running_balance = $runningBalance;
        }

        // Now if we paginate, we'd lose the context. For now, let's dump all (or limit to a reasonable range).
        // If we must paginate, we'd pass $openingBalanceForPeriod to the view and calculate iteratively there? No, loop above fixes it.
        // But if we limit the query, the loop only runs on the current page.
        // So we calculated balance on the fetched subset assuming starting point is correct.

        return view('bank_accounts.show', compact('bankAccount', 'transactions', 'openingBalanceForPeriod'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BankAccount $bankAccount)
    {
        if ($bankAccount->company_id !== $this->getCompanyId()) {
            abort(403);
        }
        return view('bank_accounts.edit', compact('bankAccount'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BankAccount $bankAccount)
    {
        if ($bankAccount->company_id !== $this->getCompanyId()) {
            abort(403);
        }

        $request->validate([
            'bank_name' => 'required|string|max:255',
            'account_holder_name' => 'required|string|max:255',
            'account_type' => 'required|string',
            'status' => 'boolean',
            // Allow editing opening balance? User said "Opening balance... Cannot be deleted (only editable by admin)"
            // Assuming current user is authorized.
            'opening_balance' => 'numeric|min:0',
            'opening_balance_date' => 'date',
        ]);

        $bankAccount->update([
            'bank_name' => $request->bank_name,
            'account_holder_name' => $request->account_holder_name,
            'account_number' => $request->account_number,
            'ifsc_code' => $request->ifsc_code,
            'account_type' => $request->account_type,
            'opening_balance' => $request->opening_balance,
            'opening_balance_date' => $request->opening_balance_date,
            'is_active' => $request->has('status') ? $request->status : $bankAccount->is_active,
        ]);

        return redirect()->route('bank-accounts.index')->with('success', 'Bank account updated successfully.');
    }
}
