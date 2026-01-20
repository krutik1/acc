@extends('layouts.main')

@section('title', 'Employees')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <h1>Employees</h1>
    <a href="{{ route('admin.employees.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Add Employee
    </a>
</div>

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.employees.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search by name or email" value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="salary_type" class="form-select">
                    <option value="">All Salary Types</option>
                    <option value="fixed" {{ request('salary_type') == 'fixed' ? 'selected' : '' }}>Fixed Salary</option>
                    <option value="piece" {{ request('salary_type') == 'piece' ? 'selected' : '' }}>Piece Rate</option>
                    <option value="trip" {{ request('salary_type') == 'trip' ? 'selected' : '' }}>Trip Based</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive (Blocked)</option>
                </select>
            </div>
            <div class="col-md-2">
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
                        <th>Name</th>
                        <th>Email</th>
                        <th>Salary Type</th>
                        <th>Rate/Amount</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-2 bg-light rounded-circle d-flex align-items-center justify-content-center text-primary" style="width: 32px; height: 32px;">
                                        <i class="bi bi-person"></i>
                                    </div>
                                    <span class="fw-medium">{{ $employee->name }}</span>
                                </div>
                            </td>
                            <td>{{ $employee->email }}</td>
                            <td>
                                @if($employee->payment_mode == 'fixed')
                                    <span class="badge bg-info text-dark">Fixed</span>
                                @elseif($employee->payment_mode == 'pcs')
                                    <span class="badge bg-warning text-dark">Piece Rate</span>
                                @elseif($employee->payment_mode == 'trip')
                                    <span class="badge bg-secondary">Trip Based</span>
                                @endif
                            </td>
                            <td>
                                @if($employee->payment_mode == 'fixed')
                                    ₹{{ number_format($employee->fixed_salary, 2) }}
                                @elseif($employee->payment_mode == 'pcs')
                                    ₹{{ number_format($employee->pcs_rate, 2) }} / pc
                                @elseif($employee->payment_mode == 'trip')
                                    ₹{{ number_format($employee->trip_rate, 2) }} / trip
                                @endif
                            </td>
                            <td>
                                @if($employee->is_blocked)
                                    <span class="badge bg-danger">Blocked</span>
                                @else
                                    <span class="badge bg-success">Active</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('admin.employees.edit', $employee) }}" class="btn btn-sm btn-outline-primary border-0" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    
                                    <form action="{{ route('admin.employees.destroy', $employee) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger border-0" onclick="return confirm('Are you sure you want to delete this employee? This action cannot be undone.')" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-person-x display-6 d-block mb-2"></i>
                                No employees found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($employees->hasPages())
            <div class="card-footer bg-white border-top-0">
                {{ $employees->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
