@extends('layouts.main')

@section('title', 'All Trips')

@section('content')
<div class="page-header mb-4 d-flex justify-content-between align-items-center">
    <h1>All Trips</h1>
    <a href="{{ route('admin.trips.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Create Trip
    </a>
</div>


<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.trips.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="driver_id" class="form-label text-muted small text-uppercase fw-bold">Driver</label>
                <select class="form-select form-select-sm" id="driver_id" name="driver_id">
                    <option value="">All Drivers</option>
                    @foreach($drivers as $driver)
                        <option value="{{ $driver->id }}" {{ request('driver_id') == $driver->id ? 'selected' : '' }}>
                            {{ $driver->name }}
                        </option>
                    @endforeach
                </select>
            </div>
             <div class="col-md-2">
                <label for="status" class="form-label text-muted small text-uppercase fw-bold">Status</label>
                <select class="form-select form-select-sm" id="status" name="status">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="date_from" class="form-label text-muted small text-uppercase fw-bold">Date From</label>
                <input type="date" class="form-control form-control-sm" id="date_from" name="date_from" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label for="date_to" class="form-label text-muted small text-uppercase fw-bold">Date To</label>
                <input type="date" class="form-control form-control-sm" id="date_to" name="date_to" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary flex-grow-1" title="Filter"><i class="bi bi-filter"></i></button>
                    <a href="{{ route('admin.trips.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
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
                        <th>Driver</th>
                        <th>Route</th>
                        <th>Description</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($trips as $trip)
                        <tr>
                            <td>{{ $trip->trip_date->format('Y-m-d H:i') }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-2 bg-light rounded-circle d-flex align-items-center justify-content-center text-primary" style="width: 24px; height: 24px; font-size: 0.8rem;">
                                        <i class="bi bi-person"></i>
                                    </div>
                                    <span>{{ $trip->user->name }}</span>
                                </div>
                            </td>
                            <td>
                                <div><i class="bi bi-geo-alt-fill text-success me-1" style="font-size: 0.8em;"></i> {{ $trip->pickup_location }}</div>
                                <div class="text-muted"><i class="bi bi-arrow-down me-1" style="font-size: 0.8em;"></i></div>
                                <div><i class="bi bi-geo-alt-fill text-danger me-1" style="font-size: 0.8em;"></i> {{ $trip->drop_location }}</div>
                            </td>
                            <td>
                                <div>{{ Str::limit($trip->description, 30) }}</div>
                                <small class="text-muted fw-bold">{{ $trip->quantity }} {{ $trip->unit }}</small>
                            </td>
                            <td>
                                @if($trip->effective_payment_mode === 'trip')
                                    <span class="badge bg-info-subtle text-info border border-info-subtle">Fixed Trip</span>
                                @else
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">PCS Based</span>
                                @endif
                                <div class="small text-muted mt-1">₹{{ number_format($trip->driver_commission, 2) }}</div>
                            </td>
                            <td>
                                @if($trip->status === 'approved')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Approved</span>
                                @elseif($trip->status === 'rejected')
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Rejected</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Pending</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($trip->status === 'pending')
                                    <form action="{{ route('admin.trips.status', $trip) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" name="status" value="approved" class="btn btn-sm btn-outline-success border-0" onclick="return confirm('Approve this trip?')" title="Approve">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.trips.status', $trip) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" name="status" value="rejected" class="btn btn-sm btn-outline-danger border-0" onclick="return confirm('Reject this trip?')" title="Reject">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('admin.trips.edit', $trip) }}" class="btn btn-sm btn-outline-primary border-0" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.trips.destroy', $trip) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this trip?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger border-0" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-truck display-6 d-block mb-2"></i>
                                No trips found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($trips->hasPages())
            <div class="card-footer bg-white border-top-0">
                {{ $trips->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
