@extends('layouts.main')

@section('title', 'Edit Challan')

@section('content')
<div class="page-header">
    <h1>Edit Challan: {{ $challan->challan_number }}</h1>
    <a href="{{ route('challans.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to List
    </a>
</div>

<form action="{{ route('challans.update', $challan) }}" method="POST" id="challanForm">
    @csrf
    @method('PUT')
    
    <div class="card mb-4">
        <div class="card-header">Challan Details</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="party_search" class="form-label">Party <span class="text-danger">*</span></label>
                    <div class="position-relative">
                        <input type="text" class="form-control" id="party_search" 
                               placeholder="Type to search party..." autocomplete="off"
                               value="{{ old('party_search', $challan->party->name) }}">
                        <input type="hidden" name="party_id" id="party_id" value="{{ old('party_id', $challan->party_id) }}">
                        <div id="party_suggestions" class="list-group position-absolute w-100 shadow" 
                             style="z-index: 1000; display: none; max-height: 200px; overflow-y: auto;">
                        </div>
                    </div>
                    @error('party_id')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="challan_number" class="form-label">Challan Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('challan_number') is-invalid @enderror" 
                           id="challan_number" name="challan_number" 
                           value="{{ old('challan_number', $challan->challan_number) }}" required>
                    <div id="challan_number_error" class="text-danger" style="display: none;"></div>
                    @error('challan_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="challan_date" class="form-label">Challan Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('challan_date') is-invalid @enderror" 
                           id="challan_date" name="challan_date" 
                           value="{{ old('challan_date', $challan->challan_date->format('Y-m-d')) }}" required>
                    @error('challan_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div id="partyDetails" class="row mt-2" style="display: none;">
                <div class="col-md-4">
                    <small class="text-muted">Address:</small>
                    <p class="mb-0" id="partyAddress"></p>
                </div>
                <div class="col-md-4">
                    <small class="text-muted">Contact:</small>
                    <p class="mb-0" id="partyContact"></p>
                </div>
                <div class="col-md-4">
                    <small class="text-muted">GST:</small>
                    <p class="mb-0" id="partyGst"></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Items</span>
            <button type="button" class="btn btn-sm btn-success" id="addItemBtn">
                <i class="bi bi-plus-lg me-1"></i>Add Item
            </button>
        </div>
        <div class="card-body p-0">
            <table class="table mb-0" id="itemsTable">
                <thead>
                    <tr>
                        <th width="40%">Description</th>
                        <th width="12%">Quantity</th>
                        <th width="12%">Unit</th>
                        <th width="15%">Rate</th>
                        <th width="15%">Amount</th>
                        <th width="6%"></th>
                    </tr>
                </thead>
                <tbody id="itemsBody">
                    @foreach($challan->items as $index => $item)
                    <tr class="item-row">
                        <td>
                            <input type="text" class="form-control form-control-sm enable-autocomplete" 
                                   data-type="challan_item_description"
                                   name="items[{{ $index }}][description]" 
                                   value="{{ old("items.$index.description", $item->description) }}" required autocomplete="off">
                        </td>
                        <td>
                            <input type="number" step="0.001" min="0.001" class="form-control form-control-sm item-qty" 
                                   name="items[{{ $index }}][quantity]" 
                                   value="{{ old("items.$index.quantity", $item->quantity) }}" required>
                        </td>
                        <td>
                            <select class="form-select form-select-sm" name="items[{{ $index }}][unit]">
                                @foreach($units as $key => $label)
                                    <option value="{{ $key }}" {{ $item->unit == $key ? 'selected' : '' }}>{{ $key }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0" class="form-control form-control-sm item-rate" 
                                   name="items[{{ $index }}][rate]" 
                                   value="{{ old("items.$index.rate", $item->rate) }}" required>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm item-amount" readonly disabled 
                                   value="₹{{ number_format($item->amount, 2) }}">
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-item" {{ $challan->items->count() == 1 ? 'disabled' : '' }}>
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                        <td><strong id="subtotal">₹{{ number_format($challan->subtotal, 2) }}</strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    
    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('challans.index') }}" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i>Update Challan
        </button>
    </div>
</form>
@include('partials.party-search-script')
@include('partials.autocomplete_script')
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const itemsBody = document.getElementById('itemsBody');
    const addItemBtn = document.getElementById('addItemBtn');
    const partySelect = document.getElementById('party_id');
    let itemIndex = {{ $challan->items->count() }};
    
    const unitOptions = `@foreach($units as $key => $label)<option value="{{ $key }}">{{ $key }}</option>@endforeach`;
    
    partySelect.addEventListener('change', function() {
        const partyId = this.value;
        const detailsDiv = document.getElementById('partyDetails');
        
        if (partyId) {
             fetch(`/api/parties/${partyId}/details`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('partyAddress').textContent = data.address || '-';
                    document.getElementById('partyContact').textContent = data.contact_number || '-';
                    document.getElementById('partyGst').textContent = data.gst_number || '-';
                    detailsDiv.style.display = 'flex';
                });
        } else {
            detailsDiv.style.display = 'none';
        }
    });
    
    if (partySelect.value) {
        partySelect.dispatchEvent(new Event('change'));
    }
    
    // Challan number duplicate validation
    const challanNumberInput = document.getElementById('challan_number');
    const challanNumberError = document.getElementById('challan_number_error');
    let duplicateCheckTimeout;
    const currentChallanId = {{ $challan->id }};
    
    challanNumberInput.addEventListener('input', function() {
        const challanNumber = this.value.trim();
        
        // Clear previous timeout
        clearTimeout(duplicateCheckTimeout);
        
        // Hide error message
        challanNumberError.style.display = 'none';
        this.classList.remove('is-invalid');
        
        // Only check if value is not empty
        if (challanNumber.length > 0) {
            // Debounce the AJAX call
            duplicateCheckTimeout = setTimeout(function() {
                fetch('{{ route("api.challans.check-duplicate") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        challan_number: challanNumber,
                        challan_id: currentChallanId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                        challanNumberError.textContent = data.message;
                        challanNumberError.style.display = 'block';
                        challanNumberInput.classList.add('is-invalid');
                    }
                })
                .catch(error => {
                    console.error('Error checking duplicate:', error);
                });
            }, 500); // Wait 500ms after user stops typing
        }
    });
    
    addItemBtn.addEventListener('click', function() {
        const newRow = document.createElement('tr');
        newRow.className = 'item-row';
        newRow.innerHTML = `
            <td>
                <input type="text" class="form-control form-control-sm enable-autocomplete" 
                       data-type="challan_item_description"
                       name="items[${itemIndex}][description]" required autocomplete="off">
            </td>
            <td><input type="number" step="0.001" min="0.001" class="form-control form-control-sm item-qty" name="items[${itemIndex}][quantity]" required></td>
            <td><select class="form-select form-select-sm" name="items[${itemIndex}][unit]">${unitOptions}</select></td>
            <td><input type="number" step="0.01" min="0" class="form-control form-control-sm item-rate" name="items[${itemIndex}][rate]" required></td>
            <td><input type="text" class="form-control form-control-sm item-amount" readonly disabled></td>
            <td><button type="button" class="btn btn-sm btn-outline-danger remove-item"><i class="bi bi-trash"></i></button></td>
        `;
        itemsBody.appendChild(newRow);
        itemIndex++;
        updateRemoveButtons();
    });
    
    itemsBody.addEventListener('click', function(e) {
        if (e.target.closest('.remove-item')) {
            e.target.closest('.item-row').remove();
            calculateSubtotal();
            updateRemoveButtons();
        }
    });
    
    itemsBody.addEventListener('input', function(e) {
        if (e.target.classList.contains('item-qty') || e.target.classList.contains('item-rate')) {
            const row = e.target.closest('.item-row');
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const rate = parseFloat(row.querySelector('.item-rate').value) || 0;
            row.querySelector('.item-amount').value = '₹' + (qty * rate).toFixed(2);
            calculateSubtotal();
        }
    });
    
    function calculateSubtotal() {
        let total = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const rate = parseFloat(row.querySelector('.item-rate').value) || 0;
            total += qty * rate;
        });
        document.getElementById('subtotal').textContent = '₹' + total.toFixed(2);
    }
    
    function updateRemoveButtons() {
        const rows = document.querySelectorAll('.item-row');
        rows.forEach(row => row.querySelector('.remove-item').disabled = rows.length === 1);
    }
});
</script>
@endpush
