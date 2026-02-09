@extends('layouts.main')

@section('title', 'My Payments')

@section('content')
<div class="page-header mb-4">
    <h1>My Payments</h1>
</div>

{{-- Filters --}}
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('driver.payments.index') }}" method="GET" class="row g-3">
            <div class="col-md-2">
                <label class="form-label small text-muted">Type</label>
                <select name="payment_type" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    <option value="Advance" {{ request('payment_type') == 'Advance' ? 'selected' : '' }}>Advance</option>
                    <option value="Salary" {{ request('payment_type') == 'Salary' ? 'selected' : '' }}>Salary</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Month</label>
                <input type="month" name="month" class="form-control form-control-sm" value="{{ request('month') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Year</label>
                <select name="year" class="form-select form-select-sm">
                    <option value="">All Years</option>
                    @for($y = date('Y'); $y >= 2024; $y--)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed/Paid</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending/Generated</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-sm btn-primary w-100">
                    <i class="bi bi-filter me-1"></i> Apply Filters
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Date</th>
                        <th>Type</th>
                        <th>Month</th>
                        <th>Description</th>
                        <th class="text-end">Amount</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                        <tr>
                            <td class="ps-4">
                                {{ $record->date ? \Carbon\Carbon::parse($record->date)->format('d M, Y') : '-' }}
                            </td>
                            <td>
                                @php
                                    $typeClass = $record->type === 'Advance' ? 'warning' : 'info';
                                    $typeTextClass = $record->type === 'Advance' ? 'dark' : 'dark'; // Use dark text for better contrast
                                @endphp
                                <span class="badge bg-{{ $typeClass }} bg-opacity-25 text-{{ $typeTextClass }} border border-{{ $typeClass }} border-opacity-25">
                                    {{ $record->type }}
                                </span>
                            </td>
                            <td>
                                @if($record->month_year)
                                    {{ \Carbon\Carbon::parse($record->month_year)->format('M-Y') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $record->description }}</td>
                            <td class="text-end fw-bold {{ $record->amount < 0 ? 'text-danger' : 'text-success' }}">
                                {{ $record->amount < 0 ? '-' : '' }}₹{{ number_format(abs($record->amount), 2) }}
                            </td>
                            <td class="text-center">
                                @php
                                    $statusColor = match(strtolower($record->status)) {
                                        'paid', 'completed', 'finalized' => 'success',
                                        'pending', 'generated', 'calculated' => 'warning',
                                        'cancelled', 'rejected' => 'danger',
                                        default => 'secondary'
                                    };
                                    // Ensure text contrast for warning/yellow backgrounds
                                    $statusTextLimit = $statusColor === 'warning' ? 'dark' : $statusColor;
                                @endphp
                                <span class="badge rounded-pill bg-{{ $statusColor }} bg-opacity-25 text-{{ $statusTextLimit }} border border-{{ $statusColor }} border-opacity-25 px-2 py-1">
                                    {{ ucfirst($record->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-wallet2 display-6 d-block mb-2"></i>
                                No payment records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($records->hasPages())
            <div class="card-footer bg-white border-top-0">
                {{ $records->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
