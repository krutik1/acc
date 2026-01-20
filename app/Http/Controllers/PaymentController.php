<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Party;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments.
     */
    public function index(Request $request)
    {
        $companyId = $this->getCompanyId();
        $query = Payment::with('party')->where('company_id', $companyId);

        // Filter by party
        if ($request->filled('party_id')) {
            $query->where('party_id', $request->party_id);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('payment_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('payment_date', '<=', $request->to_date);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Search by payment number or reference
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('payment_number', 'like', "%{$request->search}%")
                  ->orWhere('reference_number', 'like', "%{$request->search}%");
            });
        }

        // Filter by amount range
        if ($request->filled('min_amount')) {
            $query->where('amount', '>=', $request->min_amount);
        }
        if ($request->filled('max_amount')) {
            $query->where('amount', '<=', $request->max_amount);
        }

        $payments = $query->orderBy('payment_date', 'desc')->paginate(10);
        $parties = Party::where('company_id', $companyId)->orderBy('name')->get();

        return view('payments.index', compact('payments', 'parties'));
    }

    /**
     * Show the form for creating a new payment.
     */
    public function create()
    {
        $companyId = $this->getCompanyId();
        $parties = Party::where('company_id', $companyId)->orderBy('name')->get();
        $paymentNumber = Payment::generatePaymentNumber($companyId);

        return view('payments.create', compact('parties', 'paymentNumber'));
    }

    /**
     * Store a newly created payment in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'party_id' => 'required|exists:parties,id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:received,sent',
            'mode' => 'required|in:cash,cheque,bank_transfer,upi,other',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $companyId = $this->getCompanyId();

        Payment::create([
            'company_id' => $companyId,
            'party_id' => $request->party_id,
            'payment_number' => Payment::generatePaymentNumber($companyId),
            'payment_date' => $request->payment_date,
            'amount' => $request->amount,
            'type' => $request->type,
            'mode' => $request->mode,
            'reference_number' => $request->reference_number,
            'notes' => $request->notes,
        ]);

        return redirect()->route('payments.index')
            ->with('success', 'Payment recorded successfully.');
    }

    /**
     * Display the specified payment.
     */
    public function show(Payment $payment)
    {
        if ($payment->company_id != $this->getCompanyId()) {
            abort(404);
        }
        $payment->load('party');
        return view('payments.show', compact('payment'));
    }

    /**
     * Show the form for editing the specified payment.
     */
    public function edit(Payment $payment)
    {
        if ($payment->company_id != $this->getCompanyId()) {
            abort(404);
        }
        
        $companyId = $this->getCompanyId();
        $parties = Party::where('company_id', $companyId)->orderBy('name')->get();

        return view('payments.edit', compact('payment', 'parties'));
    }

    /**
     * Update the specified payment in storage.
     */
    public function update(Request $request, Payment $payment)
    {
        if ($payment->company_id != $this->getCompanyId()) {
            abort(404);
        }

        $request->validate([
            'party_id' => 'required|exists:parties,id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:received,sent',
            'mode' => 'required|in:cash,cheque,bank_transfer,upi,other',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $payment->update([
            'party_id' => $request->party_id,
            'payment_date' => $request->payment_date,
            'amount' => $request->amount,
            'type' => $request->type,
            'mode' => $request->mode,
            'reference_number' => $request->reference_number,
            'notes' => $request->notes,
        ]);

        return redirect()->route('payments.index')
            ->with('success', 'Payment updated successfully.');
    }

    /**
     * Remove the specified payment from storage.
     */
    public function destroy(Payment $payment)
    {
        if ($payment->company_id != $this->getCompanyId()) {
            abort(404);
        }

        $payment->delete();

        return redirect()->route('payments.index')
            ->with('success', 'Payment deleted successfully.');
    }

    /**
     * Print payment receipt.
     */
    public function print(Payment $payment)
    {
        if ($payment->company_id != $this->getCompanyId()) {
            abort(404);
        }
        $payment->load('party');
        $company = $payment->company;
        
        return view('payments.print', compact('payment', 'company'));
    }

    protected function getCompanyId(): ?int
    {
        return session('selected_company_id');
    }
}
