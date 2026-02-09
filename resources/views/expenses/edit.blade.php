@extends('layouts.main')

@section('title', 'Edit Expense')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 text-gray-800">Edit Expense</h1>
    <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="{{ route('expenses.update', $expense) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="date" class="form-label fw-bold">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('date') is-invalid @enderror" id="date" name="date" value="{{ old('date', $expense->date->format('Y-m-d')) }}" required>
                            @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="unit_id" class="form-label fw-bold">Unit <span class="text-danger">*</span></label>
                            <select class="form-select @error('unit_id') is-invalid @enderror" id="unit_id" name="unit_id" required>
                                <option value="">Select Unit...</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" {{ old('unit_id', $expense->unit_id) == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                                @endforeach
                            </select>
                            @error('unit_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="expense_category_id" class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                            <select class="form-select @error('expense_category_id') is-invalid @enderror" id="expense_category_id" name="expense_category_id" required>
                                <option value="">Select Category...</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('expense_category_id', $expense->expense_category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('expense_category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="amount" class="form-label fw-bold">Amount (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" value="{{ old('amount', $expense->amount) }}" required placeholder="0.00">
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="payment_mode" class="form-label fw-bold">Payment Mode <span class="text-danger">*</span></label>
                            <select class="form-select @error('payment_mode') is-invalid @enderror" id="payment_mode" name="payment_mode" required onchange="toggleBankFields()">
                                <option value="">Select Mode...</option>
                                <option value="Cash" {{ old('payment_mode', $expense->payment_mode) == 'Cash' ? 'selected' : '' }}>Cash</option>
                                <option value="Bank" {{ old('payment_mode', $expense->payment_mode) == 'Bank' ? 'selected' : '' }}>Bank</option>
                                <option value="UPI" {{ old('payment_mode', $expense->payment_mode) == 'UPI' ? 'selected' : '' }}>UPI</option>
                                <option value="Cheque" {{ old('payment_mode', $expense->payment_mode) == 'Cheque' ? 'selected' : '' }}>Cheque</option>
                            </select>
                            @error('payment_mode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                         <div class="col-md-6 bank-field" style="display: none;">
                            <label for="bank_name" class="form-label fw-bold">Bank Name / Account</label>
                            <input type="text" class="form-control @error('bank_name') is-invalid @enderror" id="bank_name" name="bank_name" value="{{ old('bank_name', $expense->bank_name) }}" placeholder="e.g. HDFC Bank">
                            @error('bank_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="reference_no" class="form-label fw-bold">Reference / Cheque No.</label>
                            <input type="text" class="form-control @error('reference_no') is-invalid @enderror" id="reference_no" name="reference_no" value="{{ old('reference_no', $expense->reference_no) }}" placeholder="Optional">
                            @error('reference_no')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                         <div class="col-md-6">
                            <label for="attachment" class="form-label fw-bold">Attachment (Optional)</label>
                            <input type="file" class="form-control @error('attachment') is-invalid @enderror" id="attachment" name="attachment" accept="image/*,application/pdf">
                            <div class="form-text small">Accepted: JPG, PNG, PDF. Max 2MB. Leave empty to keep: 
                                @if($expense->attachment_path)
                                    <a href="{{ Storage::url($expense->attachment_path) }}" target="_blank">Current Attachment</a>
                                @else
                                    None
                                @endif
                            </div>
                            @error('attachment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-bold">Description / Notes</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $expense->description) }}</textarea>
                         @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <a href="{{ route('expenses.index') }}" class="btn btn-light me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4">Update Expense</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleBankFields() {
        const mode = document.getElementById('payment_mode').value;
        const bankFields = document.querySelectorAll('.bank-field');
        
        // Show bank fields for Bank or Cheque
        if (mode === 'Bank' || mode === 'Cheque') {
            bankFields.forEach(el => el.style.display = 'block');
        } else {
            bankFields.forEach(el => el.style.display = 'none');
        }
    }
    
    // Run on load
    document.addEventListener('DOMContentLoaded', toggleBankFields);
</script>
@endpush
@endsection
