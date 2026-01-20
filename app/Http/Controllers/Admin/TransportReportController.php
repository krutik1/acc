<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TransportReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Trip::query()->with('user');

        // Scope to company
        $query->where('company_id', session('selected_company_id'));

        // Filters
        if ($request->filled('driver_id')) {
            $query->where('user_id', $request->driver_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('trip_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('trip_date', '<=', $request->date_to);
        }

        if ($request->filled('payment_mode')) {
             // Because payment_mode can be on trip OR user, filtering efficiently in DB is hard if we fallback.
             // For strict filtering, we might only allow filtering by EXPLICIT trip mode, or we accept we filter after retrieval (bad for pagination).
             // However, let's filter by the explicit 'payment_mode' column on trips if set.
             // If the requirement implies "Effective Mode", it's complex sql. 
             // Let's assume filtering by Trip Metadata for now, or maybe don't filter by mode if not critical?
             // Requirement says: "Payment mode type". 
             // Let's try to filter where trip.payment_mode = X OR (trip.payment_mode is null AND user.payment_mode = X)
             $mode = $request->payment_mode;
             $query->where(function($q) use ($mode) {
                 $q->where('payment_mode', $mode)
                   ->orWhere(function($sub) use ($mode) {
                       $sub->whereNull('payment_mode')
                           ->whereHas('user', function($u) use ($mode) {
                               $u->where('payment_mode', $mode);
                           });
                   });
             });
        }

        // Clone for Summary
        $summaryQuery = clone $query;
        $summary = [
            'total_trips' => $summaryQuery->count(),
            'total_commission' => $summaryQuery->sum('driver_commission'), // Assuming commission is always calculated and saved
            'total_quantity' => $summaryQuery->sum('quantity'), // This sums differnt units, might be misleading.
            // Better to sum by unit?
        ];
        
        // Quantity by Unit
        $quantityByUnit = (clone $query)
            ->select('unit', DB::raw('sum(quantity) as total_qty'))
            ->groupBy('unit')
            ->pluck('total_qty', 'unit');

        // Export Logic
        if ($request->has('export')) {
             return $this->export($query->get(), $request->export);
        }

        $trips = $query->latest('trip_date')->paginate(20)->withQueryString();
        
        $drivers = User::where('company_id', session('selected_company_id'))
            ->where('role', 'driver')
            ->orderBy('name')
            ->get();

        return view('admin.transport.reports.index', compact('trips', 'drivers', 'summary', 'quantityByUnit'));
    }

    private function export($trips, $format)
    {
        // Simple CSV Export for now
        if ($format === 'csv') {
            $filename = "transport-report-" . date('Y-m-d') . ".csv";
            $handle = fopen('php://temp', 'w+');
            fputcsv($handle, ['Date', 'Driver', 'Pickup', 'Drop', 'Quantity', 'Unit', 'Status', 'Payment Mode', 'Commission']);

            foreach ($trips as $trip) {
                fputcsv($handle, [
                    $trip->trip_date->format('Y-m-d H:i'),
                    $trip->user->name,
                    $trip->pickup_location,
                    $trip->drop_location,
                    $trip->quantity,
                    $trip->unit,
                    $trip->status,
                    $trip->effective_payment_mode,
                    $trip->driver_commission
                ]);
            }

            rewind($handle);
            $content = stream_get_contents($handle);
            fclose($handle);

            return response($content)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', "attachment; filename=\"$filename\"");
        }
        
        return back();
    }
}
