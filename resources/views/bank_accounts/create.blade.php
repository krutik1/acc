@extends('layouts.main')

@section('title', 'Add Bank Account')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 fw-bold text-dark">Add New Bank Account</h5>
            </div>
            <div class="card-body p-4">
                @if ($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('bank-accounts.store') }}" class="needs-validation" novalidate>
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="bank_name" class="form-label fw-semibold">Bank Name *</label>
                            <input type="text" class="form-control" id="bank_name" name="bank_name" value="{{ old('bank_name') }}" required placeholder="e.g. HDFC Bank">
                        </div>

                        <div class="col-md-6">
                            <label for="account_holder_name" class="form-label fw-semibold">Account Holder Name *</label>
                            <input type="text" class="form-control" id="account_holder_name" name="account_holder_name" value="{{ old('account_holder_name') }}" required placeholder="e.g. John Doe">
                        </div>

                        <div class="col-md-6">
                            <label for="account_number" class="form-label fw-semibold">Account Number *</label>
                            <input type="text" class="form-control" id="account_number" name="account_number" value="{{ old('account_number') }}" required placeholder="e.g. 501002345678">
                        </div>

                        <div class="col-md-6">
                            <label for="ifsc_code" class="form-label fw-semibold">IFSC Code</label>
                            <input type="text" class="form-control text-uppercase" id="ifsc_code" name="ifsc_code" value="{{ old('ifsc_code') }}" placeholder="e.g. HDFC0001234">
                        </div>

                        <div class="col-md-6">
                            <label for="account_type" class="form-label fw-semibold">Account Type</label>
                            <select class="form-select" id="account_type" name="account_type">
                                <option value="Saving" {{ old('account_type') == 'Saving' ? 'selected' : '' }}>Saving</option>
                                <option value="Current" {{ old('account_type') == 'Current' ? 'selected' : '' }}>Current</option>
                                <option value="OD" {{ old('account_type') == 'OD' ? 'selected' : '' }}>OD / CC</option>
                                <option value="Cash" {{ old('account_type') == 'Cash' ? 'selected' : '' }}>Cash Account</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="opening_balance" class="form-label fw-semibold">Opening Balance *</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" step="0.01" class="form-control" id="opening_balance" name="opening_balance" value="{{ old('opening_balance', 0) }}" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="opening_balance_date" class="form-label fw-semibold">Opening Balance Date *</label>
                            <input type="date" class="form-control" id="opening_balance_date" name="opening_balance_date" value="{{ old('opening_balance_date', date('Y-m-d')) }}" required>
                        </div>

                        <div class="col-12 mt-4 text-end border-top pt-3">
                            <a href="{{ route('bank-accounts.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i> Create Bank Account
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
