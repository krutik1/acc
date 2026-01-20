<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - {{ $payment->payment_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ccc;
            padding: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .company-details {
            font-size: 12px;
            color: #555;
        }
        .receipt-title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin: 20px 0;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .info-table {
            width: 100%;
            margin-bottom: 30px;
        }
        .info-table td {
            padding: 5px;
            vertical-align: top;
        }
        .label {
            font-weight: bold;
            width: 150px;
        }
        .amount-box {
            border: 2px solid #333;
            padding: 15px;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0;
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .signature {
            border-top: 1px solid #333;
            padding-top: 5px;
            width: 200px;
            text-align: center;
        }
        @media print {
            body { padding: 0; }
            .container { border: none; padding: 0; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="company-name">{{ $company->name }}</div>
            <div class="company-details">
                {{ $company->address }}<br>
                @if($company->contact_number) Phone: {{ $company->contact_number }} @endif
                @if($company->gst_number) | GSTIN: {{ $company->gst_number }} @endif
            </div>
        </div>

        <div class="receipt-title">PAYMENT {{ $payment->type == 'received' ? 'RECEIPT' : 'VOUCHER' }}</div>

        <table class="info-table">
            <tr>
                <td class="label">Receipt No:</td>
                <td>{{ $payment->payment_number }}</td>
                <td class="label" style="text-align: right;">Date:</td>
                <td style="text-align: right;">{{ $payment->payment_date->format('d/m/Y') }}</td>
            </tr>
        </table>

        <div style="margin-bottom: 20px;">
            <strong>{{ $payment->type == 'received' ? 'Received with thanks from' : 'Paid to' }}:</strong><br>
            <span style="font-size: 16px; margin-left: 20px;">{{ $payment->party->name }}</span>
        </div>

        <div class="amount-box">
            Amount: ₹ {{ number_format($payment->amount, 2) }}
        </div>

        <table class="info-table">
            <tr>
                <td class="label">Payment Mode:</td>
                <td>{{ ucfirst(str_replace('_', ' ', $payment->mode)) }}</td>
            </tr>
            @if($payment->reference_number)
            <tr>
                <td class="label">Reference No:</td>
                <td>{{ $payment->reference_number }}</td>
            </tr>
            @endif
            @if($payment->notes)
            <tr>
                <td class="label">Notes:</td>
                <td>{{ $payment->notes }}</td>
            </tr>
            @endif
        </table>

        <div class="footer">
            <div class="signature">
                Party's Signature
            </div>
            <div class="signature">
                Authorized Signatory
            </div>
        </div>
    </div>
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
