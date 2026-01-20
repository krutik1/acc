@extends('layouts.main')

@section('title', 'Monthly Salaries')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <h1>Monthly Salaries</h1>
    <a href="{{ route('admin.salaries.create') }}" class="btn btn-primary">
        <i class="bi bi-calculator me-1"></i>Generate Salary
    </a>
</div>

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.salaries.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="month" name="month" class="form-control" value="{{ request('month') }}">
            </div>
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-secondary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Month</th>
                        <th>Employee</th>
                        <th>Earnings</th>
                        <th>Upaad Deducted</th>
                        <th>Net Payable</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($salaries as $salary)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($salary->month)->format('F Y') }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-2 bg-light rounded-circle d-flex align-items-center justify-content-center text-primary" style="width: 32px; height: 32px;">
                                        <i class="bi bi-person"></i>
                                    </div>
                                    <span class="fw-medium">{{ $salary->user->name }}</span>
                                </div>
                            </td>
                            <td>₹{{ number_format($salary->total_amount, 2) }}</td>
                            <td class="text-danger">-₹{{ number_format($salary->total_upaad, 2) }}</td>
                            <td class="fw-bold text-success">₹{{ number_format($salary->payable_amount, 2) }}</td>
                            <td>
                                @if($salary->status == 'paid')
                                    <span class="badge bg-success">Paid</span>
                                    <br><small class="text-muted">{{ $salary->payment_date ? $salary->payment_date->format('d/m/y') : '' }}</small>
                                @else
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    @if($salary->status == 'pending')
                                        <form action="{{ route('admin.salaries.markPaid', $salary) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-success border-0" title="Mark as Paid" onclick="return confirm('Mark this salary as Paid?')">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </form>
                                    @endif
                                    
                                    <form action="{{ route('admin.salaries.destroy', $salary) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger border-0" onclick="return confirm('Are you sure you want to delete this salary record?')" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-receipt display-6 d-block mb-2"></i>
                                No salary records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($salaries->hasPages())
            <div class="card-footer bg-white border-top-0">
                {{ $salaries->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
