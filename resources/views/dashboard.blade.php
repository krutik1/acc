@extends('layouts.main')

@section('title', 'Dashboard')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold mb-0 text-dark">Dashboard</h2>
    </div>
</div>

<!-- Grid Cards -->
<div class="row g-4 mb-4">
    <!-- Card 1: Customers -->
    <div class="col-xl-3 col-md-6">
        <div class="card h-100 p-3">
            <div class="d-flex align-items-center mb-2">
                <div class="icon-shape rounded-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background-color: var(--primary-color);">
                    <i class="bi bi-people text-white fs-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <p class="text-muted mb-0" style="font-size: 0.9rem;">Total Customers</p>
                <h3 class="fw-bold mb-0" style="color: var(--primary-color);">{{ $stats['parties'] ?? 10 }}</h3>
            </div>
        </div>
    </div>
    
    <!-- Card 2: Vendors -->
    <div class="col-xl-3 col-md-6">
        <div class="card h-100 p-3">
            <div class="d-flex align-items-center mb-2">
                <div class="icon-shape rounded-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background-color: var(--secondary-color);">
                    <i class="bi bi-archive text-white fs-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <p class="text-muted mb-0" style="font-size: 0.9rem;">Total Challans</p>
                <h3 class="fw-bold mb-0" style="color: var(--warning-color);">{{ $stats['challans'] ?? 8 }}</h3>
            </div>
        </div>
    </div>
    
    <!-- Card 3: Invoices -->
    <div class="col-xl-3 col-md-6">
        <div class="card h-100 p-3">
            <div class="d-flex align-items-center mb-2">
                <div class="icon-shape rounded-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background-color: var(--warning-color);">
                    <i class="bi bi-file-text text-white fs-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <p class="text-muted mb-0" style="font-size: 0.9rem;">Total Invoices</p>
                <h3 class="fw-bold mb-0" style="color: var(--warning-color);">{{ $stats['invoices'] ?? 16 }}</h3>
            </div>
        </div>
    </div>
    
    <!-- Card 4: Bills -->
    <div class="col-xl-3 col-md-6">
        <div class="card h-100 p-3">
            <div class="d-flex align-items-center mb-2">
                <div class="icon-shape rounded-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background-color: var(--danger-color);">
                    <i class="bi bi-receipt text-white fs-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <p class="text-muted mb-0" style="font-size: 0.9rem;">Pending Actions</p>
                <h3 class="fw-bold mb-0" style="color: var(--danger-color);">{{ $stats['pending_challans'] ?? 16 }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Account Balance Section (Mockup based on image) -->
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header border-0 pb-0">
                <span class="border-start border-4 border-primary ps-2">Financial Overview</span>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                         <div class="p-3 bg-success bg-opacity-10 rounded-3 text-center">
                            <p class="text-muted mb-1">Income</p>
                            <h3 class="fw-bold text-success mb-0">₹ {{ number_format($stats['total_income'], 2) }}</h3>
                         </div>
                    </div>
                    <div class="col-md-6">
                         <div class="p-3 bg-danger bg-opacity-10 rounded-3 text-center">
                            <p class="text-muted mb-1">Expense</p>
                            <h3 class="fw-bold text-danger mb-0">₹ {{ number_format($stats['total_expense'], 2) }}</h3>
                         </div>
                    </div>
                </div>
                 <div class="mt-4">
                    <h6 class="text-muted mb-2">Summary</h6>
                    <p class="text-muted small">
                        This overview shows the total income from received payments and total expenses from sent payments for the current company.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Cashflow Chart (Dummy Placeholder) -->
    <!-- Quick Actions -->
    <div class="col-lg-5">
        <div class="card h-100">
             <div class="card-header border-0 pb-0">
                <span class="border-start border-4 border-primary ps-2">Quick Actions</span>
            </div>
            <div class="card-body">
                 <div class="row g-3">
                     <div class="col-6">
                         <a href="{{ route('invoices.create') }}" class="card btn btn-light h-100 p-3 d-flex flex-column align-items-center justify-content-center gap-2 border-0 shadow-sm" style="transition: transform 0.2s;">
                             <div class="icon-shape bg-primary bg-opacity-10 text-primary rounded-3 p-3 mb-2">
                                 <i class="bi bi-plus-lg fs-4"></i>
                             </div>
                             <span class="fw-bold text-dark">New Invoice</span>
                         </a>
                     </div>
                     <div class="col-6">
                        <a href="{{ route('parties.create') }}" class="card btn btn-light h-100 p-3 d-flex flex-column align-items-center justify-content-center gap-2 border-0 shadow-sm" style="transition: transform 0.2s;">
                            <div class="icon-shape bg-secondary bg-opacity-10 text-secondary rounded-3 p-3 mb-2">
                                <i class="bi bi-person-plus fs-4"></i>
                            </div>
                            <span class="fw-bold text-dark">Add Party</span>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('challans.create') }}" class="card btn btn-light h-100 p-3 d-flex flex-column align-items-center justify-content-center gap-2 border-0 shadow-sm" style="transition: transform 0.2s;">
                            <div class="icon-shape bg-warning bg-opacity-10 text-warning rounded-3 p-3 mb-2">
                                <i class="bi bi-file-earmark-plus fs-4"></i>
                            </div>
                            <span class="fw-bold text-dark">Create Challan</span>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('reports.index') }}" class="card btn btn-light h-100 p-3 d-flex flex-column align-items-center justify-content-center gap-2 border-0 shadow-sm" style="transition: transform 0.2s;">
                            <div class="icon-shape bg-danger bg-opacity-10 text-danger rounded-3 p-3 mb-2">
                                <i class="bi bi-graph-up fs-4"></i>
                            </div>
                            <span class="fw-bold text-dark">View Reports</span>
                        </a>
                    </div>
                 </div>
            </div>
        </div>
    </div>
</div>
@endsection
