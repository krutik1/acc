<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SuggestionController extends Controller
{
    /**
     * Search for suggestions in specified table and column.
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        $type = $request->get('type');
        
        if (strlen($query) < 1) {
            return response()->json([]);
        }

        // Whitelist mapping for security
        $allowed = [
            'challan_item_description' => ['table' => 'challan_items', 'column' => 'description'],
            'payment_notes' => ['table' => 'payments', 'column' => 'notes'],
            'payment_reference' => ['table' => 'payments', 'column' => 'reference_number'],
            'invoice_notes' => ['table' => 'invoices', 'column' => 'notes'],
            'company_name' => ['table' => 'companies', 'column' => 'name'],
        ];

        if (!array_key_exists($type, $allowed)) {
            return response()->json([], 400);
        }

        $config = $allowed[$type];
        $table = $config['table'];
        $column = $config['column'];
        
        $results = DB::table($table)
            ->where($table . '.' . $column, 'LIKE', "%{$query}%")
            ->distinct()
            ->limit(10);
            
        // Add company scope
        if (Schema::hasColumn($table, 'company_id')) {
             $results->where($table . '.company_id', $this->getCompanyId());
        } elseif ($table === 'challan_items') {
             $results->join('challans', 'challan_items.challan_id', '=', 'challans.id')
                     ->where('challans.company_id', $this->getCompanyId());
        }

        // We specifically select the column to avoid ambiguous column errors if joining
        $suggestions = $results->pluck($table . '.' . $column);

        return response()->json($suggestions);
    }
}
