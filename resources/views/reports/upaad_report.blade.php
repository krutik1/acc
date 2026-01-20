@extends('layouts.main')

@section('title', 'Advance (Upaad) Report')

@section('content')
<div class="page-header mb-4">
    <h1>Advance (Upaad) Report</h1>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('reports.upaad-report') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Select Month</label>
                <input type="month" name="month" class="form-control" value="{{ $month }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Employee (Optional)</label>
                <select name="user_id" class="form-select">
                    <option value="">All Employees</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ request('user_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">View Report</button>
            </div>
            <div class="col-md-2">
                <button type="button" onclick="window.print()" class="btn btn-secondary w-100">
                    <i class="bi bi-printer me-1"></i>Print
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card h-100">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Employee-wise Summary ({{ \Carbon\Carbon::parse($month)->format('F Y') }})</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Employee</th>
                                <th class="text-center">No. of Advances</th>
                                <th class="text-end">Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalReport = 0; @endphp
                            @forelse($employeeSummary as $userId => $data)
                                @php $totalReport += $data['total']; @endphp
                                <tr>
                                    <td>{{ $data['user']->name }}</td>
                                    <td class="text-center">{{ $data['count'] }}</td>
                                    <td class="text-end fw-bold">₹{{ number_format($data['total'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">No advances taken in this month.</td>
                                </tr>
                            @endforelse
                            <tr class="bg-light fw-bold">
                                <td colspan="2" class="text-end">Total</td>
                                <td class="text-end text-danger">₹{{ number_format($totalReport, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Detailed list</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Date</th>
                                <th>Employee</th>
                                <th>Role</th>
                                <th>Remarks</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($upaads as $upaad)
                                <tr>
                                    <td>{{ $upaad->date->format('d M, Y') }}</td>
                                    <td>{{ $upaad->user->name }}</td>
                                    <td><small class="badge bg-light text-dark border">{{ ucfirst($upaad->user->role) }}</small></td>
                                    <td>{{ $upaad->remarks }}</td>
                                    <td class="text-end fw-bold text-danger">₹{{ number_format($upaad->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
