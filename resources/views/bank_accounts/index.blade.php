@extends('layouts.main')

@section('title', 'Bank Accounts')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Bank Accounts</h1>
    <a href="{{ route('bank-accounts.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Add Account
    </a>
</div>

<div class="row g-4">
    @forelse ($bankAccounts as $account)
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="card-title fw-bold text-dark mb-1">{{ $account->bank_name }}</h5>
                            <p class="text-muted small mb-0">{{ $account->account_number }}</p>
                        </div>
                        <span class="badge {{ $account->is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} rounded-pill px-3 py-2">
                            {{ $account->account_type }}
                        </span>
                    </div>
                    
                    <div class="mt-4 mb-4">
                        <p class="text-muted small text-uppercase fw-semibold mb-1">Current Balance</p>
                        <h2 class="display-6 fw-bold {{ $account->current_balance >= 0 ? 'text-success' : 'text-danger' }}">
                            ₹{{ number_format($account->current_balance, 2) }}
                        </h2>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-between align-items-center pt-3 border-top">
                        <a href="{{ route('bank-accounts.show', $account) }}" class="btn btn-outline-primary btn-sm stretched-linkz" style="position: relative; z-index: 2;">
                            View Statement <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                        <a href="{{ route('bank-accounts.edit', $account) }}" class="btn btn-light btn-sm text-muted" style="position: relative; z-index: 2;">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="text-center py-5">
                <div class="mb-3 text-muted">
                    <i class="bi bi-bank" style="font-size: 3rem;"></i>
                </div>
                <h4 class="fw-normal text-muted">No Bank Accounts Found</h4>
                <p class="text-secondary mb-4">Get started by adding your first bank account to track specific ledgers.</p>
                <a href="{{ route('bank-accounts.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i> Add Account
                </a>
            </div>
        </div>
    @endforelse
</div>

<style>
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
    .transition-all {
        transition: all 0.3s ease;
    }
    .bg-success-subtle {
        background-color: #d1e7dd;
    }
    .text-success {
        color: #198754 !important;
    }
    .bg-danger-subtle {
        background-color: #f8d7da;
    }
    .text-danger {
        color: #dc3545 !important;
    }
</style>
@endsection
