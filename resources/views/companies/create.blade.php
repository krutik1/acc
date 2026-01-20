@extends('layouts.main')

@section('title', 'Create Company')

@section('content')
<div class="page-header">
    <h1>Create Company</h1>
    <a href="{{ route('companies.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to List
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('companies.store') }}" method="POST">
            @csrf
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Company Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                           id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="gst_number" class="form-label">GST Number</label>
                    <input type="text" class="form-control @error('gst_number') is-invalid @enderror" 
                           id="gst_number" name="gst_number" value="{{ old('gst_number') }}" 
                           placeholder="e.g., 24BCYPB6027P1Z1" maxlength="15">
                    @error('gst_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="state_code" class="form-label">State Code</label>
                    <input type="text" class="form-control @error('state_code') is-invalid @enderror" 
                           id="state_code" name="state_code" value="{{ old('state_code') }}" 
                           placeholder="e.g., 24-GJ" maxlength="10">
                    @error('state_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="mobile_numbers" class="form-label">Mobile Numbers</label>
                    <input type="text" class="form-control @error('mobile_numbers') is-invalid @enderror" 
                           id="mobile_numbers" name="mobile_numbers" value="{{ old('mobile_numbers') }}" 
                           placeholder="e.g., 9712435347- 8487962263">
                    @error('mobile_numbers')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Separate multiple numbers with comma or dash</div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                <textarea class="form-control @error('address') is-invalid @enderror" 
                          id="address" name="address" rows="3" required>{{ old('address') }}</textarea>
                @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <hr>
            
            <h5 class="mb-3">Bank Details</h5>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="bank_name" class="form-label">Bank Name</label>
                    <input type="text" class="form-control @error('bank_name') is-invalid @enderror" 
                           id="bank_name" name="bank_name" value="{{ old('bank_name') }}">
                    @error('bank_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="ifsc_code" class="form-label">IFSC Code</label>
                    <input type="text" class="form-control @error('ifsc_code') is-invalid @enderror" 
                           id="ifsc_code" name="ifsc_code" value="{{ old('ifsc_code') }}" 
                           placeholder="e.g., UTIB0004401" maxlength="11">
                    @error('ifsc_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="account_number" class="form-label">Account Number</label>
                    <input type="text" class="form-control @error('account_number') is-invalid @enderror" 
                           id="account_number" name="account_number" value="{{ old('account_number') }}">
                    @error('account_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="mb-3">
                <label for="terms_conditions" class="form-label">Terms & Conditions</label>
                <textarea class="form-control @error('terms_conditions') is-invalid @enderror" 
                          id="terms_conditions" name="terms_conditions" rows="5">{{ old('terms_conditions') }}</textarea>
                @error('terms_conditions')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Enter terms and conditions (one per line)</div>
            </div>
            
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_default" name="is_default" value="1" {{ old('is_default') ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_default">
                        Set as default company
                    </label>
                </div>
            </div>
            
            <hr>
            
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('companies.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>Create Company
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
