@extends('layouts.main')

@section('title', 'My Trips')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <h1>My Trips</h1>
    @if(!auth()->user()->is_blocked)
        <a href="{{ route('driver.trips.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>New Trip
        </a>
    @endif
</div>

<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('driver.trips.index') }}" method="GET" class="row g-3 align-items-end">
             <div class="col-md-3">
                <label for="status" class="form-label text-muted small text-uppercase fw-bold">Status</label>
                <select class="form-select form-select-sm" id="status" name="status">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="date_from" class="form-label text-muted small text-uppercase fw-bold">Date From</label>
                <input type="date" class="form-control form-control-sm" id="date_from" name="date_from" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-3">
                <label for="date_to" class="form-label text-muted small text-uppercase fw-bold">Date To</label>
                <input type="date" class="form-control form-control-sm" id="date_to" name="date_to" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary flex-grow-1">Filter</button>
                    <a href="{{ route('driver.trips.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
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
                        <th>Route</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($trips as $trip)
                        <tr>
                            <td>{{ $trip->trip_date->format('Y-m-d H:i') }}</td>
                            <td>
                                <div><i class="bi bi-geo-alt-fill text-success me-1" style="font-size: 0.8em;"></i> {{ $trip->pickup_location }}</div>
                                <div class="text-muted"><i class="bi bi-arrow-down me-1" style="font-size: 0.8em;"></i></div>
                                <div><i class="bi bi-geo-alt-fill text-danger me-1" style="font-size: 0.8em;"></i> {{ $trip->drop_location }}</div>
                            </td>
                            <td>
                                <div>{{ Str::limit($trip->description, 30) }}</div>
                                <div class="d-flex gap-2 mt-1">
                                    <small class="text-muted fw-bold">{{ $trip->quantity }} {{ $trip->unit }}</small>
                                    @if($trip->effective_payment_mode === 'trip')
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" style="font-size: 0.7em;">Fixed</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" style="font-size: 0.7em;">PCS</span>
                                    @endif
                                </div>
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
                                    <a href="{{ route('driver.trips.edit', $trip) }}" class="btn btn-sm btn-outline-primary action-btn bg-white text-primary border-0 shadow-none">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @else
                                    <span class="text-muted fs-7"><i class="bi bi-lock"></i></span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
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
