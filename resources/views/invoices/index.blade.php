@extends('layouts.main')

@section('title', 'Invoices')

@section('content')
<div class="page-header">
    <h1>Invoices</h1>
    <a href="{{ route('invoices.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Create Invoice
    </a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('invoices.index') }}" class="row g-3">
            <div class="col-md-3">
                <select name="party_id" class="form-select">
                    <option value="">All Parties</option>
                    @foreach($parties as $party)
                        <option value="{{ $party->id }}" {{ request('party_id') == $party->id ? 'selected' : '' }}>
                            {{ $party->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" placeholder="From">
            </div>
            <div class="col-md-2">
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}" placeholder="To">
            </div>
            <div class="col-md-3">
                <div class="input-group">
                    <input type="number" name="min_amount" class="form-control" placeholder="Min" value="{{ request('min_amount') }}">
                    <span class="input-group-text">-</span>
                    <input type="number" name="max_amount" class="form-control" placeholder="Max" value="{{ request('max_amount') }}">
                </div>
            </div>
            <div class="col-md-2">
                <input type="text" name="search" class="form-control" placeholder="Invoice No." value="{{ request('search') }}">
            </div>
            <div class="col-md-12 text-end">
                <button type="submit" class="btn btn-primary me-2"><i class="bi bi-filter me-1"></i>Filter</button>
                <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Invoices List -->
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Invoice No.</th>
                    <th>Date</th>
                    <th>Party</th>
                    <th class="text-end">Subtotal</th>
                    <th class="text-end">GST</th>
                    <th class="text-end">Final Amount</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                <tr>
                    <td><a href="{{ route('invoices.show', $invoice) }}"><strong>{{ $invoice->invoice_number }}</strong></a></td>
                    <td>{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                    <td>{{ $invoice->party->name }}</td>
                    <td class="text-end">₹{{ number_format($invoice->subtotal, 2) }}</td>
                    <td class="text-end">₹{{ number_format($invoice->gst_amount, 2) }}</td>
                    <td class="text-end"><strong class="text-primary">₹{{ number_format($invoice->final_amount, 2) }}</strong></td>
                    <td>
                        <a href="{{ route('invoices.show', $invoice) }}" class="action-btn bg-warning me-1" title="View">
                            <i class="bi bi-eye-fill text-white" style="font-size: 0.8rem;"></i>
                        </a>
                        <a href="{{ route('invoices.edit', $invoice) }}" class="action-btn bg-info me-1" title="Edit">
                            <i class="bi bi-pencil-fill text-white" style="font-size: 0.8rem;"></i>
                        </a>
                        <a href="{{ route('invoices.pdf', $invoice) }}" class="action-btn bg-danger me-1" title="Download PDF">
                            <i class="bi bi-file-pdf-fill text-white" style="font-size: 0.8rem;"></i>
                        </a>
                        <a href="{{ route('invoices.print', $invoice) }}" class="action-btn bg-primary me-1" title="Print" target="_blank">
                            <i class="bi bi-printer-fill text-white" style="font-size: 0.8rem;"></i>
                        </a>
                        <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" class="d-inline" 
                              onsubmit="return confirm('Are you sure you want to delete this invoice?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-btn bg-danger" title="Delete">
                                <i class="bi bi-trash-fill text-white" style="font-size: 0.8rem;"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">
                        <i class="bi bi-receipt display-4 d-block mb-2"></i>
                        No invoices found. <a href="{{ route('invoices.create') }}">Create your first invoice</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($invoices->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $invoices->links() }}
</div>
@endif
@endsection
