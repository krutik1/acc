@extends('layouts.main')

@section('title', 'Monthly Expenses')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 text-gray-800">Monthly Expenses</h1>
    <a href="{{ route('expenses.reports.export', ['type' => 'monthly', 'year' => $year]) }}" class="btn btn-success">
        <i class="bi bi-file-earmark-pdf me-1"></i>Export PDF
    </a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('expenses.reports.monthly') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="year" class="form-label fw-bold small text-muted">Select Year</label>
                <select class="form-select @error('year') is-invalid @enderror" id="year" name="year" onchange="this.form.submit()">
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="unit_id" class="form-label fw-bold small text-muted">Select Unit</label>
                <select class="form-select" id="unit_id" name="unit_id" onchange="this.form.submit()">
                    <option value="">All Units</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" {{ $unitId == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-primary">Monthly Trend</h6>
            </div>
            <div class="card-body">
                <canvas id="monthlyChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-primary">Summary</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <h5 class="text-muted small text-uppercase">Total Expenses ({{ $year }})</h5>
                    <h2 class="font-weight-bold text-dark">₹ {{ number_format($totalExpensesYear, 2) }}</h2>
                </div>
                 <div class="table-responsive">
                    <table class="table table-sm table-borderless">
                        <tbody>
                            @foreach($chartData['labels'] as $index => $month)
                                @if($chartData['data'][$index] > 0)
                                <tr>
                                    <td>{{ $month }}</td>
                                    <td class="text-end fw-bold">₹ {{ number_format($chartData['data'][$index], 2) }}</td>
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartData['labels']) !!},
            datasets: [{
                label: 'Expenses',
                data: {!! json_encode($chartData['data']) !!},
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value, index, values) {
                            return '₹' + value;
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
</script>
@endpush
@endsection
