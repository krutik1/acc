<?php

namespace App\Http\Controllers;

use App\Http\Requests\PartyRequest;
use App\Models\Party;
use Illuminate\Http\Request;

class PartyController extends Controller
{
    /**
     * Display a listing of parties.
     */
    public function index(Request $request)
    {
        $query = Party::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact_number', 'like', "%{$search}%")
                  ->orWhere('gst_number', 'like', "%{$search}%");
            });
        }

        // Filter by company_id (allow current company or null for old data)
        $companyId = $this->getCompanyId();
        $query->where(function($q) use ($companyId) {
            $q->where('company_id', $companyId)
              ->orWhereNull('company_id');
        });

        $parties = $query->orderBy('name')->paginate(10);

        return view('parties.index', compact('parties'));
    }

    /**
     * Show the form for creating a new party.
     */
    public function create()
    {
        return view('parties.create');
    }

    /**
     * Store a newly created party in storage.
     */
    public function store(PartyRequest $request)
    {
        $data = $request->validated();
        $data['company_id'] = $this->getCompanyId();
        Party::create($data);

        return redirect()
            ->route('parties.index')
            ->with('success', 'Party created successfully.');
    }

    /**
     * Display the specified party.
     */
    public function show(Party $party)
    {
        // Ensure party belongs to current company or has no company
        if ($party->company_id && $party->company_id != $this->getCompanyId()) {
            abort(404);
        }

        $party->load(['challans' => function ($query) {
            $query->latest('challan_date')->limit(10);
        }, 'invoices' => function ($query) {
            $query->latest('invoice_date')->limit(10);
        }]);

        return view('parties.show', compact('party'));
    }

    /**
     * Show the form for editing the specified party.
     */
    public function edit(Party $party)
    {
        // Ensure party belongs to current company or has no company
        if ($party->company_id && $party->company_id != $this->getCompanyId()) {
            abort(404);
        }
        return view('parties.edit', compact('party'));
    }

    /**
     * Update the specified party in storage.
     */
    public function update(PartyRequest $request, Party $party)
    {
        // Ensure party belongs to current company or has no company
        if ($party->company_id && $party->company_id != $this->getCompanyId()) {
            abort(404);
        }
        
        // If party had no company_id, assign it to current company on update
        $data = $request->validated();
        if (!$party->company_id) {
            $data['company_id'] = $this->getCompanyId();
        }
        
        $party->update($data);

        return redirect()
            ->route('parties.index')
            ->with('success', 'Party updated successfully.');
    }

    /**
     * Remove the specified party from storage.
     */
    public function destroy(Party $party)
    {
        // Ensure party belongs to current company or has no company
        if ($party->company_id && $party->company_id != $this->getCompanyId()) {
            abort(404);
        }

        // Check if party has any challans or invoices
        if ($party->challans()->exists() || $party->invoices()->exists()) {
            return redirect()
                ->route('parties.index')
                ->with('error', 'Cannot delete party with existing challans or invoices.');
        }

        $party->delete();

        return redirect()
            ->route('parties.index')
            ->with('success', 'Party deleted successfully.');
    }

    /**
     * Get party details for AJAX requests.
     */
    public function getDetails(Party $party)
    {
        // Ensure party belongs to current company or has no company
        if ($party->company_id && $party->company_id != $this->getCompanyId()) {
            abort(404);
        }

        return response()->json([
            'id' => $party->id,
            'name' => $party->name,
            'address' => $party->address,
            'contact_number' => $party->contact_number,
            'gst_number' => $party->gst_number,
        ]);
    }

    /**
     * Search parties for auto-suggestion.
     */
    public function search(Request $request)
    {
        $search = $request->get('q');
        $companyId = $this->getCompanyId();

        $parties = Party::where('company_id', $companyId)
            ->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'address', 'gst_number', 'contact_number']);

        return response()->json($parties);
    }

    /**
     * Fetch details from GST number (AJAX).
     */
    public function fetchGstDetails(Request $request, \App\Services\GstService $gstService)
    {
        $gst = $request->get('gst_number');
        
        if (!$gst) {
            return response()->json(['valid' => false, 'message' => 'GST number is required.']);
        }

        // 1. Validate Format
        if (!$gstService->validateFormat($gst)) {
            return response()->json(['valid' => false, 'message' => 'Invalid GST number format.']);
        }

        // 2. Fetch Details (Simulated)
        $details = $gstService->getDetails($gst);

        if ($details) {
            return response()->json([
                'valid' => true,
                'data' => $details
            ]);
        }

        return response()->json(['valid' => false, 'message' => 'GST details not found.']);
    }
}
