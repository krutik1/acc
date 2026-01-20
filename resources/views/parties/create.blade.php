@extends('layouts.main')

@section('title', 'Create Party')

@section('content')
<div class="page-header">
    <h1>Create Party</h1>
    <a href="{{ route('parties.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to List
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('parties.store') }}" method="POST">
            @csrf
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Party Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                           id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="contact_number" class="form-label">Contact Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('contact_number') is-invalid @enderror" 
                           id="contact_number" name="contact_number" value="{{ old('contact_number') }}" required>
                    @error('contact_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
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
            
            <div class="mb-3">
                <label for="gst_number" class="form-label">GST Number <span class="text-muted">(Optional)</span></label>
                <div class="input-group">
                    <input type="text" class="form-control @error('gst_number') is-invalid @enderror" 
                           id="gst_number" name="gst_number" value="{{ old('gst_number') }}" 
                           placeholder="e.g., 27AABCU9603R1ZX" maxlength="15">
                    <div class="input-group-text">
                        <input class="form-check-input mt-0 me-2" type="checkbox" id="fetch_gst_details" aria-label="Fetch details from GST">
                        <label class="form-check-label mb-0" for="fetch_gst_details" style="cursor: pointer;">Validate & Fetch</label>
                    </div>
                </div>
                @error('gst_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div id="gst_feedback" class="form-text"></div>
            </div>
            
            <hr>
            
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('parties.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>Create Party
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const gstInput = document.getElementById('gst_number');
    const fetchCheckbox = document.getElementById('fetch_gst_details');
    const feedbackDiv = document.getElementById('gst_feedback');
    const nameInput = document.getElementById('name');
    const addressInput = document.getElementById('address');

    function validateAndFetchGst() {
        // Only run if checkbox is checked
        if (!fetchCheckbox.checked) {
            feedbackDiv.textContent = '15-character alphanumeric GST Identification Number';
            feedbackDiv.classList.remove('text-success', 'text-danger');
            feedbackDiv.classList.add('text-muted');
            gstInput.classList.remove('is-valid', 'is-invalid');
            return;
        }

        const gst = gstInput.value.trim().toUpperCase();
        gstInput.value = gst; // Auto-uppercase

        if (gst.length === 0) {
            return;
        }

        // Show loading state
        feedbackDiv.textContent = 'Validating...';
        feedbackDiv.className = 'form-text text-primary';

        fetch('{{ route("api.parties.fetch-gst") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ gst_number: gst })
        })
        .then(response => response.json())
        .then(data => {
            if (data.valid) {
                // success
                feedbackDiv.textContent = 'GST Valid! details fetched.';
                feedbackDiv.className = 'form-text text-success';
                gstInput.classList.remove('is-invalid');
                gstInput.classList.add('is-valid');

                // Auto-fill if data present
                if (data.data) {
                    if(data.data.name) nameInput.value = data.data.name;
                    if(data.data.address) addressInput.value = data.data.address;
                }
            } else {
                // error
                feedbackDiv.textContent = data.message;
                feedbackDiv.className = 'form-text text-danger';
                gstInput.classList.remove('is-valid');
                gstInput.classList.add('is-invalid');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            feedbackDiv.textContent = 'Error fetching details.';
            feedbackDiv.className = 'form-text text-danger';
        });
    }

    // Trigger on blur if checked, or on checkbox change
    gstInput.addEventListener('blur', validateAndFetchGst);
    fetchCheckbox.addEventListener('change', validateAndFetchGst);
    
    // Clear validation state on input
    gstInput.addEventListener('input', function() {
        if (!fetchCheckbox.checked) return;
        this.classList.remove('is-valid', 'is-invalid');
        feedbackDiv.textContent = '';
    });
});
</script>
@endsection
