@extends('layouts.main')

@section('title', 'Party Statement')

@section('content')
<div class="page-header">
    <h1>Party Statement</h1>
    <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to Reports
    </a>
</div>

<div class="row">
    <!-- Filter Sidebar -->
    <div class="col-md-3">
        <div class="card mb-4">
            <div class="card-header">Filter Options</div>
            <div class="card-body">
                <form action="{{ route('reports.party-statement') }}" method="GET">
                    <div class="mb-3">
                        <label class="form-label">Select Party</label>
                        <!-- Reusing the auto-suggestion logic here would be ideal, but for now a select is safer for quick implementation -->
                        <select name="party_id" class="form-select select2" required>
                            <option value="">Select Party</option>
                            @foreach($parties as $p)
                                <option value="{{ $p->id }}" {{ $party && $party->id == $p->id ? 'selected' : '' }}>
                                    {{ $p->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">From Date</label>
                        <input type="date" class="form-control" name="from_date" value="{{ $fromDate }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">To Date</label>
                        <input type="date" class="form-control" name="to_date" value="{{ $toDate }}">
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Generate Statement</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Statement View -->
    <div class="col-md-9">
        @if($party)
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">{{ $party->name }}</h5>
                        <small class="text-muted">{{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}</small>
                    </div>
                    <div>
                        <button onclick="window.print()" class="btn btn-outline-dark btn-sm">
                            <i class="bi bi-printer me-1"></i>Print
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Ref #</th>
                                    <th>Description</th>
                                    <th class="text-end">Debit (₹)</th>
                                    <th class="text-end">Credit (₹)</th>
                                    <th class="text-end">Balance (₹)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Opening Balance -->
                                <tr class="table-secondary fw-bold">
                                    <td colspan="3">Opening Balance</td>
                                    <td class="text-end">
                                        {{ $openingBalance > 0 ? number_format($openingBalance, 2) : '' }}
                                    </td>
                                    <td class="text-end">
                                        {{ $openingBalance < 0 ? number_format(abs($openingBalance), 2) : '' }}
                                    </td>
                                    <td class="text-end">
                                        {{ number_format(abs($openingBalance), 2) }} {{ $openingBalance >= 0 ? 'Dr' : 'Cr' }}
                                    </td>
                                </tr>

                                @php 
                                    $runningBalance = $openingBalance; 
                                    $totalDebit = 0;
                                    $totalCredit = 0;
                                @endphp

                                @forelse($transactions as $trx)
                                    @php
                                        // Debit increases balance (Receivable), Credit decreases it
                                        $runningBalance += $trx['debit'] - $trx['credit'];
                                        $totalDebit += $trx['debit'];
                                        $totalCredit += $trx['credit'];
                                    @endphp
                                    <tr>
                                        <td>{{ $trx['date']->format('d M Y') }}</td>
                                        <td>
                                            @if(isset($trx['url']))
                                                <a href="{{ $trx['url'] }}" class="text-decoration-none">{{ $trx['ref_number'] }}</a>
                                            @else
                                                {{ $trx['ref_number'] }}
                                            @endif
                                        </td>
                                        <td>{{ $trx['description'] }}</td>
                                        <td class="text-end text-danger">
                                            {{ $trx['debit'] > 0 ? number_format($trx['debit'], 2) : '-' }}
                                        </td>
                                        <td class="text-end text-success">
                                            {{ $trx['credit'] > 0 ? number_format($trx['credit'], 2) : '-' }}
                                        </td>
                                        <td class="text-end fw-bold">
                                            {{ number_format(abs($runningBalance), 2) }} {{ $runningBalance >= 0 ? 'Dr' : 'Cr' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">No transactions found in this period.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="3" class="text-end">Current Totals</td>
                                    <td class="text-end text-danger">{{ number_format($totalDebit, 2) }}</td>
                                    <td class="text-end text-success">{{ number_format($totalCredit, 2) }}</td>
                                    <td class="text-end">{{ number_format(abs($runningBalance), 2) }} {{ $runningBalance >= 0 ? 'Dr' : 'Cr' }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-info text-center py-5">
                <i class="bi bi-search display-1 mb-3"></i>
                <p class="fs-4">Select a party to view their statement.</p>
            </div>
        @endif
    </div>
</div>

<!-- Simple Print Styles -->
<style>
@media print {
    body * {
        visibility: hidden;
    }
    .card-header button, .btn, form {
        display: none !important;
    }
    .col-md-3 {
        display: none;
    }
    .col-md-9 {
        width: 100%;
        flex: 0 0 100%;
        max-width: 100%;
    }
    .card {
        border: none !important;
    }
    .card-body, .card-body * {
        visibility: visible;
    }
    .card-header {
        visibility: visible;
        background: none !important;
        border-bottom: 2px solid #000 !important;
    }
    .card-header h5, .card-header small {
        visibility: visible;
    }
    .card-body {
        position: absolute;
        left: 0;
        top: 100px;
        width: 100%;
    }
    .card-header {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
}
</style>
@endsection
