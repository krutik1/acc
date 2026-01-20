@extends('layouts.main')

@section('title', 'Edit Trip')

@section('content')
<div class="page-header mb-4">
    <h1>Edit Trip</h1>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('driver.trips.update', $trip) }}">
                    @csrf
                    @method('PUT')

                    <!-- Trip Date -->
                    <div class="mb-3">
                        <label for="trip_date" class="form-label">Trip Date & Time <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control @error('trip_date') is-invalid @enderror" id="trip_date" name="trip_date" value="{{ old('trip_date', $trip->trip_date->format('Y-m-d\TH:i')) }}" required>
                        @error('trip_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Pickup Location -->
                    <div class="mb-3">
                        <label for="pickup_location" class="form-label">Pickup Location <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('pickup_location') is-invalid @enderror" id="pickup_location" name="pickup_location" value="{{ old('pickup_location', $trip->pickup_location) }}" required>
                        @error('pickup_location')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Drop Location -->
                    <div class="mb-3">
                        <label for="drop_location" class="form-label">Drop Location <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('drop_location') is-invalid @enderror" id="drop_location" name="drop_location" value="{{ old('drop_location', $trip->drop_location) }}" required>
                        @error('drop_location')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control @error('quantity') is-invalid @enderror" id="quantity" name="quantity" value="{{ old('quantity', $trip->quantity) }}" required>
                            @error('quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="unit" class="form-label">Unit <span class="text-danger">*</span></label>
                            <select class="form-select @error('unit') is-invalid @enderror" id="unit" name="unit" required>
                                <option value="PCS" {{ old('unit', $trip->unit) == 'PCS' ? 'selected' : '' }}>PCS</option>
                                <option value="Kg" {{ old('unit', $trip->unit) == 'Kg' ? 'selected' : '' }}>Kg</option>
                                <option value="Tons" {{ old('unit', $trip->unit) == 'Tons' ? 'selected' : '' }}>Tons</option>
                                <option value="Ltr" {{ old('unit', $trip->unit) == 'Ltr' ? 'selected' : '' }}>Ltr</option>
                                <option value="Box" {{ old('unit', $trip->unit) == 'Box' ? 'selected' : '' }}>Box</option>
                                <option value="Packet" {{ old('unit', $trip->unit) == 'Packet' ? 'selected' : '' }}>Packet</option>
                            </select>
                            @error('unit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label for="description" class="form-label">Description (Goods/Materials)</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $trip->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('driver.trips.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Update Trip
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
