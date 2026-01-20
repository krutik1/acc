@extends('layouts.main')

@section('title', 'Payments')

@section('content')
<div class="page-header">
    <h1>Payments</h1>
    <a href="{{ route('payments.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Record Payment
    </a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('payments.index') }}" class="row g-3">
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
            <div class="col-md-2">
                 <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <option value="received" {{ request('type') == 'received' ? 'selected' : '' }}>Received</option>
                    <option value="sent" {{ request('type') == 'sent' ? 'selected' : '' }}>Sent</option>
                </select>
            </div>
             <div class="col-md-3">
                <div class="input-group">
                    <input type="number" name="min_amount" class="form-control" placeholder="Min" value="{{ request('min_amount') }}">
                    <span class="input-group-text">-</span>
                    <input type="number" name="max_amount" class="form-control" placeholder="Max" value="{{ request('max_amount') }}">
                </div>
            </div>
            
            <div class="col-md-9">
                <input type="text" name="search" class="form-control" placeholder="Search Payment No..." value="{{ request('search') }}">
            </div>
             <div class="col-md-3 text-end">
                 <button type="submit" class="btn btn-primary me-2"><i class="bi bi-filter me-1"></i>Filter</button>
                 <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">


    <div class="table-responsive">
        <table class="table align-items-center table-flush table-hover mb-0">
            <thead>
                <tr>
                    <th>Payment No.</th>
                    <th>Date</th>
                    <th>Party</th>
                    <th>Type</th>
                    <th>Mode</th>
                    <th class="text-end">Amount</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                <tr>
                    <td>
                        <a href="{{ route('payments.show', $payment) }}" class="fw-bold text-primary text-decoration-none">
                            {{ $payment->payment_number }}
                        </a>
                    </td>
                    <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                    <td>
                         <div class="d-flex align-items-center">
                            <span class="avatar avatar-xs rounded-circle bg-light text-dark me-2 d-flex align-items-center justify-content-center border" style="width: 24px; height: 24px; font-size: 10px;">
                                {{ substr($payment->party->name, 0, 1) }}
                            </span>
                            {{ Str::limit($payment->party->name, 20) }}
                        </div>
                    </td>
                    <td>
                        @if($payment->type == 'received')
                            <span class="badge bg-success">Received</span>
                        @else
                            <span class="badge bg-danger">Sent</span>
                        @endif
                    </td>
                    <td><span class="text-muted small text-uppercase">{{ str_replace('_', ' ', $payment->mode) }}</span></td>
                    <td class="text-end fw-bold">₹{{ number_format($payment->amount, 2) }}</td>
                    <td class="text-end">
                        <a href="{{ route('payments.show', $payment) }}" class="action-btn bg-warning me-1" title="View">
                            <i class="bi bi-eye-fill text-white" style="font-size: 0.8rem;"></i>
                        </a>
                        <a href="{{ route('payments.edit', $payment) }}" class="action-btn bg-info me-1" title="Edit">
                            <i class="bi bi-pencil-fill text-white" style="font-size: 0.8rem;"></i>
                        </a>
                        <a href="{{ route('payments.print', $payment) }}" class="action-btn bg-primary me-1" title="Print" target="_blank">
                            <i class="bi bi-printer-fill text-white" style="font-size: 0.8rem;"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                       <div class="text-muted">
                            <i class="bi bi-cash-stack display-4 mb-3 d-block opacity-50"></i>
                            <p class="h5">No payments found</p>
                            <a href="{{ route('payments.create') }}" class="btn btn-sm btn-primary mt-2">Record First Payment</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($payments->hasPages())
    <div class="card-footer border-0 py-4">
        <div class="d-flex justify-content-end">
            {{ $payments->links() }}
        </div>
    </div>
    @endif
</div>

@if($payments->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $payments->links() }}
</div>
@endif
@endsection
