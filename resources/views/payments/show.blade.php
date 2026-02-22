@extends('layouts.main')

@section('title', 'Payment Details')

@section('content')
<div class="page-header">
    <h1>Payment Details</h1>
    <div>
        <a href="{{ route('payments.edit', $payment) }}" class="btn btn-primary me-2">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        <a href="{{ route('payments.print', $payment) }}" class="btn btn-secondary me-2" target="_blank">
            <i class="bi bi-printer me-1"></i>Print
        </a>
        <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Payment #{{ $payment->payment_number }}</h5>
                <span class="badge {{ $payment->type == 'received' ? 'bg-success' : 'bg-danger' }}">
                    {{ ucfirst($payment->type) }}
                </span>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-sm-6">
                        <h6 class="mb-3 text-muted">Party Details</h6>
                        <p class="mb-1"><strong>{{ $payment->party->name }}</strong></p>
                        @if($payment->party->address)
                            <p class="mb-1">{{ $payment->party->address }}</p>
                        @endif
                        @if($payment->party->contact_number)
                            <p class="mb-1">Phone: {{ $payment->party->contact_number }}</p>
                        @endif
                    </div>
                    <div class="col-sm-6 text-sm-end">
                        <h6 class="mb-3 text-muted">Payment Info</h6>
                        <p class="mb-1">Date: <strong>{{ $payment->payment_date->format('d/m/Y') }}</strong></p>
                        <p class="mb-1">Mode: {{ ucfirst(str_replace('_', ' ', $payment->mode)) }}</p>
                        @if($payment->reference_number)
                            <p class="mb-1">Ref: {{ $payment->reference_number }}</p>
                        @endif
                    </div>
                </div>

                <div class="p-4 bg-light rounded text-center mb-4">
                    <h3 class="mb-0 display-6">₹{{ formatIndianCurrency($payment->amount) }}</h3>
                    <small class="text-muted">Amount Paid</small>
                </div>

                @if($payment->notes)
                <div class="mb-4">
                    <h6 class="text-muted">Notes</h6>
                    <p>{{ $payment->notes }}</p>
                </div>
                @endif

                @if($payment->invoices->count() > 0)
                <div class="mb-4">
                    <h6 class="text-muted mb-3">Linked Invoices</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Date</th>
                                    <th class="text-end">Invoice Amount</th>
                                    <th class="text-end">Paid in this Payment</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payment->invoices as $invoice)
                                <tr>
                                    <td>
                                        <a href="{{ route('invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a>
                                    </td>
                                    <td>{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                                    <td class="text-end">₹ {{ formatIndianCurrency($invoice->final_amount) }}</td>
                                    <td class="text-end fw-bold text-success">₹ {{ formatIndianCurrency($invoice->pivot->amount) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-light fw-bold">
                                    <td colspan="3" class="text-end">Total Allocated:</td>
                                    <td class="text-end">₹ {{ formatIndianCurrency($payment->invoices->sum('pivot.amount')) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                @endif

            </div>
            <div class="card-footer text-end">
                <form action="{{ route('payments.destroy', $payment) }}" method="POST" class="d-inline" 
                      onsubmit="return confirm('Are you sure you want to delete this payment?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">Delete Payment</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
