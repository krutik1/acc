@extends('layouts.main')

@section('title', 'Reports')

@section('content')
<div class="page-header">
    <h1>Reports & Analytics</h1>
    <div class="col-md-3">
        <form action="{{ route('reports.index') }}" method="GET">
            <div class="input-group">
                <select name="year" class="form-select" onchange="this.form.submit()">
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>Year {{ $y }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-outline-primary">Go</button>
            </div>
        </form>
    </div>
</div>

<div class="row mb-4">
    <!-- Summary Cards -->
    <div class="col-md-6 mb-3">
        <div class="card h-100 p-3">
            <div class="d-flex align-items-center mb-2">
                <div class="icon-shape rounded-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background-color: var(--primary-color);">
                    <i class="bi bi-currency-rupee text-white fs-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <p class="text-muted mb-0" style="font-size: 0.9rem;">Total Sales ({{ $year }})</p>
                <h3 class="fw-bold mb-0" style="color: var(--primary-color);">₹{{ number_format($totalSalesYear, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card h-100 p-3">
            <div class="d-flex align-items-center mb-2">
                <div class="icon-shape rounded-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background-color: var(--secondary-color);">
                    <i class="bi bi-graph-up-arrow text-white fs-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <p class="text-muted mb-0" style="font-size: 0.9rem;">Average Monthly Sales</p>
                <h3 class="fw-bold mb-0" style="color: var(--secondary-color);">₹{{ number_format($averageMonthlySales, 2) }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Chart Section -->
    <div class="col-md-12 mb-4">
        <div class="card h-100">
            <div class="card-header border-0 pb-0">
                <span class="border-start border-4 border-primary ps-2">Monthly Sales Trend</span>
            </div>
            <div class="card-body">
                <div style="height: 300px;">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Table Section -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header border-0 pb-0">
                <span class="border-start border-4 border-warning ps-2">Detailed Monthly Breakdown</span>
            </div>
            <div class="card-body p-0 mt-3">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th class="ps-4">Month</th>
                            <th class="text-center">Invoice Count</th>
                            <th class="text-end">Total Sales</th>
                            <th class="text-end pe-4">% of Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($detailedData as $data)
                        <tr>
                            <td class="ps-4 fw-bold">{{ $data['month'] }}</td>
                            <td class="text-center"><span class="badge bg-light text-dark border">{{ $data['count'] }}</span></td>
                            <td class="text-end">₹{{ number_format($data['sales'], 2) }}</td>
                            <td class="text-end pe-4">
                                @if($totalSalesYear > 0)
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ ($data['sales'] / $totalSalesYear) * 100 }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ number_format(($data['sales'] / $totalSalesYear) * 100, 1) }}%</small>
                                @else
                                    0%
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-light fw-bold">
                            <td class="ps-4">Total</td>
                            <td class="text-center">{{ collect($detailedData)->sum('count') }}</td>
                            <td class="text-end">₹{{ number_format($totalSalesYear, 2) }}</td>
                            <td class="text-end pe-4">100%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('salesChart').getContext('2d');
    
    // Data from server
    const labels = @json($chartData['labels']);
    const data = @json($chartData['data']);
    
    // AccountGo Green Gradient
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(111, 217, 67, 0.5)'); // #6fd943 with opacity
    gradient.addColorStop(1, 'rgba(111, 217, 67, 0.0)');

    new Chart(ctx, {
        type: 'line', // Line chart looks better for trends
        data: {
            labels: labels,
            datasets: [{
                label: 'Monthly Sales (₹)',
                data: data,
                backgroundColor: gradient,
                borderColor: '#6fd943', // Brand Green
                borderWidth: 2,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#6fd943',
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true,
                tension: 0.4 // Smooth curves
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#fff',
                    titleColor: '#000',
                    bodyColor: '#000',
                    borderColor: '#edf2f9',
                    borderWidth: 1,
                    padding: 10,
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
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        borderDash: [5, 5],
                        color: '#edf2f9'
                    },
                    ticks: {
                        callback: function(value) {
                            return '₹' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush
