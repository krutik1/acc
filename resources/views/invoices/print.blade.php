<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        @media print {
            @page { 
                size: portrait; 
                margin-top: 1.5cm;
                margin-bottom: 0.2cm;
                margin-left: 0.5cm;
                margin-right: 0.5cm;
            }
            body { -webkit-print-color-adjust: exact; }
        }
        body {
            font-family: 'Times New Roman', Times, serif; /* Closer to the image look */
            font-size: 12px;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 19cm; /* Safe print width */
            margin: 0 auto;
            border: 2px solid #000;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        
        /* Header */
        .header {
            text-align: center;
            padding: 5px;
            border-bottom: 1px solid #000;
            position: relative;
        }
        .tax-invoice-label {
            font-size: 12px;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 5px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .company-address {
            font-size: 10px;
        }
        .gst-no {
            font-weight: bold;
            font-size: 12px;
            margin-top: 2px;
        }
        
        .header-corners {
            display: flex;
            justify-content: space-between;
            padding: 2px 5px;
            font-size: 11px;
            border-bottom: 1px solid #000;
            font-weight: bold;
        }

        /* Party & Invoice Info */
        .info-section {
            display: flex;
            border-bottom: 1px solid #000;
        }
        .party-details {
            width: 60%;
            padding: 5px;
            border-right: 1px solid #000;
        }
        .invoice-details {
            width: 40%;
            padding: 5px;
        }
        .info-row {
            display: flex;
            margin-bottom: 2px;
        }
        .info-label {
            width: 80px;
            font-weight: bold;
        }
        
        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
        }
        .items-table th, .items-table td {
            border-right: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 3px;
            font-size: 11px;
        }
        .items-table th {
            text-align: center;
            font-weight: bold;
            border-bottom: 1px solid #000;
        }
        .items-table th:last-child, .items-table td:last-child {
            border-right: none;
        }
        .items-table td {
            vertical-align: middle;
        }
        
        /* Specific Column Widths */
        .col-ch-no { width: 10%; }
        .col-ch-date { width: 12%; }
        .col-particulars { width: 45%; }
        /* .col-hsn removed */
        .col-qty { width: 10%; }
        .col-rate { width: 10%; }
        .col-amount { width: 13%; }

        /* Total Section in Table */
        .total-row td {
            font-weight: bold;
            background-color: #f0f0f0; /* Optional: light gray bg like some forms */
            border-top: 1px solid #000;
            height: auto;
        }

        /* Footer Section */
        .footer {
            display: flex;
            border-top: 1px solid #000; /* Redundant if table has bottom border, but safe */
        }
        .footer-left {
            width: 65%;
            padding: 5px;
            border-right: 1px solid #000;
            font-size: 11px;
        }
        .footer-right {
            width: 35%;
            padding: 0;
            font-size: 11px;
        }
        
        .bank-details { margin-bottom: 10px; }
        .amount-words { margin-top: 10px; font-weight: bold; font-style: italic; }
        .terms { margin-top: 5px; font-size: 10px; }
        .terms ol { padding-left: 20px; margin: 0; }
        
        .tax-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .tax-table td {
            padding: 2px 5px;
            text-align: right;
        }
        .grand-total {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 5px;
            font-weight: bold;
            text-align: right;
            font-size: 12px;
        }
        .signature {
            text-align: center;
            margin-top: 40px;
            font-weight: bold;
        }
        
        /* Utility to strip height from normal rows */
        .items-body tr td { height: auto; padding-bottom: 2px;} 
        /* Minimum height filler */
        .filler-row td { height: auto; border-bottom: none; }
        
    </style>
</head>
<body>

<div class="container">
    <!-- Header -->
    <div class="header">
        <div class="tax-invoice-label">TAX INVOICE</div>
        <div class="company-name">{{ $company->name ?? 'OM DYEING' }}</div>
        <div class="company-address">{{ $company->address ?? 'Address Line Here' }}</div>
        <div class="gst-no">GSTI No : {{ $company->gst_number ?? '-----------------' }}</div>
    </div>
    
    <div class="header-corners">
        <div>State Code : {{ $company->state_code ?? '24-GJ' }}</div>
        <div>Mo : {{ $company->mobile_numbers ?? '' }}</div>
    </div>

    <!-- Info Section -->
    <div class="info-section">
        <div class="party-details">
            <div style="font-weight: bold; margin-bottom: 5px;">M/s.: {{ $invoice->party->name }}</div>
            <div style="margin-left: 30px; margin-bottom: 10px;">
                {{ $invoice->party->address }}<br>
                {{ $invoice->party->city ?? 'SURAT' }}
            </div>
            
            <div style="display: flex; justify-content: space-between;">
                <div><strong>GSTI No:</strong> {{ $invoice->party->gst_number }}</div>
                <div><strong>State Code:</strong> {{ $invoice->party->gst_number ? substr($invoice->party->gst_number, 0, 2) . '-' . substr($invoice->party->gst_number, 2, 2) : '' }}</div>
            </div>
        </div>
        <div class="invoice-details">
            <div class="info-row">
                <span class="info-label" style="width: 60px;">INV NO.</span>
                <span class="font-bold">{{ $invoice->invoice_number }}</span>
            </div>
            <div class="info-row">
                <span class="info-label" style="width: 60px;">Date :</span>
                <span>{{ $invoice->invoice_date->format('d/m/Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label" style="width: 60px;">Due Day :</span>
                <span>0</span>
            </div>
            <div class="info-row">
                <span class="info-label" style="width: 60px;">Due Date :</span>
                <span>{{ $invoice->invoice_date->format('d/m/Y') }}</span>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th class="col-ch-no">Ch. No</th>
                <th class="col-ch-date">Ch. Date</th>
                <th class="col-particulars">PARTICULARS</th>
                <th class="col-qty">Pcs</th>
                <th class="col-rate">RATE</th>
                <th class="col-amount">AMOUNT</th>
            </tr>
        </thead>
        <tbody class="items-body">
            @php
                $totalTaka = 0;
                $totalMtrs = 0;
                $totalAmount = 0;
            @endphp
            @foreach($invoice->challans as $challan)
                @foreach($challan->items as $item)
                    @php
                        $totalTaka += $item->quantity;
                        $totalMtrs += $item->quantity; // Should ideally be a different field if available
                        $totalAmount += $item->amount;
                    @endphp
                        <tr>
                        <td class="text-center">{{ $challan->challan_number }}</td>
                        <td class="text-center">{{ $challan->challan_date->format('d/m/y') }}</td>
                        <td>{{ $item->description }}</td>
                        <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                        <td class="text-right">{{ number_format($item->rate, 2) }}</td>
                        <td class="text-right">{{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach
            @endforeach
            
            <!-- Filler rows to maintain height if needed, OR just let CSS handle min-height -->
            @for($i = 0; $i < (30 - $invoice->getAllItems()->count()); $i++)
                <tr style="border-bottom: none;">
                    <td style="border-bottom: none;">&nbsp;</td>
                    <td style="border-bottom: none;"></td>
                    <td style="border-bottom: none;"></td>
                    <td style="border-bottom: none;"></td>
                    <td style="border-bottom: none;"></td>
                    <td style="border-bottom: none;"></td>
                </tr>
            @endfor
            
            <!-- Total Row -->
            <tr class="total-row">
                <td colspan="3" class="text-right">Total</td>
                <td class="text-right">{{ number_format($totalMtrs, 2) }}</td>
                <td></td>
                <td class="text-right">{{ number_format($totalAmount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <div class="footer-left">
            @if($company && ($company->bank_name || $company->ifsc_code || $company->account_number))
            <div class="bank-details">
                <div class="font-bold">Bank Detail</div>
                <div>Bank Name : {{ $company->bank_name }}</div>
                <div>IFSC Code : {{ $company->ifsc_code }}</div>
                <div>A/c No : {{ $company->account_number }}</div>
            </div>
            @endif
            
            @php
                // Number to words logic (simplified)
                $f = new NumberFormatter("en", NumberFormatter::SPELLOUT);
                $words = $f->format($invoice->final_amount);
            @endphp
            <div class="amount-words">
                Rupees {{ ucwords($words) }} Only
            </div>
            
            @if($company && $company->terms_conditions)
            <div class="terms">
                <div class="font-bold">Terms and Conditions :</div>
                {!! nl2br(e($company->terms_conditions)) !!}
            </div>
            @endif
        </div>
        
        <div class="footer-right">
            @php
                $discountedSubtotal = $invoice->subtotal - $invoice->discount_amount;
                $cgstAmount = $invoice->gst_amount / 2;
                $sgstAmount = $invoice->gst_amount / 2;
                $rounding = $invoice->final_amount - ($discountedSubtotal + $invoice->gst_amount - $invoice->tds_amount);
            @endphp
            
            <table class="tax-table">
                @if($invoice->discount_amount > 0)
                <tr>
                    <td>Discount {{ $invoice->discount_type == 'percentage' ? '('.$invoice->discount_value.'%)' : '' }}</td>
                    <td></td>
                    <td>- {{ number_format($invoice->discount_amount, 2) }}</td>
                </tr>
                @endif
                <tr>
                    <td>CGST</td>
                    <td>{{ number_format($invoice->gst_percent / 2, 2) }} %</td>
                    <td>{{ number_format($cgstAmount, 2) }}</td>
                </tr>
                <tr>
                    <td>SGST</td>
                    <td>{{ number_format($invoice->gst_percent / 2, 2) }} %</td>
                    <td>{{ number_format($sgstAmount, 2) }}</td>
                </tr>
                <tr>
                    <td>Rounding</td>
                    <td>{{ number_format(0, 2) }} %</td>
                    <td>{{ number_format($rounding, 2) }}</td>
                </tr>
            </table>
            
            <div class="grand-total">
                Total Amount : {{ number_format($invoice->final_amount, 2) }}
            </div>
            
            <div class="signature">
                FOR {{ strtoupper($company->name ?? 'OM DYEING') }}
            </div>
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
