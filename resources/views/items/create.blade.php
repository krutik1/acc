@extends('layouts.main')

@section('title', 'Add Item')

@section('content')
<div class="page-header">
    <h1>Add New Item</h1>
    <a href="{{ route('items.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to List
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Item Details</div>
            <div class="card-body">
                <form action="{{ route('items.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Item Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" required autofocus>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="hsn_code" class="form-label">HSN Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('hsn_code') is-invalid @enderror" 
                                   id="hsn_code" name="hsn_code" value="{{ old('hsn_code') }}" required>
                            @error('hsn_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="unit" class="form-label">Unit <span class="text-danger">*</span></label>
                            <select class="form-select @error('unit') is-invalid @enderror" id="unit" name="unit" required>
                                <option value="" disabled selected>Select Unit</option>
                                @foreach(App\Models\ChallanItem::UNITS as $key => $label)
                                    <option value="{{ $key }}" {{ old('unit', 'pcs') == $key ? 'selected' : '' }}>
                                        {{ $label }} ({{ $key }})
                                    </option>
                                @endforeach
                            </select>
                            @error('unit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="rate" class="form-label">Default Rate (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" class="form-control @error('rate') is-invalid @enderror" 
                                   id="rate" name="rate" value="{{ old('rate', '0.00') }}" required>
                            @error('rate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <a href="{{ route('items.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Save Item
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
