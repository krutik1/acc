@extends('layouts.main')

@section('title', 'Edit Driver')

@section('content')
<div class="page-header mb-4">
    <h1>Edit Driver</h1>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.drivers.update', $driver) }}">
                    @csrf
                    @method('PUT')

                    <!-- Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $driver->name) }}" required autofocus>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $driver->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>
                    <h5 class="mb-3">Payment Settings</h5>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="payment_mode" class="form-label">Default Payment Mode</label>
                            <select class="form-select @error('payment_mode') is-invalid @enderror" id="payment_mode" name="payment_mode">
                                <option value="trip" {{ old('payment_mode', $driver->payment_mode) == 'trip' ? 'selected' : '' }}>Trip-based</option>
                                <option value="pcs" {{ old('payment_mode', $driver->payment_mode) == 'pcs' ? 'selected' : '' }}>PCS-based</option>
                            </select>
                            @error('payment_mode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="trip_rate" class="form-label">Trip Rate (₹)</label>
                            <input type="number" step="0.01" class="form-control @error('trip_rate') is-invalid @enderror" id="trip_rate" name="trip_rate" value="{{ old('trip_rate', $driver->trip_rate) }}">
                            @error('trip_rate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="pcs_rate" class="form-label">PCS Rate (₹)</label>
                            <input type="number" step="0.01" class="form-control @error('pcs_rate') is-invalid @enderror" id="pcs_rate" name="pcs_rate" value="{{ old('pcs_rate', $driver->pcs_rate) }}">
                            @error('pcs_rate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr>
                    <h5 class="mb-3">Change Password <small class="text-muted">(Optional)</small></h5>

                    <!-- Password (Optional) -->
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" autocomplete="new-password">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.drivers.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Update Driver
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
