<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChallanRequest;
use App\Models\Challan;
use App\Models\ChallanItem;
use App\Models\Party;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChallanController extends Controller
{
    /**
     * Display a listing of challans.
     */
    public function index(Request $request)
    {
        $companyId = $this->getCompanyId();
        $query = Challan::with('party')->where('company_id', $companyId);

        // Filter by party
        if ($request->filled('party_id')) {
            $query->where('party_id', $request->party_id);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('challan_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('challan_date', '<=', $request->to_date);
        }

        // Filter by invoice status
        if ($request->filled('invoiced')) {
            $query->where('is_invoiced', $request->invoiced === 'yes');
        }

        // Search by challan number
        if ($request->filled('search')) {
            $query->where('challan_number', 'like', "%{$request->search}%");
        }

        // Filter by amount range
        if ($request->filled('min_amount')) {
            $query->where('subtotal', '>=', $request->min_amount);
        }
        if ($request->filled('max_amount')) {
            $query->where('subtotal', '<=', $request->max_amount);
        }

        $challans = $query->orderBy('challan_date', 'desc')->paginate(10);
        $parties = Party::where('company_id', $companyId)->orderBy('name')->get();

        return view('challans.index', compact('challans', 'parties'));
    }

    /**
     * Show the form for creating a new challan.
     */
    public function create()
    {
        $companyId = $this->getCompanyId();
        $parties = Party::where('company_id', $companyId)->orderBy('name')->get();
        $units = ChallanItem::UNITS;
        $challanNumber = Challan::generateChallanNumber($companyId);

        return view('challans.create', compact('parties', 'units', 'challanNumber'));
    }

    /**
     * Store a newly created challan in storage.
     */
    public function store(ChallanRequest $request)
    {
        DB::beginTransaction();
        
        try {
            // Determine challan number - use provided or auto-generate
            $challanNumber = !empty($request->challan_number) 
                ? $request->challan_number 
                : Challan::generateChallanNumber();
            
            // Create challan
            $challan = Challan::create([
                'company_id' => $this->getCompanyId(),
                'party_id' => $request->party_id,
                'challan_number' => $challanNumber,
                'challan_date' => $request->challan_date,
                'subtotal' => 0,
                'is_invoiced' => false,
            ]);

            // Server-side recalculation of item amounts
            $subtotal = 0;
            foreach ($request->items as $itemData) {
                $amount = round($itemData['quantity'] * $itemData['rate'], 2);
                $subtotal += $amount;

                ChallanItem::create([
                    'challan_id' => $challan->id,
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'],
                    'unit' => $itemData['unit'],
                    'rate' => $itemData['rate'],
                    'amount' => $amount,
                ]);
            }

            // Update subtotal
            $challan->update(['subtotal' => $subtotal]);

            DB::commit();

            return redirect()
                ->route('challans.show', $challan)
                ->with('success', 'Challan created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to create challan. Please try again.');
        }
    }

    /**
     * Display the specified challan.
     */
    public function show(Challan $challan)
    {
        // Ensure challan belongs to current company
        if ($challan->company_id != $this->getCompanyId()) {
            abort(404);
        }
        $challan->load(['party', 'items']);
        return view('challans.show', compact('challan'));
    }

    /**
     * Show the form for editing the specified challan.
     */
    public function edit(Challan $challan)
    {
        // Ensure challan belongs to current company
        if ($challan->company_id != $this->getCompanyId()) {
            abort(404);
        }
        $challan->load('items');
        $parties = Party::where('company_id', $this->getCompanyId())->orderBy('name')->get();
        $units = ChallanItem::UNITS;

        return view('challans.edit', compact('challan', 'parties', 'units'));
    }

    /**
     * Update the specified challan in storage.
     */
    public function update(ChallanRequest $request, Challan $challan)
    {
        // Ensure challan belongs to current company
        if ($challan->company_id != $this->getCompanyId()) {
            abort(404);
        }

        DB::beginTransaction();

        try {
            // Determine challan number - use provided or keep existing
            $challanNumber = !empty($request->challan_number) 
                ? $request->challan_number 
                : $challan->challan_number;
            
            // Update challan details
            $challan->update([
                'party_id' => $request->party_id,
                'challan_number' => $challanNumber,
                'challan_date' => $request->challan_date,
            ]);

            // Delete existing items
            $challan->items()->delete();

            // Recreate items with server-side calculation
            $subtotal = 0;
            foreach ($request->items as $itemData) {
                $amount = round($itemData['quantity'] * $itemData['rate'], 2);
                $subtotal += $amount;

                ChallanItem::create([
                    'challan_id' => $challan->id,
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'],
                    'unit' => $itemData['unit'],
                    'rate' => $itemData['rate'],
                    'amount' => $amount,
                ]);
            }

            // Update subtotal
            $challan->update(['subtotal' => $subtotal]);

            DB::commit();

            return redirect()
                ->route('challans.show', $challan)
                ->with('success', 'Challan updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to update challan. Please try again.');
        }
    }

    /**
     * Remove the specified challan from storage.
     */
    public function destroy(Challan $challan)
    {
        // Ensure challan belongs to current company
        if ($challan->company_id != $this->getCompanyId()) {
            abort(404);
        }

        if ($challan->is_invoiced) {
            return redirect()
                ->route('challans.index')
                ->with('error', 'Cannot delete a challan that has been invoiced.');
        }

        $challan->delete();

        return redirect()
            ->route('challans.index')
            ->with('success', 'Challan deleted successfully.');
    }

    /**
     * Get challans for a specific party (AJAX endpoint).
     */
    public function getByParty(Request $request, Party $party)
    {
        // Ensure party belongs to current company
        if ($party->company_id != $this->getCompanyId()) {
            abort(404);
        }

        $companyId = $this->getCompanyId();
        // Get current invoice ID from request to exclude it from "already invoiced" check
        $currentInvoiceId = $request->query('current_invoice_id');

        $challans = $party->challans()
            ->where(function($q) use ($companyId) {
                $q->where('company_id', $companyId)
                  ->orWhereNull('company_id');
            })
            ->with(['items', 'invoices'])
            ->orderBy('challan_date', 'desc')
            ->get()
            ->filter(function ($challan) use ($currentInvoiceId) {
                // 1. If we are editing an invoice, always show challans belonging to THIS invoice
                if ($currentInvoiceId) {
                    $belongsToCurrent = $challan->invoices->contains('id', $currentInvoiceId);
                    if ($belongsToCurrent) {
                        return true;
                    }
                }

                // 2. Check if attached to any OTHER invoice
                // We use the relationship count as the source of truth instead of is_invoiced flag
                $lastInvoice = $challan->invoices()->latest('updated_at')->first();
                
                // If no invoice attached, shows it
                if (!$lastInvoice) {
                    return true;
                }
                
                // If attached, check if edited significantly AFTER the invoice was created
                // We reduce the buffer to 1 second to catch updates that happen after invoicing
                if ($challan->updated_at > $lastInvoice->updated_at->copy()->addSeconds(1)) {
                    return true;
                }
                
                return false;
            })
            ->values() // Reset keys after filter
            ->map(function ($challan) {
                return [
                    'id' => $challan->id,
                    'challan_number' => $challan->challan_number,
                    'challan_date' => $challan->challan_date->format('d/m/Y'),
                    'subtotal' => number_format($challan->subtotal, 2),
                    'subtotal_raw' => $challan->subtotal,
                    'items_count' => $challan->items->count(),
                    'is_invoiced' => $challan->is_invoiced,
                    'items' => $challan->items->map(function ($item) {
                        return [
                            'description' => $item->description,
                            'quantity' => $item->quantity,
                            'unit' => $item->unit,
                            'rate' => number_format($item->rate, 2),
                            'amount' => number_format($item->amount, 2),
                        ];
                    }),
                ];
            });

        return response()->json([
            'party' => [
                'id' => $party->id,
                'name' => $party->name,
                'address' => $party->address,
                'contact_number' => $party->contact_number,
                'gst_number' => $party->gst_number,
            ],
            'challans' => $challans,
        ]);
    }

    /**
     * Check if a challan number already exists (AJAX endpoint).
     */
    public function checkDuplicate(Request $request)
    {
        $request->validate([
            'challan_number' => ['required', 'string'],
            'challan_id' => ['nullable', 'exists:challans,id'],
        ]);

        $query = Challan::where('challan_number', $request->challan_number)
            ->where('company_id', $this->getCompanyId());
        
        // Exclude current challan if editing
        if ($request->challan_id) {
            $query->where('id', '!=', $request->challan_id);
        }

        $exists = $query->exists();

        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'This challan number already exists.' : '',
        ]);
    }
}
