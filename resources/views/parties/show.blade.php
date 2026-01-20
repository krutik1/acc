@extends('layouts.main')

@section('title', $party->name)

@section('content')
<div class="page-header">
    <h1>{{ $party->name }}</h1>
    <div>
        <a href="{{ route('parties.edit', $party) }}" class="btn btn-outline-primary me-2">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        <a href="{{ route('parties.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">Party Details</div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th class="text-muted">Name</th>
                        <td>{{ $party->name }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Contact</th>
                        <td>{{ $party->contact_number }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">GST</th>
                        <td>{{ $party->gst_number ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Address</th>
                        <td>{{ $party->address }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">Recent Challans</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <tbody>
                        @forelse($party->challans as $challan)
                        <tr>
                            <td>
                                <a href="{{ route('challans.show', $challan) }}">{{ $challan->challan_number }}</a>
                                <br><small class="text-muted">{{ $challan->challan_date->format('d/m/Y') }}</small>
                            </td>
                            <td class="text-end">₹{{ number_format($challan->subtotal, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td class="text-center text-muted py-3">No challans</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">Recent Invoices</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <tbody>
                        @forelse($party->invoices as $invoice)
                        <tr>
                            <td>
                                <a href="{{ route('invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a>
                                <br><small class="text-muted">{{ $invoice->invoice_date->format('d/m/Y') }}</small>
                            </td>
                            <td class="text-end">₹{{ number_format($invoice->final_amount, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td class="text-center text-muted py-3">No invoices</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
