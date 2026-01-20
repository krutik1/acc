<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvoiceRequest;
use App\Models\Challan;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Party;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices.
     */
    public function index(Request $request)
    {
        $companyId = $this->getCompanyId();
        $query = Invoice::with('party')->where('company_id', $companyId);

        // Filter by party
        if ($request->filled('party_id')) {
            $query->where('party_id', $request->party_id);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('invoice_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('invoice_date', '<=', $request->to_date);
        }

        // Search by invoice number
        if ($request->filled('search')) {
            $query->where('invoice_number', 'like', "%{$request->search}%");
        }

        // Filter by amount range
        if ($request->filled('min_amount')) {
            $query->where('final_amount', '>=', $request->min_amount);
        }
        if ($request->filled('max_amount')) {
            $query->where('final_amount', '<=', $request->max_amount);
        }

        $invoices = $query->orderBy('invoice_date', 'desc')->paginate(10);
        $parties = Party::where('company_id', $companyId)->orderBy('name')->get();

        return view('invoices.index', compact('invoices', 'parties'));
    }

    /**
     * Show the form for creating a new invoice.
     */
    public function create()
    {
        $companyId = $this->getCompanyId();
        $parties = Party::where(function($q) use ($companyId) {
            $q->where('company_id', $companyId)
              ->orWhereNull('company_id');
        })->orderBy('name')->get();
        $invoiceNumber = Invoice::generateInvoiceNumber($companyId);

        return view('invoices.create', compact('parties', 'invoiceNumber'));
    }

    /**
     * Store a newly created invoice in storage.
     */
    public function store(InvoiceRequest $request)
    {
        DB::beginTransaction();

        try {
            $companyId = $this->getCompanyId();
            // Validate that selected challans belong to the party and company (allow both invoiced and non-invoiced)
            $challans = Challan::whereIn('id', $request->challan_ids)
                ->where('party_id', $request->party_id)
                ->where(function($q) use ($companyId) {
                    $q->where('company_id', $companyId)
                      ->orWhereNull('company_id');
                })
                ->get();

            if ($challans->count() !== count($request->challan_ids)) {
                return back()
                    ->withInput()
                    ->with('error', 'One or more selected challans are invalid.');
            }

            // Start Logic: Check if we should update an existing invoice or create a new one

            // 1. Identify which invoices the selected challans currently belong to
            $existingInvoiceIds = [];
             foreach ($request->challan_ids as $challanId) {
                $challan = Challan::find($challanId);
                if ($challan) {
                     // Get all invoices containing this challan
                    $currentInvoices = $challan->invoices()->get();
                    foreach ($currentInvoices as $currInvoice) {
                        $existingInvoiceIds[$currInvoice->id] = $currInvoice->id;
                    }
                }
            }

            $uniqueExistingInvoiceIds = array_values($existingInvoiceIds);
            $targetInvoiceToUpdate = null;

            // 2. Logic to decide: Update Existing or Create New
            // Case A: specific existing invoice is being re-invoiced (all selected challans belong to SAME invoice, or are new)
            // Actually, safe logic: If EXACTLY ONE existing invoice is found among linked challans, update THAT invoice.
            // If multiple existing invoices are involved (merging), we deleting them and creating NEW (current behavior).
            if (count($uniqueExistingInvoiceIds) === 1) {
                $targetInvoiceToUpdate = Invoice::find($uniqueExistingInvoiceIds[0]);
            }

            if ($targetInvoiceToUpdate) {
                // UPDATE EXISTING INVOICE
                
                 // Calculate subtotal from challans (server-side)
                $subtotal = $challans->sum('subtotal');

                // Calculate all amounts
                $amounts = Invoice::calculateAmounts(
                    $subtotal,
                    $request->gst_percent ?? 0,
                    $request->tds_percent ?? 0,
                    $request->discount_type ?? 'fixed',
                    $request->discount_value ?? 0
                );
                
                // Update the invoice
                $targetInvoiceToUpdate->update([
                    'party_id' => $request->party_id,
                    'invoice_date' => $request->invoice_date,
                    'subtotal' => $amounts['subtotal'],
                    'gst_percent' => $request->gst_percent ?? 0,
                    'gst_amount' => $amounts['gst_amount'],
                    'tds_percent' => $request->tds_percent ?? 0,
                    'tds_amount' => $amounts['tds_amount'],
                    'discount_type' => $request->discount_type ?? 'fixed',
                    'discount_value' => $request->discount_value ?? 0,
                    'discount_amount' => $amounts['discount_amount'],
                    'final_amount' => $amounts['final_amount'],
                    'notes' => $request->notes,
                ]);

                 // Sync challans (this handles adding new ones and keeping old ones)
                $targetInvoiceToUpdate->challans()->sync($request->challan_ids);
                
                // Mark all these as invoiced
                Challan::whereIn('id', $request->challan_ids)->update(['is_invoiced' => true]);

                DB::commit();

                return redirect()
                    ->route('invoices.show', $targetInvoiceToUpdate)
                    ->with('success', 'Invoice updated successfully.');

            }

            // DEFAULT BEHAVIOR: CREATE NEW INVOICE (and delete old ones if any)
                
                // Delete old invoices that contain any of these challans
                // If a challan is being re-invoiced, delete the old invoice(s) that contained it
                foreach ($request->challan_ids as $challanId) {
                    $challan = Challan::find($challanId);
                    if ($challan) {
                        // Get all invoices containing this challan
                        $oldInvoices = $challan->invoices()->get();
                        foreach ($oldInvoices as $oldInvoice) {
                            // If the old invoice only contains this one challan, delete the entire invoice
                            if ($oldInvoice->challans()->count() === 1) {
                                $oldInvoice->challans()->detach();
                                $oldInvoice->delete();
                            } else {
                                // Otherwise, just detach this challan from the old invoice
                                $oldInvoice->challans()->detach($challanId);
                                // Update is_invoiced status if no other invoices contain this challan
                                if ($challan->invoices()->count() === 0) {
                                    $challan->update(['is_invoiced' => false]);
                                }
                            }
                        }
                    }
                }

                // Calculate subtotal from challans (server-side)
                $subtotal = $challans->sum('subtotal');

            // Calculate all amounts
            $amounts = Invoice::calculateAmounts(
                $subtotal,
                $request->gst_percent ?? 0,
                $request->tds_percent ?? 0,
                $request->discount_type ?? 'fixed',
                $request->discount_value ?? 0
            );

            // Create invoice
            $invoice = Invoice::create([
                'company_id' => $companyId,
                'party_id' => $request->party_id,
                'invoice_number' => Invoice::generateInvoiceNumber($companyId),
                'invoice_date' => $request->invoice_date,
                'subtotal' => $amounts['subtotal'],
                'gst_percent' => $request->gst_percent ?? 0,
                'gst_amount' => $amounts['gst_amount'],
                'tds_percent' => $request->tds_percent ?? 0,
                'tds_amount' => $amounts['tds_amount'],
                'discount_type' => $request->discount_type ?? 'fixed',
                'discount_value' => $request->discount_value ?? 0,
                'discount_amount' => $amounts['discount_amount'],
                'final_amount' => $amounts['final_amount'],
                'notes' => $request->notes,
            ]);

            // Attach challans to invoice
            $invoice->challans()->attach($request->challan_ids);

            // Mark challans as invoiced
            Challan::whereIn('id', $request->challan_ids)->update(['is_invoiced' => true]);

            DB::commit();

            return redirect()
                ->route('invoices.show', $invoice)
                ->with('success', 'Invoice created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Invoice creation failed: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified invoice.
     */
    public function edit(Invoice $invoice)
    {
        // Ensure invoice belongs to current company
        if ($invoice->company_id != $this->getCompanyId()) {
            abort(404);
        }
        $invoice->load(['party', 'challans']);
        $companyId = $this->getCompanyId();
        $parties = Party::where(function($q) use ($companyId) {
            $q->where('company_id', $companyId)
              ->orWhereNull('company_id');
        })->orderBy('name')->get();
        
        return view('invoices.edit', compact('invoice', 'parties'));
    }

    /**
     * Update the specified invoice in storage.
     */
    public function update(InvoiceRequest $request, Invoice $invoice)
    {
        DB::beginTransaction();

        try {
            $companyId = $this->getCompanyId();
            // Ensure invoice belongs to current company
            if ($invoice->company_id != $companyId) {
                abort(404);
            }
            // Validate that selected challans belong to the party and company
            $challans = Challan::whereIn('id', $request->challan_ids)
                ->where('party_id', $request->party_id)
                ->where(function($q) use ($companyId) {
                    $q->where('company_id', $companyId)
                      ->orWhereNull('company_id');
                })
                ->get();

            if ($challans->count() !== count($request->challan_ids)) {
                return back()
                    ->withInput()
                    ->with('error', 'One or more selected challans are invalid.');
            }

            // Calculate subtotal from challans (server-side)
            $subtotal = $challans->sum('subtotal');

            // Calculate all amounts
            $amounts = Invoice::calculateAmounts(
                $subtotal,
                $request->gst_percent ?? 0,
                $request->tds_percent ?? 0,
                $request->discount_type ?? 'fixed',
                $request->discount_value ?? 0
            );

            // Get old challan IDs before update
            $oldChallanIds = $invoice->challans->pluck('id')->toArray();

            // Update invoice
            $invoice->update([
                'party_id' => $request->party_id,
                'invoice_date' => $request->invoice_date,
                'subtotal' => $amounts['subtotal'],
                'gst_percent' => $request->gst_percent ?? 0,
                'gst_amount' => $amounts['gst_amount'],
                'tds_percent' => $request->tds_percent ?? 0,
                'tds_amount' => $amounts['tds_amount'],
                'discount_type' => $request->discount_type ?? 'fixed',
                'discount_value' => $request->discount_value ?? 0,
                'discount_amount' => $amounts['discount_amount'],
                'final_amount' => $amounts['final_amount'],
                'notes' => $request->notes,
            ]);

            // Sync challans (remove old, add new)
            $invoice->challans()->sync($request->challan_ids);

            // Update is_invoiced status for all challans
            // Mark newly added challans as invoiced
            Challan::whereIn('id', $request->challan_ids)->update(['is_invoiced' => true]);
            
            // Mark removed challans as not invoiced only if they're not in any other invoice
            $removedChallanIds = array_diff($oldChallanIds, $request->challan_ids);
            if (!empty($removedChallanIds)) {
                foreach ($removedChallanIds as $challanId) {
                    $challan = Challan::find($challanId);
                    if ($challan && $challan->invoices()->count() === 0) {
                        $challan->update(['is_invoiced' => false]);
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('invoices.show', $invoice)
                ->with('success', 'Invoice updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to update invoice. Please try again.');
        }
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice)
    {
        // Ensure invoice belongs to current company
        if ($invoice->company_id != $this->getCompanyId()) {
            abort(404);
        }
        $invoice->load(['party', 'challans.items']);
        $company = $invoice->company;
        return view('invoices.show', compact('invoice', 'company'));
    }

    /**
     * Remove the specified invoice from storage.
     */
    public function destroy(Invoice $invoice)
    {
        // Ensure invoice belongs to current company
        if ($invoice->company_id != $this->getCompanyId()) {
            abort(404);
        }

        DB::beginTransaction();

        try {
            // Mark challans as not invoiced
            $invoice->challans()->update(['is_invoiced' => false]);

            // Detach challans
            $invoice->challans()->detach();

            // Delete invoice
            $invoice->delete();

            DB::commit();

            return redirect()
                ->route('invoices.index')
                ->with('success', 'Invoice deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete invoice. Please try again.');
        }
    }

    /**
     * Download invoice as PDF.
     */
    public function downloadPdf(Invoice $invoice)
    {
        // Ensure invoice belongs to current company
        if ($invoice->company_id != $this->getCompanyId()) {
            abort(404);
        }
        $invoice->load(['party', 'challans.items', 'company']);
        $company = $invoice->company;

        $pdf = Pdf::loadView('invoices.pdf', compact('invoice', 'company'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download("Invoice-{$invoice->invoice_number}.pdf");
    }

    /**
     * Print invoice (HTML view).
     */
    public function print(Invoice $invoice)
    {
        // Ensure invoice belongs to current company
        if ($invoice->company_id != $this->getCompanyId()) {
            abort(404);
        }
        $invoice->load(['party', 'challans.items', 'company']);
        $company = $invoice->company;
        return view('invoices.print', compact('invoice', 'company'));
    }

    /**
     * Calculate invoice amounts (AJAX endpoint).
     */
    public function calculate(Request $request)
    {
        $request->validate([
            'challan_ids' => 'required|array|min:1',
            'gst_percent' => 'nullable|numeric|min:0|max:100',
            'tds_percent' => 'nullable|numeric|min:0|max:100',
            'discount_type' => 'required|in:fixed,percentage',
            'discount_value' => 'nullable|numeric|min:0',
        ]);

        $companyId = $this->getCompanyId();

        // Get challans and calculate subtotal (allow both invoiced and non-invoiced)
        $challans = Challan::whereIn('id', $request->challan_ids)
            ->where(function($q) use ($companyId) {
                $q->where('company_id', $companyId)
                  ->orWhereNull('company_id');
            })
            ->get();

        $subtotal = $challans->sum('subtotal');

        // Calculate amounts
        $amounts = Invoice::calculateAmounts(
            $subtotal,
            $request->gst_percent ?? 0,
            $request->tds_percent ?? 0,
            $request->discount_type ?? 'fixed',
            $request->discount_value ?? 0
        );

        return response()->json([
            'subtotal' => number_format($amounts['subtotal'], 2),
            'gst_amount' => number_format($amounts['gst_amount'], 2),
            'tds_amount' => number_format($amounts['tds_amount'], 2),
            'discount_amount' => number_format($amounts['discount_amount'], 2),
            'final_amount' => number_format($amounts['final_amount'], 2),
            'subtotal_raw' => $amounts['subtotal'],
            'gst_amount_raw' => $amounts['gst_amount'],
            'tds_amount_raw' => $amounts['tds_amount'],
            'discount_amount_raw' => $amounts['discount_amount'],
            'final_amount_raw' => $amounts['final_amount'],
        ]);
    }
}
