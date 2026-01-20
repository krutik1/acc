@extends('layouts.main')

@section('title', 'Advance (Upaad)')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <h1>Advance (Upaad) Records</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUpaadModal">
        <i class="bi bi-plus-lg me-1"></i>Add Upaad
    </button>
</div>

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.upaads.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <select name="employee_id" class="form-select">
                    <option value="">All Employees</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" placeholder="From Date" aria-label="From Date">
            </div>
            <div class="col-md-3">
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" placeholder="To Date" aria-label="To Date">
            </div>
            <div class="col-md-3">
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
                        <th>Date</th>
                        <th>Employee</th>
                        <th>Amount</th>
                        <th>Remarks</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($upaads as $upaad)
                        <tr>
                            <td>{{ $upaad->date->format('d M, Y') }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-2 bg-light rounded-circle d-flex align-items-center justify-content-center text-primary" style="width: 32px; height: 32px;">
                                        <i class="bi bi-person"></i>
                                    </div>
                                    <span class="fw-medium">{{ $upaad->user->name }}</span>
                                </div>
                            </td>
                            <td class="fw-bold text-danger">₹{{ number_format($upaad->amount, 2) }}</td>
                            <td>{{ $upaad->remarks ?? '-' }}</td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('admin.upaads.edit', $upaad) }}" class="btn btn-sm btn-outline-primary border-0" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    
                                    <form action="{{ route('admin.upaads.destroy', $upaad) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger border-0" onclick="return confirm('Are you sure you want to delete this record?')" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="bi bi-cash-stack display-6 d-block mb-2"></i>
                                No upaad records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($upaads->hasPages())
            <div class="card-footer bg-white border-top-0">
                {{ $upaads->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Add Upaad Modal --}}
<div class="modal fade" id="addUpaadModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.upaads.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Record Advance Payment (Upaad)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Employee <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-select" required>
                            <option value="">Select Employee</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Record</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
