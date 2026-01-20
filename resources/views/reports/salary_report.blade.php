@extends('layouts.main')

@section('title', 'Monthly Salary Report')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <h1>Monthly Salary Report</h1>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('reports.salary-report') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Select Month</label>
                <input type="month" name="month" class="form-control" value="{{ $month }}">
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

<div class="card">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 text-center">Salary Report for {{ \Carbon\Carbon::parse($month)->format('F Y') }}</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Employee</th>
                        <th class="text-end">Basic / Rate</th>
                        <th class="text-end">Gross Earnings</th>
                        <th class="text-end text-danger">Upaad Deduction</th>
                        <th class="text-end text-success">Net Payable</th>
                        <th class="text-center">Status</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalEarnings = 0;
                        $totalUpaad = 0;
                        $totalPayable = 0;
                    @endphp
                    @forelse($salaries as $salary)
                        @php
                            $totalEarnings += $salary->total_amount;
                            $totalUpaad += $salary->total_upaad;
                            $totalPayable += $salary->payable_amount;
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $salary->user->name }}</strong><br>
                                <small class="text-muted">{{ ucfirst($salary->salary_type) }}</small>
                            </td>
                            <td class="text-end">
                                @if($salary->salary_type == 'fixed')
                                    {{ number_format($salary->fixed_salary, 2) }}
                                @else
                                    {{ $salary->total_pieces }} pcs @ {{ $salary->piece_rate }}
                                @endif
                            </td>
                            <td class="text-end">₹{{ number_format($salary->total_amount, 2) }}</td>
                            <td class="text-end text-danger">₹{{ number_format($salary->total_upaad, 2) }}</td>
                            <td class="text-end fw-bold">₹{{ number_format($salary->payable_amount, 2) }}</td>
                            <td class="text-center">
                                @if($salary->status == 'paid')
                                    <span class="badge bg-success">PAID</span>
                                    @if($salary->payment_date)<br><small>{{ $salary->payment_date->format('d/m') }}</small>@endif
                                @else
                                    <span class="badge bg-warning text-dark">PENDING</span>
                                @endif
                            </td>
                            <td><small>{{ $salary->remarks }}</small></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No salary records generated for this month.</td>
                        </tr>
                    @endforelse
                    <tr class="bg-light fw-bold">
                        <td colspan="2" class="text-end">Total</td>
                        <td class="text-end">₹{{ number_format($totalEarnings, 2) }}</td>
                        <td class="text-end text-danger">₹{{ number_format($totalUpaad, 2) }}</td>
                        <td class="text-end text-success">₹{{ number_format($totalPayable, 2) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
