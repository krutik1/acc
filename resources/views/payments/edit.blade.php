@extends('layouts.main')

@section('title', 'Edit Payment')

@section('content')
<div class="page-header">
    <h1>Edit Payment</h1>
    <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to List
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('payments.update', $payment) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="payment_number" class="form-label">Payment Number</label>
                            <input type="text" class="form-control" value="{{ $payment->payment_number }}" readonly disabled>
                        </div>
                        <div class="col-md-6">
                            <label for="payment_date" class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('payment_date') is-invalid @enderror" 
                                   id="payment_date" name="payment_date" value="{{ old('payment_date', $payment->payment_date->format('Y-m-d')) }}" required>
                            @error('payment_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                            <label for="party_search" class="form-label">Party <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="text" class="form-control" id="party_search" 
                                       placeholder="Type to search party..." autocomplete="off"
                                       value="{{ old('party_search', $payment->party->name) }}">
                                <input type="hidden" name="party_id" id="party_id" value="{{ old('party_id', $payment->party_id) }}">
                                <div id="party_suggestions" class="list-group position-absolute w-100 shadow" 
                                     style="z-index: 1000; display: none; max-height: 200px; overflow-y: auto;">
                                </div>
                            </div>
                            @error('party_id')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="type" class="form-label">Payment Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                <option value="received" {{ old('type', $payment->type) == 'received' ? 'selected' : '' }}>Received (In)</option>
                                <option value="sent" {{ old('type', $payment->type) == 'sent' ? 'selected' : '' }}>Sent (Out)</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror" 
                                       id="amount" name="amount" value="{{ old('amount', $payment->amount) }}" required>
                            </div>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="mode" class="form-label">Payment Mode <span class="text-danger">*</span></label>
                            <select class="form-select @error('mode') is-invalid @enderror" id="mode" name="mode" required>
                                <option value="cash" {{ old('mode', $payment->mode) == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="cheque" {{ old('mode', $payment->mode) == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                <option value="bank_transfer" {{ old('mode', $payment->mode) == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer / NEFT / RTGS</option>
                                <option value="upi" {{ old('mode', $payment->mode) == 'upi' ? 'selected' : '' }}>UPI</option>
                                <option value="other" {{ old('mode', $payment->mode) == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('mode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="reference_number" class="form-label">Reference / Cheque No.</label>
                            <input type="text" class="form-control @error('reference_number') is-invalid @enderror" 
                                   id="reference_number" name="reference_number" value="{{ old('reference_number', $payment->reference_number) }}">
                            @error('reference_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="3">{{ old('notes', $payment->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">Update Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@include('partials.party-search-script')
@endsection
