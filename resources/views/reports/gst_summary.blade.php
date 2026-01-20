@extends('layouts.main')

@section('title', 'GST Sales Report')

@push('styles')
<style>
    /* Lightweight Print Tweaks to ensure Table fits */
    @media print {
        @page { size: landscape; margin: 0.5cm; }
        body { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        .card { border: none !important; box-shadow: none !important; }
        .table th { background-color: #f8f9fa !important; color: #000 !important; }
    }
</style>
@endpush

@section('content')
<div class="row no-print mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header border-0 pb-0">
                <span class="border-start border-4 border-primary ps-2">GST Sales Report</span>
            </div>
            <div class="card-body">
                <form action="{{ route('reports.gst-summary') }}" method="GET" class="row align-items-end g-3">
                    <div class="col-md-3">
                        <label class="form-label">Party</label>
                        <select name="party_id" class="form-select select2">
                            <option value="">All Parties</option>
                            @foreach($parties as $party)
                                <option value="{{ $party->id }}" {{ isset($selectedPartyId) && $selectedPartyId == $party->id ? 'selected' : '' }}>
                                    {{ $party->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date Range</label>
                        <div class="input-group">
                            <input type="date" class="form-control" name="from_date" value="{{ $fromDate }}">
                            <span class="input-group-text bg-light">to</span>
                            <input type="date" class="form-control" name="to_date" value="{{ $toDate }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-filter me-1"></i>Filter
                        </button>
                    </div>
                    <div class="col-md-2 ms-auto">
                        <button type="button" onclick="window.print()" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-printer me-1"></i>Print
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <!-- Print Header -->
        <div class="report-header d-none d-print-block">
            <div class="h3 fw-bold text-primary">{{ $company->name }}</div>
            <div class="fw-bold mb-2">GSTIN : {{ $company->gst_number }} &nbsp;|&nbsp; STATE CODE : {{ substr($company->state_code ?? '24', 0, 2) }}</div>
            <div class="mb-3 text-muted">
                Sales Register ({{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($toDate)->format('d M Y') }})
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-bordered mb-0 align-middle">
                <thead class="text-center align-middle">
                    <tr>
                        <th style="width: 5%;">Bill No</th>
                        <th style="width: 8%;">Date</th>
                        <th style="width: 20%;">Account Name</th>
                        <th style="width: 12%;">GSTIN</th>
                        <th style="width: 5%;">State</th>
                        <th style="width: 10%;">Taxable</th>
                        <th style="width: 8%;">CGST</th>
                        <th style="width: 8%;">SGST</th>
                        <th style="width: 8%;">IGST</th>
                        <th style="width: 6%;">Diff</th>
                        <th style="width: 10%;">Net</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $sumTaxable = 0; $sumCGST = 0; $sumSGST = 0; $sumIGST = 0; $sumDiff = 0; $sumNet = 0;
                    @endphp

                    @forelse($invoices as $inv)
                        @php
                            $sumTaxable += $inv->taxable_value;
                            $sumCGST += $inv->cgst;
                            $sumSGST += $inv->sgst;
                            $sumIGST += $inv->igst;
                            $sumDiff += $inv->diff;
                            $sumNet += $inv->net_amount;
                            $displayState = $inv->state_code . '-IN'; 
                            $states = ['24' => '24-GJ', '27' => '27-MH', '08' => '08-RJ', '23' => '23-MP'];
                            $displayState = $states[$inv->state_code] ?? $inv->state_code;
                        @endphp
                        <tr>
                            <td class="text-center fw-bold">{{ $inv->bill_no }}</td>
                            <td class="text-center">{{ $inv->date->format('d/m/y') }}</td>
                            <td>{{ Str::limit($inv->party_name, 25) }}</td>
                            <td class="text-center small">{{ $inv->gstin }}</td>
                            <td class="text-center small">{{ $displayState }}</td>
                            <td class="text-end">{{ number_format($inv->taxable_value, 2) }}</td>
                            <td class="text-end">{{ number_format($inv->cgst, 2) }}</td>
                            <td class="text-end">{{ number_format($inv->sgst, 2) }}</td>
                            <td class="text-end">{{ number_format($inv->igst, 2) }}</td>
                            <td class="text-end">{{ number_format($inv->diff, 2) }}</td>
                            <td class="text-end fw-bold text-primary">{{ number_format($inv->net_amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-4 text-muted">No records found for this period.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-light fw-bold text-center">
                    <tr>
                        <td colspan="5" class="text-end">Total</td>
                        <td class="text-end">{{ number_format($sumTaxable, 2) }}</td>
                        <td class="text-end">{{ number_format($sumCGST, 2) }}</td>
                        <td class="text-end">{{ number_format($sumSGST, 2) }}</td>
                        <td class="text-end">{{ number_format($sumIGST, 2) }}</td>
                        <td class="text-end">{{ number_format($sumDiff, 2) }}</td>
                        <td class="text-end text-primary">{{ number_format($sumNet, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div class="row mt-3 no-print">
            <div class="col-12 text-muted small">
                <i class="bi bi-info-circle me-1"></i> Diff represents rounding adjustments applied to final net amount.
            </div>
        </div>
    </div>
</div>
@endsection
