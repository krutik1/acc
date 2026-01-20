@extends('layouts.main') 

@section('content') <!-- Assuming layouts.app or layouts.driver, checking DriverPaymentController usage -->
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">My Monthly Payments</h1>
    </div>

    <!-- Filter Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Payments</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('driver.salaries.index') }}" method="GET" class="row align-items-end">
                <div class="col-md-4 mb-3">
                    <label for="month">Month</label>
                    <input type="month" name="month" id="month" class="form-control" value="{{ request('month') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                    <a href="{{ route('driver.salaries.index') }}" class="btn btn-secondary w-100 mt-2">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Monthly Payments</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Trips</th>
                            <th>Total Earned</th>
                            <th>Advance Deducted</th>
                            <th>Bonus/Ded</th>
                            <th>Net Payable</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($salaries as $salary)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($salary->month)->format('M Y') }}</td>
                            <td>{{ $salary->total_trips }}</td>
                            <td>{{ number_format($salary->total_amount, 2) }}</td>
                            <td class="text-danger">-{{ number_format($salary->total_upaad, 2) }}</td>
                            <td>
                                @if($salary->bonus > 0) <span class="text-success">+{{ number_format($salary->bonus, 2) }}</span><br> @endif
                                @if($salary->deduction > 0) <span class="text-danger">-{{ number_format($salary->deduction, 2) }}</span> @endif
                            </td>
                            <td class="font-weight-bold">{{ number_format($salary->payable_amount, 2) }}</td>
                            <td>
                                @if($salary->status == 'paid')
                                    <span class="badge badge-success">Paid</span>
                                @else
                                    <span class="badge badge-warning">Pending</span>
                                @endif
                            </td>
                            <td>
                                {{ $salary->payment_date ? $salary->payment_date->format('d M Y') : '-' }}
                            </td>
                            <td>{{ $salary->remarks }}</td>
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
