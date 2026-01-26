@extends('layouts.main')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Driver Monthly Payments</h1>
        <a href="{{ route('admin.driver-salaries.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Generate Payment
        </a>
    </div>

    <!-- Filter Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Payments</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.driver-salaries.index') }}" method="GET" class="row align-items-end">
                <div class="col-md-3 mb-3">
                    <label for="month">Month</label>
                    <input type="month" name="month" id="month" class="form-control" value="{{ request('month') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="generated" {{ request('status') == 'generated' ? 'selected' : '' }}>Generated</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                    <a href="{{ route('admin.driver-salaries.index') }}" class="btn btn-secondary w-100 mt-2">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Payment Records</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Driver</th>
                            <th>Month</th>
                            <th>Trips</th>
                            <th>Total Earned</th>
                            <th>Advance Payment</th>
                            <th>Bonus/Ded</th>
                            <th>Payable</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($salaries as $salary)
                        <tr>
                            <td>{{ $salary->user->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($salary->month)->format('M Y') }}</td>
                            <td>{{ $salary->total_trips }}</td>
                            <td>
                                <strong>{{ number_format($salary->total_amount, 2) }}</strong><br>
                                <small class="text-muted">Fixed: {{ number_format($salary->fixed_trip_amount, 2) }}</small><br>
                                <small class="text-muted">PCS: {{ number_format($salary->pcs_trip_amount, 2) }}</small>
                            </td>
                            <td>
                                <span class="text-danger">-{{ number_format($salary->advance_amount, 2) }}</span>
                            </td>
                            <td>
                                <span class="text-success">+{{ number_format($salary->bonus, 2) }}</span><br>
                                <span class="text-danger">-{{ number_format($salary->deduction, 2) }}</span>
                            </td>
                            <td class="font-weight-bold">{{ number_format($salary->payable_amount, 2) }}</td>
                            <td>
                                @if($salary->status == 'paid')
                                    <span class="badge badge-success">Paid</span>
                                    <br><small>{{ $salary->payment_date ? $salary->payment_date->format('d M Y') : '' }}</small>
                                @else
                                    <span class="badge badge-warning">Pending</span>
                                @endif
                            </td>
                            <td>
                                @if($salary->status != 'paid')
                                    <form action="{{ route('admin.driver-salaries.markPaid', $salary) }}" method="POST" class="d-inline" onsubmit="return confirm('Mark this as Paid?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Mark Paid">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>
                                @endif
                                <form action="{{ route('admin.driver-salaries.destroy', $salary) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this record?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">No payment records found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $salaries->links() }}
        </div>
    </div>
</div>
@endsection
