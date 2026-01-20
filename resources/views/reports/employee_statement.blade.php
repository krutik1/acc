@extends('layouts.main')

@section('title', 'Employee Statement')

@section('content')
<div class="page-header mb-4">
    <h1>Employee Statement</h1>
</div>

<div class="card mb-4 d-print-none">
    <div class="card-body">
        <form action="{{ route('reports.employee-statement') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Select Employee</label>
                <select name="user_id" class="form-select" required>
                    <option value="">Select Employee</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ request('user_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">From Date</label>
                <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">To Date</label>
                <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">View Statement</button>
            </div>
            <div class="col-md-1">
                <button type="button" onclick="window.print()" class="btn btn-secondary w-100">
                    <i class="bi bi-printer"></i>
                </button>
            </div>
        </form>
    </div>
</div>

@if($user)
<div class="card">
    <div class="card-header bg-white py-3">
        <div class="d-flex justify-content-between">
            <div>
                <h5 class="mb-1">Statement for {{ $user->name }}</h5>
                <small class="text-muted">{{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }} to {{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}</small>
            </div>
            <div class="text-end">
                <h6 class="mb-1">Opening Balance (Pending Salaries)</h6>
                <span class="badge bg-secondary fs-6">₹{{ number_format($openingBalance, 2) }}</span>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th class="text-end">Earnings (Cr)</th>
                        <th class="text-end">Upaad / Deductions (Dr)</th>
                        <th class="text-end">Paid Out (Dr)</th>
                        <th class="text-end">Balance (Payable)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="bg-light">
                        <td colspan="5" class="text-end fw-bold">Opening Balance</td>
                        <td class="text-end fw-bold">₹{{ number_format($openingBalance, 2) }}</td>
                    </tr>
                    
                    @php $runningBalance = $openingBalance; @endphp
                    
                    @forelse($transactions as $trx)
                        @php
                            // Logic: 
                            // Salary Gen: +Earnings -Upaad = +Payable increase. Or simply adding Payable.
                            // Upaad Taken: -Upaad Amount. (If we consider Upaad deduction in salary separately)
                            
                            // Let's look at how I simplified $trx['payable'].
                            // Salary Gen: payable = net payable amount (Positive).
                            // Upaad Taken: payable = -amount (Negative).
                            // Paid Out: payable = -amount (Negative).
                            
                            $runningBalance += $trx['payable'];
                        @endphp
                        <tr>
                            <td>{{ $trx['date']->format('d M, Y') }}</td>
                            <td>{{ $trx['description'] }}</td>
                            <td class="text-end text-success">{{ isset($trx['earnings']) && $trx['earnings'] > 0 ? '₹'.number_format($trx['earnings'], 2) : '-' }}</td>
                            <td class="text-end text-danger">{{ isset($trx['upaad']) && $trx['upaad'] > 0 ? '₹'.number_format($trx['upaad'], 2) : '-' }}</td>
                            <td class="text-end text-primary">{{ isset($trx['paid']) && $trx['paid'] > 0 ? '₹'.number_format($trx['paid'], 2) : '-' }}</td>
                            <td class="text-end fw-bold">₹{{ number_format($runningBalance, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No transactions found in this period.</td>
                        </tr>
                    @endforelse
                    
                    <tr class="bg-light fw-bold">
                        <td colspan="5" class="text-end">Closing Balance (Net Payable)</td>
                        <td class="text-end">₹{{ number_format($runningBalance, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@endsection
