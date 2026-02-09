@extends('layouts.main')

@section('title', 'Unit-wise Expenses')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 text-gray-800">Unit-wise Expenses</h1>
    <a href="{{ route('expenses.reports.export', ['type' => 'unit', 'from_date' => $fromDate, 'to_date' => $toDate]) }}" class="btn btn-success">
        <i class="bi bi-file-earmark-pdf me-1"></i>Export PDF
    </a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('expenses.reports.unit-wise') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="from_date" class="form-label fw-bold small text-muted">From Date</label>
                <input type="date" class="form-control" id="from_date" name="from_date" value="{{ $fromDate }}">
            </div>
            <div class="col-md-3">
                <label for="to_date" class="form-label fw-bold small text-muted">To Date</label>
                <input type="date" class="form-control" id="to_date" name="to_date" value="{{ $toDate }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table align-items-center table-flush table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th scope="col" class="ps-4">Unit Name</th>
                    <th scope="col" class="text-center">Total Transactions</th>
                    <th scope="col" class="text-end pe-4">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @php $grandTotal = 0; $grandCount = 0; @endphp
                @forelse($unitExpenses as $unit)
                @php 
                    $amount = $unit->expenses_sum_amount ?? 0;
                    $count = $unit->expenses_count ?? 0;
                    $grandTotal += $amount;
                    $grandCount += $count;
                @endphp
                <tr>
                    <td class="ps-4 fw-bold text-dark">{{ $unit->name }}</td>
                    <td class="text-center">{{ $count }}</td>
                    <td class="text-end pe-4 fw-bold">₹ {{ number_format($amount, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center py-5">
                        <div class="text-muted">
                            <i class="bi bi-shop display-4 mb-3 d-block opacity-50"></i>
                            <p class="h5">No data found</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($unitExpenses->count() > 0)
            <tfoot class="bg-light">
                <tr>
                    <td class="ps-4 fw-bold py-3">Total</td>
                    <td class="text-center fw-bold py-3">{{ $grandCount }}</td>
                    <td class="text-end pe-4 fw-bold py-3">₹ {{ number_format($grandTotal, 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
