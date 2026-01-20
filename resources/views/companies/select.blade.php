@extends('layouts.main')

@section('title', 'Select Company')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="bi bi-building me-2"></i>Select Your Company</h4>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    @if($companies->isEmpty())
                        <div class="alert alert-info">
                            <p class="mb-3">No companies found. Please create a company first.</p>
                            <a href="{{ route('companies.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-lg me-1"></i>Create Company
                            </a>
                        </div>
                    @else
                        <form action="{{ route('companies.select.store') }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label for="company_id" class="form-label">Choose a company to continue:</label>
                                <select name="company_id" id="company_id" class="form-select form-select-lg @error('company_id') is-invalid @enderror" required>
                                    <option value="">-- Select Company --</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                            {{ $company->name }}
                                            @if($company->is_default)
                                                (Default)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('company_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-check-circle me-2"></i>Continue
                                </button>
                                <a href="{{ route('companies.create') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-plus-lg me-1"></i>Create New Company
                                </a>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
