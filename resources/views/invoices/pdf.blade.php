<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        @page { 
            size: A4 portrait; 
            margin-top: 1.5cm;
            margin-bottom: 0.2cm;
            margin-left: 0.5cm;
            margin-right: 0.5cm;
        }
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            line-height: 1.2;
        }
        .container {
            width: 100%;
            border: 2px solid #000;
        }
        
        /* Helpers */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        
        /* Table Layout Helpers */
        .w-100 { width: 100%; border-collapse: collapse; }
        .w-60 { width: 60%; }
        .w-40 { width: 40%; }
        .w-65 { width: 65%; }
        .w-35 { width: 35%; }
        
        .border-bottom { border-bottom: 1px solid #000; }
        .border-right { border-right: 1px solid #000; }
        .border-top { border-top: 1px solid #000; }
        
        /* Header */
        .header {
            text-align: center;
            padding: 5px;
            border-bottom: 1px solid #000;
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
        .company-address { font-size: 10px; }
        .gst-no { font-weight: bold; font-size: 12px; margin-top: 2px; }
        
        .header-corners {
            width: 100%;
            border-bottom: 1px solid #000;
        }
        .header-corners td {
            padding: 2px 5px;
            font-size: 11px;
            font-weight: bold;
        }
        
        /* Info Section */
        .info-table { width: 100%; border-collapse: collapse; border-bottom: 1px solid #000; }
        .info-table td { vertical-align: top; padding: 5px; }
        
        /* Items Table */
        .items-table { width: 100%; border-collapse: collapse; }
        .items-table th, .items-table td {
            border-right: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 3px;
            font-size: 11px;
            vertical-align: middle; /* Vertical Center Logic */
        }
        .items-table th { text-align: center; font-weight: bold; }
        .items-table th:last-child, .items-table td:last-child { border-right: none; }
        
        /* Column Widths */
        .col-ch-no { width: 10%; }
        .col-ch-date { width: 12%; }
        .col-particulars { width: 45%; }
        .col-qty { width: 10%; }
        .col-rate { width: 10%; }
        .col-amount { width: 13%; }
        
        /* Footer */
        .footer-table { width: 100%; border-collapse: collapse; }
        .footer-table td { vertical-align: top; padding: 0; }
        .footer-left { padding: 5px; border-right: 1px solid #000; font-size: 11px; }
        .footer-right { font-size: 11px; }
        
        .tax-table { width: 100%; border-collapse: collapse; }
        .tax-table td { padding: 2px 5px; text-align: right; }
        
        .grand-total {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 5px;
            font-weight: bold;
            text-align: right;
            font-size: 12px;
        }
        .signature { text-align: center; margin-top: 40px; font-weight: bold; }
        
        /* Height Fillers */
        .filler-row td { border-bottom: none; height: auto; }
        .total-row td { font-weight: bold; background-color: #f0f0f0; border-top: 1px solid #000; }
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
    
    <table class="header-corners">
        <tr>
            <td class="text-left">State Code : {{ $company->state_code ?? '24-GJ' }}</td>
            <td class="text-right">Mo : {{ $company->mobile_numbers ?? '' }}</td>
        </tr>
    </table>

    <!-- Info Section -->
    <table class="info-table">
        <tr>
            <td class="w-60 border-right">
                <div class="font-bold" style="margin-bottom: 5px;">M/s.: {{ $invoice->party->name }}</div>
                <div style="margin-left: 30px; margin-bottom: 10px;">
                    {{ $invoice->party->address }}<br>
                    {{ $invoice->party->city ?? 'SURAT' }}
                </div>
                <table class="w-100">
                    <tr>
                        <td><strong>GSTI No:</strong> {{ $invoice->party->gst_number }}</td>
                        <td class="text-right"><strong>State Code:</strong> {{ $invoice->party->gst_number ? substr($invoice->party->gst_number, 0, 2) . '-' . substr($invoice->party->gst_number, 2, 2) : '' }}</td>
                    </tr>
                </table>
            </td>
            <td class="w-40">
                <table class="w-100">
                    <tr>
                        <td style="width: 70px;"><strong>INV NO.</strong></td>
                        <td><strong>{{ $invoice->invoice_number }}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Date :</strong></td>
                        <td>{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Due Day :</strong></td>
                        <td>0</td>
                    </tr>
                    <tr>
                        <td><strong>Due Date :</strong></td>
                        <td>{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

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
        <tbody>
            @php
                $totalMtrs = 0;
                $totalAmount = 0;
            @endphp
            @foreach($invoice->challans as $challan)
                @foreach($challan->items as $item)
                    @php
                        $totalMtrs += $item->quantity;
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
            
            <!-- Filler rows -->
            @for($i = 0; $i < (20 - $invoice->getAllItems()->count()); $i++)
                <tr class="filler-row">
                    <td>&nbsp;</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endfor
            
            <tr class="total-row">
                <td colspan="3" class="text-right">Total</td>
                <td class="text-right">{{ number_format($totalMtrs, 2) }}</td>
                <td></td>
                <td class="text-right">{{ number_format($totalAmount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Footer -->
    <table class="footer-table">
        <tr>
            <td class="w-65 footer-left">
                @if($company && ($company->bank_name || $company->ifsc_code || $company->account_number))
                <div style="margin-bottom: 10px;">
                    <div class="font-bold">Bank Detail</div>
                    <div>Bank Name : {{ $company->bank_name }}</div>
                    <div>IFSC Code : {{ $company->ifsc_code }}</div>
                    <div>A/c No : {{ $company->account_number }}</div>
                </div>
                @endif
                
                @php
                    $f = new NumberFormatter("en", NumberFormatter::SPELLOUT);
                    $words = $f->format($invoice->final_amount);
                @endphp
                <div style="margin-top: 10px; font-weight: bold; font-style: italic;">
                    Rupees {{ ucwords($words) }} Only
                </div>
                
                @if($company && $company->terms_conditions)
                <div style="margin-top: 5px; font-size: 10px;">
                    <div class="font-bold">Terms and Conditions :</div>
                    {!! nl2br(e($company->terms_conditions)) !!}
                </div>
                @endif
            </td>
            <td class="w-35 footer-right">
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
            </td>
        </tr>
    </table>

</div>

</body>
</html>
