@extends('layouts.main')

@section('title', 'Company: ' . $company->name)

@section('content')
<div class="page-header">
    <h1>Company: {{ $company->name }}</h1>
    <div>
        <a href="{{ route('companies.edit', $company) }}" class="btn btn-outline-primary me-2">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        <a href="{{ route('companies.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">Company Details</div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <small class="text-muted">Company Name</small>
                        <p class="fw-bold fs-5">{{ $company->name }}</p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Status</small>
                        <p>
                            @if($company->is_default)
                                <span class="badge bg-success">Default Company</span>
                            @else
                                <span class="badge bg-secondary">-</span>
                            @endif
                        </p>
                    </div>
                </div>
                
                <div class="mb-3">
                    <small class="text-muted">Address</small>
                    <p>{{ $company->address }}</p>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <small class="text-muted">GST Number</small>
                        <p>{{ $company->gst_number ?: '-' }}</p>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">State Code</small>
                        <p>{{ $company->state_code ?: '-' }}</p>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Mobile Numbers</small>
                        <p>{{ $company->mobile_numbers ?: '-' }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">Bank Details</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <small class="text-muted">Bank Name</small>
                        <p>{{ $company->bank_name ?: '-' }}</p>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">IFSC Code</small>
                        <p>{{ $company->ifsc_code ?: '-' }}</p>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Account Number</small>
                        <p>{{ $company->account_number ?: '-' }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        @if($company->terms_conditions)
        <div class="card">
            <div class="card-header">Terms & Conditions</div>
            <div class="card-body">
                <pre class="mb-0" style="white-space: pre-wrap; font-family: inherit;">{{ $company->terms_conditions }}</pre>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
