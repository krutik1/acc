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
        $query = Payment::with(['party', 'invoices'])->where('company_id', $companyId);

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
            $query->where(function ($q) use ($request) {
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

        $payments = $query->orderBy('payment_date', 'desc')->orderBy('id', 'desc')->paginate(10);
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
            'invoices' => 'nullable|array',
            'invoices.*.id' => 'nullable|exists:invoices,id',
            'invoices.*.amount' => 'nullable|numeric|min:0',
        ]);


        $companyId = $this->getCompanyId();

        DB::beginTransaction();
        try {
            $payment = Payment::create([
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

            if ($request->filled('invoices')) {
                foreach ($request->invoices as $invoiceData) {
                    if (isset($invoiceData['id']) && isset($invoiceData['amount']) && $invoiceData['amount'] > 0) {
                        $payment->invoices()->attach($invoiceData['id'], [
                            'amount' => $invoiceData['amount']
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('payments.index')
                ->with('success', 'Payment recorded successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to record payment: ' . $e->getMessage());
        }
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

        $payment->load('invoices');
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
            'invoices' => 'nullable|array',
            'invoices.*.id' => 'nullable|exists:invoices,id', // Allow null for unchecked rows
            'invoices.*.amount' => 'nullable|numeric|min:0', // Allow null for unchecked rows
        ]);

        DB::beginTransaction();
        try {
            $payment->update([
                'party_id' => $request->party_id,
                'payment_date' => $request->payment_date,
                'amount' => $request->amount,
                'type' => $request->type,
                'mode' => $request->mode,
                'reference_number' => $request->reference_number,
                'notes' => $request->notes,
            ]);

            $syncData = [];
            if ($request->filled('invoices')) {
                foreach ($request->invoices as $invoiceData) {
                    if (isset($invoiceData['id']) && isset($invoiceData['amount']) && $invoiceData['amount'] > 0) {
                        $syncData[$invoiceData['id']] = ['amount' => $invoiceData['amount']];
                    }
                }
            }
            $payment->invoices()->sync($syncData);

            DB::commit();
            return redirect()->route('payments.index')
                ->with('success', 'Payment updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update payment: ' . $e->getMessage());
        }
    }


    /**
     * Remove the specified payment from storage.
     */
    public function destroy(Payment $payment)
    {
        if ($payment->company_id != $this->getCompanyId()) {
            abort(403);
        }

        DB::beginTransaction();
        try {
            // Detach all invoices first
            $payment->invoices()->detach();

            // Delete the payment record
            $payment->delete();

            DB::commit();
            return redirect()->route('payments.index')
                ->with('success', 'Payment deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete payment: ' . $e->getMessage());
        }
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

    public function getPendingInvoices(Request $request, Party $party)
    {
        if ($party->company_id != $this->getCompanyId()) {
            return response()->json([], 403);
        }

        $paymentId = $request->payment_id;

        // Fetch all invoices for the party, limited to last 2 years to hide "old" records
        $invoices = $party->invoices()
            ->where('invoice_date', '>=', now()->subYears(2))
            ->orderBy('invoice_date', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->filter(function ($invoice) use ($paymentId) {
                // If it's already linked to THIS payment, we MUST show it
                if ($paymentId) {
                    $isLinkedToThis = DB::table('payment_invoices')
                        ->where('payment_id', $paymentId)
                        ->where('invoice_id', $invoice->id)
                        ->exists();
                    if ($isLinkedToThis)
                        return true;
                }

                // NEW "One-Link" RULE: Hide if recorded in ANY payment
                $isLinkedToAny = DB::table('payment_invoices')
                    ->where('invoice_id', $invoice->id)
                    ->exists();

                if ($isLinkedToAny)
                    return false;

                // Otherwise, only show if it has a real balance > 0.009 (to handle float precision)
                $pending = (float) $invoice->pending_amount;
                return $pending > 0.009;
            })
            ->values();



        // Include the pivot amount if payment_id is provided
        if ($paymentId) {
            $payment = Payment::with('invoices')->find($paymentId);
            $invoices = $invoices->map(function ($invoice) use ($payment) {
                $linked = $payment->invoices->where('id', $invoice->id)->first();
                $invoice->setAttribute('allocated_amount', $linked ? $linked->pivot->amount : 0);
                return $invoice;
            });
        } else {
            // For new payments, just set allocated to 0
            $invoices = $invoices->map(function ($invoice) {
                $invoice->setAttribute('allocated_amount', 0);
                return $invoice;
            });
        }

        return response()->json($invoices);
    }



    protected function getCompanyId(): ?int
    {
        return session('selected_company_id');
    }
}
