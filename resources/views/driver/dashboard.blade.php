@extends('layouts.main')

@section('title', 'Driver Dashboard')

@section('content')
<div class="page-header mb-4">
    <h1>Driver Dashboard</h1>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Trips</h5>
                @if(!auth()->user()->is_blocked)
                    <a href="{{ route('driver.trips.create') }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-lg me-1"></i>New Trip
                    </a>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Date</th>
                                <th>Route</th>
                                <th>Status</th>
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
                                        <div class="mt-1">
                                            @if($trip->effective_payment_mode === 'trip')
                                                <span class="badge bg-light text-secondary border" style="font-size: 0.7em;">Fixed Trip</span>
                                            @else
                                                <span class="badge bg-light text-secondary border" style="font-size: 0.7em;">PCS Based</span>
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
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">
                                        <i class="bi bi-truck display-6 d-block mb-2"></i>
                                        No recent trips found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white border-top-0 text-center">
                     <a href="{{ route('driver.trips.index') }}" class="text-decoration-none">View All Trips &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
        <!-- Actions -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('driver.reports.index') }}" class="btn btn-outline-primary">
                        <i class="bi bi-bar-chart-line me-2"></i>My Reports
                    </a>
                </div>
            </div>
        </div>

        <!-- My Rates -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">My Payment Rates</h5>
            </div>
            <div class="list-group list-group-flush">
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold">Default Mode</div>
                        <small class="text-muted">Applied if not set on trip</small>
                    </div>
                    <span class="badge bg-primary rounded-pill">{{ auth()->user()->payment_mode == 'trip' ? 'Fixed Trip' : 'PCS Based' }}</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold">Trip Rate</div>
                        <small class="text-muted">Per Trip</small>
                    </div>
                    <span class="fw-bold">₹{{ number_format(auth()->user()->trip_rate, 2) }}</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold">PCS Rate</div>
                        <small class="text-muted">Per Unit</small>
                    </div>
                    <span class="fw-bold">₹{{ number_format(auth()->user()->pcs_rate, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
