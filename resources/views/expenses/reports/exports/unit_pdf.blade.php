<!DOCTYPE html>
<html>
<head>
    <title>Unit-wise Expenses Report</title>
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
        <h3>Unit-wise Expenses Report</h3>
        <p>From: {{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }} To: {{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Unit Name</th>
                <th class="text-right">Total Amount</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach($unitExpenses as $unit)
                @php 
                    $amount = $unit->expenses_sum_amount ?? 0;
                    $total += $amount;
                @endphp
                @if($amount > 0)
                <tr>
                    <td>{{ $unit->name }}</td>
                    <td class="text-right">Rs. {{ number_format($amount, 2) }}</td>
                </tr>
                @endif
            @endforeach
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
