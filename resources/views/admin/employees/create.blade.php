@extends('layouts.main')

@section('title', 'Add Employee')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center bg-white py-3">
                <h5 class="mb-0">Add New Employee</h5>
                <a href="{{ route('admin.employees.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back
                </a>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.employees.store') }}" method="POST">
                    @csrf
                    
                    <h6 class="mb-3 text-muted">Personal Information</h6>
                    <div class="mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                        @error('name') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                        @error('email') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <h6 class="mb-3 text-muted">Salary Details</h6>
                    <div class="mb-3">
                        <label class="form-label">Salary Type <span class="text-danger">*</span></label>
                        <select name="payment_mode" id="payment_mode" class="form-select" required onchange="toggleSalaryInputs()">
                            <option value="">Select Type</option>
                            <option value="fixed" {{ old('payment_mode') == 'fixed' ? 'selected' : '' }}>Fixed Salary</option>
                            <option value="pcs" {{ old('payment_mode') == 'pcs' ? 'selected' : '' }}>Piece Rate</option>
                            <!-- <option value="trip" {{ old('payment_mode') == 'trip' ? 'selected' : '' }}>Trip Based</option> -->
                        </select>
                        @error('payment_mode') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <div id="fixed_input" class="mb-3" style="display: none;">
                        <label class="form-label">Monthly Fixed Salary (₹) <span class="text-danger">*</span></label>
                        <input type="number" name="fixed_salary" class="form-control" step="0.01" min="0" value="{{ old('fixed_salary', 0) }}">
                        @error('fixed_salary') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <div id="piece_input" class="mb-3" style="display: none;">
                        <label class="form-label">Rate per Piece (₹) <span class="text-danger">*</span></label>
                        <input type="number" name="pcs_rate" class="form-control" step="0.01" min="0" value="{{ old('pcs_rate', 0) }}">
                        @error('pcs_rate') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                   {{-- <div id="trip_input" class="mb-3" style="display: none;">
                        <label class="form-label">Rate per Trip (₹) <span class="text-danger">*</span></label>
                        <input type="number" name="trip_rate" class="form-control" step="0.01" min="0" value="{{ old('trip_rate', 0) }}">
                        @error('trip_rate') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div> --}}

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Create Employee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleSalaryInputs() {
        const mode = document.getElementById('payment_mode').value;
        
        document.getElementById('fixed_input').style.display = (mode === 'fixed') ? 'block' : 'none';
        document.getElementById('piece_input').style.display = (mode === 'pcs') ? 'block' : 'none';
        // document.getElementById('trip_input').style.display = (mode === 'trip') ? 'block' : 'none';
    }

    // Run on load to set correct state
    toggleSalaryInputs();
</script>
@endsection
