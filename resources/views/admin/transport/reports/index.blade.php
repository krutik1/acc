@extends('layouts.main')

@section('title', 'Transportation Reports')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <h1>Transportation Reports</h1>
    <div class="dropdown">
        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <i class="bi bi-download me-1"></i>Export
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}">Export CSV</a></li>
        </ul>
    </div>
</div>

<div class="row">
    <!-- Filter Sidebar -->
    <div class="col-md-3">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Filters</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.transport.reports.index') }}" method="GET">
                    <div class="mb-3">
                        <label for="driver_id" class="form-label">Driver</label>
                        <select class="form-select" id="driver_id" name="driver_id">
                            <option value="">All Drivers</option>
                            @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}" {{ request('driver_id') == $driver->id ? 'selected' : '' }}>
                                    {{ $driver->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>

                     <div class="mb-3">
                        <label for="payment_mode" class="form-label">Payment Mode</label>
                        <select class="form-select" id="payment_mode" name="payment_mode">
                            <option value="">All Modes</option>
                            <option value="trip" {{ request('payment_mode') == 'trip' ? 'selected' : '' }}>Fixed Trip</option>
                            <option value="pcs" {{ request('payment_mode') == 'pcs' ? 'selected' : '' }}>PCS Based</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="date_from" class="form-label">Date From</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                    </div>

                    <div class="mb-3">
                        <label for="date_to" class="form-label">Date To</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="{{ route('admin.transport.reports.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Report Content -->
    <div class="col-md-9">
        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body">
                        <h6 class="card-title opacity-75">Total Trips</h6>
                        <h2 class="mb-0">{{ $summary['total_trips'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white h-100">
                    <div class="card-body">
                        <h6 class="card-title opacity-75">Total Commission</h6>
                        <h2 class="mb-0">₹{{ number_format($summary['total_commission'], 2) }}</h2>
                    </div>
                </div>
            </div>
             <div class="col-md-4">
                <div class="card bg-info text-white h-100">
                    <div class="card-body">
                         <h6 class="card-title opacity-75">Quantity Summary</h6>
                         <ul class="list-unstyled mb-0 small">
                             @forelse($quantityByUnit as $unit => $qty)
                                <li><strong>{{ $unit }}:</strong> {{ number_format($qty, 2) }}</li>
                             @empty
                                <li>-</li>
                             @endforelse
                         </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Trip Details</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Date</th>
                                <th>Driver</th>
                                <th>Route</th>
                                <th>Details</th>
                                <th>Status</th>
                                <th class="text-end">Commission</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($trips as $trip)
                                <tr>
                                    <td>{{ $trip->trip_date->format('Y-m-d H:i') }}</td>
                                    <td>{{ $trip->user->name }}</td>
                                    <td>
                                        <small>{{ $trip->pickup_location }} <i class="bi bi-arrow-right"></i> {{ $trip->drop_location }}</small>
                                    </td>
                                    <td>
                                        <div>{{ Str::limit($trip->description, 20) }}</div>
                                        <small class="text-muted">{{ $trip->quantity }} {{ $trip->unit }}</small>
                                        @if($trip->effective_payment_mode === 'trip')
                                            <span class="badge bg-light text-secondary border" style="font-size: 0.7em;">Fixed</span>
                                        @else
                                            <span class="badge bg-light text-secondary border" style="font-size: 0.7em;">PCS</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $trip->status === 'approved' ? 'success' : ($trip->status === 'rejected' ? 'danger' : 'warning') }}-subtle text-{{ $trip->status === 'approved' ? 'success' : ($trip->status === 'rejected' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($trip->status) }}
                                        </span>
                                    </td>
                                    <td class="text-end">₹{{ number_format($trip->driver_commission, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No trips match the selected filters.</td>
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
    </div>
</div>
@endsection
