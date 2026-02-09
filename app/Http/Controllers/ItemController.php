<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $companyId = $this->getCompanyId();
        $query = Item::where('company_id', $companyId);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('hsn_code', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('name')->paginate(10);

        return view('items.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('items.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $companyId = $this->getCompanyId();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('items')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId)
                        ->whereNull('deleted_at');
                })
            ],
            'hsn_code' => ['required', 'string', 'max:50'],
            'rate' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:50'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $validated['company_id'] = $companyId;

        Item::create($validated);

        return redirect()->route('items.index')
            ->with('success', 'Item created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Item $item)
    {
        if ($item->company_id != $this->getCompanyId()) {
            abort(404);
        }
        return view('items.edit', compact('item'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Item $item)
    {
        if ($item->company_id != $this->getCompanyId()) {
            abort(404);
        }

        $companyId = $this->getCompanyId();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('items')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId)
                        ->whereNull('deleted_at');
                })->ignore($item->id)
            ],
            'hsn_code' => ['required', 'string', 'max:50'],
            'rate' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:50'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $item->update($validated);

        return redirect()->route('items.index')
            ->with('success', 'Item updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        if ($item->company_id != $this->getCompanyId()) {
            abort(404);
        }

        $item->delete();

        return redirect()->route('items.index')
            ->with('success', 'Item deleted successfully.');
    }

    /**
     * Search items for autocomplete (API).
     */
    public function search(Request $request)
    {
        $query = $request->get('q');

        if (strlen($query) < 1) {
            return response()->json([]);
        }

        $items = Item::where('company_id', $this->getCompanyId())
            ->where('status', 'active')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('hsn_code', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'hsn_code', 'rate', 'unit']);

        return response()->json($items);
    }
}
