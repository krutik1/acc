@extends('layouts.main')

@section('title', 'Challan ' . $challan->challan_number)

@section('content')
<div class="page-header">
    <h1>Challan: {{ $challan->challan_number }}</h1>
    <div>
        <a href="{{ route('challans.edit', $challan) }}" class="btn btn-outline-primary me-2">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        <a href="{{ route('challans.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">Challan Details</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <small class="text-muted">Challan Number</small>
                        <p class="fw-bold">{{ $challan->challan_number }}</p>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Challan Date</small>
                        <p>{{ $challan->challan_date->format('d M Y') }}</p>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Status</small>
                        <p>
                            @if($challan->is_invoiced)
                                <span class="badge bg-success">Invoiced</span>
                            @else
                                <span class="badge bg-warning text-dark">Pending</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">Items</div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Description</th>
                            <th class="text-end">Quantity</th>
                            <th>Unit</th>
                            <th class="text-end">Rate</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($challan->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->description }}</td>
                            <td class="text-end">{{ number_format($item->quantity, 3) }}</td>
                            <td>{{ $item->unit }}</td>
                            <td class="text-end">₹{{ number_format($item->rate, 2) }}</td>
                            <td class="text-end">₹{{ number_format($item->amount, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-light">
                            <td colspan="5" class="text-end"><strong>Subtotal:</strong></td>
                            <td class="text-end"><strong class="fs-5">₹{{ number_format($challan->subtotal, 2) }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">Party Details</div>
            <div class="card-body">
                <h5>{{ $challan->party->name }}</h5>
                <p class="text-muted mb-2">{{ $challan->party->address }}</p>
                <p class="mb-1"><i class="bi bi-telephone me-2"></i>{{ $challan->party->contact_number }}</p>
                @if($challan->party->gst_number)
                <p class="mb-0"><i class="bi bi-file-text me-2"></i>{{ $challan->party->gst_number }}</p>
                @endif
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-body text-center">
                <p class="mb-3">Ready to create an invoice?</p>
                <a href="{{ route('invoices.create') }}?party_id={{ $challan->party_id }}" class="btn btn-primary">
                    <i class="bi bi-receipt me-1"></i>Create Invoice
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
