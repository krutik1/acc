@extends('layouts.main')

@section('title', 'Create Trip')

@section('content')
<div class="page-header mb-4">
    <h1>Create Trip</h1>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.trips.store') }}">
                    @csrf

                    <!-- Driver Selection -->
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Driver <span class="text-danger">*</span></label>
                        <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id" required>
                            <option value="">Select Driver</option>
                            @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}" {{ old('user_id') == $driver->id ? 'selected' : '' }}>
                                    {{ $driver->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Trip Date -->
                    <div class="mb-3">
                        <label for="trip_date" class="form-label">Trip Date & Time <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control @error('trip_date') is-invalid @enderror" id="trip_date" name="trip_date" value="{{ old('trip_date', now()->format('Y-m-d\TH:i')) }}" required>
                        @error('trip_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Pickup Location -->
                    <div class="mb-3">
                        <label for="pickup_location" class="form-label">Pickup Location <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('pickup_location') is-invalid @enderror" id="pickup_location" name="pickup_location" value="{{ old('pickup_location') }}" required>
                        @error('pickup_location')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Drop Location -->
                    <div class="mb-3">
                        <label for="drop_location" class="form-label">Drop Location <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('drop_location') is-invalid @enderror" id="drop_location" name="drop_location" value="{{ old('drop_location') }}" required>
                        @error('drop_location')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control @error('quantity') is-invalid @enderror" id="quantity" name="quantity" value="{{ old('quantity') }}" required>
                            @error('quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="unit" class="form-label">Unit <span class="text-danger">*</span></label>
                            <select class="form-select @error('unit') is-invalid @enderror" id="unit" name="unit" required>
                                <option value="PCS" {{ old('unit') == 'PCS' ? 'selected' : '' }}>PCS</option>
                                <option value="Kg" {{ old('unit') == 'Kg' ? 'selected' : '' }}>Kg</option>
                                <option value="Tons" {{ old('unit') == 'Tons' ? 'selected' : '' }}>Tons</option>
                                <option value="Ltr" {{ old('unit') == 'Ltr' ? 'selected' : '' }}>Ltr</option>
                                <option value="Box" {{ old('unit') == 'Box' ? 'selected' : '' }}>Box</option>
                                <option value="Packet" {{ old('unit') == 'Packet' ? 'selected' : '' }}>Packet</option>
                            </select>
                            @error('unit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        </div>
                    </div>

                    <hr>
                    <h5 class="mb-3">Payment Override <small class="text-muted">(Optional - Leaves blank to use Driver Default)</small></h5>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="payment_mode" class="form-label">Mode Override</label>
                            <select class="form-select @error('payment_mode') is-invalid @enderror" id="payment_mode" name="payment_mode">
                                <option value="">Use Driver Default</option>
                                <option value="trip" {{ old('payment_mode') == 'trip' ? 'selected' : '' }}>Trip-based</option>
                                <option value="pcs" {{ old('payment_mode') == 'pcs' ? 'selected' : '' }}>PCS-based</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="trip_rate" class="form-label">Trip Rate Override (₹)</label>
                            <input type="number" step="0.01" class="form-control" id="trip_rate" name="trip_rate" value="{{ old('trip_rate') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="pcs_rate" class="form-label">PCS Rate Override (₹)</label>
                            <input type="number" step="0.01" class="form-control" id="pcs_rate" name="pcs_rate" value="{{ old('pcs_rate') }}">
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label for="description" class="form-label">Description (Goods/Materials)</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.trips.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Create Trip
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
