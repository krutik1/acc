<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Trip;
use Illuminate\Support\Facades\DB;

class DriverReportController extends Controller
{
    public function index(Request $request)
    {
        $query = auth()->user()->trips();

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('trip_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('trip_date', '<=', $request->date_to);
        }

        // Summary Statistics (Calculated on filtered data)
        $summaryQuery = clone $query;
        $summary = [
            'total_trips' => $summaryQuery->count(),
            'total_commission' => $summaryQuery->sum('driver_commission'),
        ];
        
        $quantityByUnit = (clone $query)
            ->select('unit', DB::raw('sum(quantity) as total_qty'))
            ->groupBy('unit')
            ->pluck('total_qty', 'unit');

        // Export Logic
        if ($request->has('export') && $request->export == 'csv') {
             return $this->export($query->get());
        }

        $trips = $query->latest('trip_date')->paginate(15)->withQueryString();

        return view('driver.reports.index', compact('trips', 'summary', 'quantityByUnit'));
    }

    private function export($trips)
    {
        $filename = "my-transport-report-" . date('Y-m-d') . ".csv";
        $handle = fopen('php://temp', 'w+');
        fputcsv($handle, ['Date', 'Pickup', 'Drop', 'Quantity', 'Unit', 'Status', 'Payment Mode', 'Earnings']);

        foreach ($trips as $trip) {
            fputcsv($handle, [
                $trip->trip_date->format('Y-m-d H:i'),
                $trip->pickup_location,
                $trip->drop_location,
                $trip->quantity,
                $trip->unit,
                $trip->status,
                $trip->effective_payment_mode == 'trip' ? 'Fixed Trip' : 'PCS Based',
                number_format($trip->driver_commission, 2)
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"$filename\"");
    }
}
