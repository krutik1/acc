@extends('layouts.main')

@section('title', 'Edit Bank Account')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Edit Bank Account</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('bank-accounts.update', $bankAccount) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <!-- Bank Name -->
                            <div class="col-md-6">
                                <label for="bank_name" class="form-label">Bank Name</label>
                                <input type="text" class="form-control" id="bank_name" name="bank_name" value="{{ old('bank_name', $bankAccount->bank_name) }}" required>
                            </div>

                            <!-- Account Holder Name -->
                            <div class="col-md-6">
                                <label for="account_holder_name" class="form-label">Holder Name</label>
                                <input type="text" class="form-control" id="account_holder_name" name="account_holder_name" value="{{ old('account_holder_name', $bankAccount->account_holder_name) }}" required>
                            </div>

                            <!-- Account Number (Readonly) -->
                            <div class="col-md-6">
                                <label for="account_number" class="form-label">Account Number</label>
                                <input type="text" class="form-control bg-light" id="account_number" name="account_number" value="{{ $bankAccount->account_number }}" readonly>
                                <small class="text-muted">Cannot be changed.</small>
                            </div>

                            <!-- IFSC Code -->
                            <div class="col-md-6">
                                <label for="ifsc_code" class="form-label">IFSC Code</label>
                                <input type="text" class="form-control text-uppercase" id="ifsc_code" name="ifsc_code" value="{{ old('ifsc_code', $bankAccount->ifsc_code) }}">
                            </div>

                            <!-- Account Type -->
                            <div class="col-md-6">
                                <label for="account_type" class="form-label">Account Type</label>
                                <select class="form-select" id="account_type" name="account_type">
                                    <option value="Saving" {{ $bankAccount->account_type == 'Saving' ? 'selected' : '' }}>Saving</option>
                                    <option value="Current" {{ $bankAccount->account_type == 'Current' ? 'selected' : '' }}>Current</option>
                                    <option value="OD" {{ $bankAccount->account_type == 'OD' ? 'selected' : '' }}>OD / CC</option>
                                    <option value="Cash" {{ $bankAccount->account_type == 'Cash' ? 'selected' : '' }}>Cash Account</option>
                                </select>
                            </div>

                            <!-- Opening Balance (Readonly) -->
                            <div class="col-md-6">
                                <label for="opening_balance" class="form-label">Opening Balance</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control bg-light" value="{{ $bankAccount->opening_balance }}" readonly>
                                </div>
                                <small class="text-muted">Cannot be changed after creation.</small>
                            </div>

                            <!-- Status -->
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="is_active">
                                    <option value="1" {{ $bankAccount->is_active ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ !$bankAccount->is_active ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>

                            <!-- Action Buttons -->
                            <div class="col-12 text-end mt-4">
                                <a href="{{ route('bank-accounts.index') }}" class="btn btn-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update Account</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
