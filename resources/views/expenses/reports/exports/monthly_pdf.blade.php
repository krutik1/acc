<!DOCTYPE html>
<html>
<head>
    <title>Monthly Expenses Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 20px; }
        .text-right { text-align: right; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #777; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $company->name }}</h2>
        <h3>Monthly Expenses Report - {{ $year }}</h3>
    </div>

    <table>
        <thead>
            <tr>
                <th>Month</th>
                <th class="text-right">Total Amount</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @for($m = 1; $m <= 12; $m++)
                @php 
                    $monthName = \Carbon\Carbon::create()->month($m)->format('F');
                    $amount = 0;
                    // Find matching month in data collection
                    $monthData = $data->firstWhere('month', $m);
                    if ($monthData) {
                        $amount = $monthData->total_amount;
                    }
                    $total += $amount;
                @endphp
                @if($amount > 0)
                <tr>
                    <td>{{ $monthName }}</td>
                    <td class="text-right">Rs. {{ number_format($amount, 2) }}</td>
                </tr>
                @endif
            @endfor
        </tbody>
        <tfoot>
            <tr>
                <th>Total</th>
                <th class="text-right">Rs. {{ number_format($total, 2) }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Generated on {{ now()->format('d M Y, h:i A') }}
    </div>
</body>
</html>
