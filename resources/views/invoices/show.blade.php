@extends('layouts.main')

@section('title', 'Invoice ' . $invoice->invoice_number)

@section('content')
<div class="page-header">
    <h1>Invoice: {{ $invoice->invoice_number }}</h1>
    <div>
        <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-primary me-2">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-danger me-2">
            <i class="bi bi-file-pdf me-1"></i>Download PDF
        </a>
        <a href="{{ route('invoices.print', $invoice) }}" class="btn btn-outline-secondary me-2" target="_blank">
            <i class="bi bi-printer me-1"></i>Print
        </a>
        <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Invoice Details -->
        <div class="card mb-4">
            <div class="card-header">Invoice Details</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <small class="text-muted">Invoice Number</small>
                        <p class="fw-bold fs-5">{{ $invoice->invoice_number }}</p>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Invoice Date</small>
                        <p>{{ $invoice->invoice_date->format('d M Y') }}</p>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Created On</small>
                        <p>{{ $invoice->created_at->format('d M Y, h:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Included Challans -->
        <div class="card mb-4">
            <div class="card-header">Included Challans</div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Challan No.</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->challans as $challan)
                        <tr>
                            <td>
                                <a href="{{ route('challans.show', $challan) }}">{{ $challan->challan_number }}</a>
                            </td>
                            <td>{{ $challan->challan_date->format('d/m/Y') }}</td>
                            <td>{{ $challan->items->count() }} items</td>
                            <td class="text-end">₹{{ number_format($challan->subtotal, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Item Breakdown -->
        <div class="card mb-4">
            <div class="card-header">Item Breakdown</div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Description</th>
                            <th class="text-end">Qty</th>
                            <th>Unit</th>
                            <th class="text-end">Rate</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $itemNum = 1; @endphp
                        @foreach($invoice->challans as $challan)
                            @foreach($challan->items as $item)
                            <tr>
                                <td>{{ $itemNum++ }}</td>
                                <td>{{ $item->description }}</td>
                                <td class="text-end">{{ number_format($item->quantity, 3) }}</td>
                                <td>{{ $item->unit }}</td>
                                <td class="text-end">₹{{ number_format($item->rate, 2) }}</td>
                                <td class="text-end">₹{{ number_format($item->amount, 2) }}</td>
                            </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Party Details -->
        <div class="card mb-4">
            <div class="card-header">Party Details</div>
            <div class="card-body">
                <h5>{{ $invoice->party->name }}</h5>
                <p class="text-muted mb-2">{{ $invoice->party->address }}</p>
                <p class="mb-1"><i class="bi bi-telephone me-2"></i>{{ $invoice->party->contact_number }}</p>
                @if($invoice->party->gst_number)
                <p class="mb-0"><i class="bi bi-file-text me-2"></i>{{ $invoice->party->gst_number }}</p>
                @endif
            </div>
        </div>
        
        <!-- Amount Summary -->
        <div class="card mb-4">
            <div class="card-header">Amount Summary</div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td>Subtotal:</td>
                        <td class="text-end">₹{{ number_format($invoice->subtotal, 2) }}</td>
                    </tr>
                    @if($invoice->gst_amount > 0)
                    <tr>
                        <td>GST ({{ $invoice->gst_percent }}%):</td>
                        <td class="text-end text-success">+ ₹{{ number_format($invoice->gst_amount, 2) }}</td>
                    </tr>
                    @endif
                    @if($invoice->tds_amount > 0)
                    <tr>
                        <td>TDS ({{ $invoice->tds_percent }}%):</td>
                        <td class="text-end text-danger">- ₹{{ number_format($invoice->tds_amount, 2) }}</td>
                    </tr>
                    @endif
                    @if($invoice->discount_amount > 0)
                    <tr>
                        <td>Discount 
                            @if($invoice->discount_type == 'percentage')
                                ({{ $invoice->discount_value }}%)
                            @endif
                            :
                        </td>
                        <td class="text-end text-danger">- ₹{{ number_format($invoice->discount_amount, 2) }}</td>
                    </tr>
                    @endif
                    <tr class="table-light">
                        <td class="fw-bold fs-5">Final Amount:</td>
                        <td class="text-end highlight-amount">₹{{ number_format($invoice->final_amount, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>
        
        @if($invoice->notes)
        <div class="card">
            <div class="card-header">Notes</div>
            <div class="card-body">
                {{ $invoice->notes }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
