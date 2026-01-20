@extends('layouts.main')

@section('title', 'Create Invoice')

@section('content')
<div class="page-header">
    <h1>Create Invoice</h1>
    <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to List
    </a>
</div>

<form action="{{ route('invoices.store') }}" method="POST" id="invoiceForm">
    @csrf
    
    <div class="row">
        <div class="col-md-8">
            <!-- Party Selection -->
            <div class="card mb-4">
                <div class="card-header">Step 1: Select Party</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="party_search" class="form-label">Party <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="text" class="form-control" id="party_search" 
                                       placeholder="Type to search party..." autocomplete="off">
                                <input type="hidden" name="party_id" id="party_id" value="{{ old('party_id', request('party_id')) }}">
                                <div id="party_suggestions" class="list-group position-absolute w-100 shadow" 
                                     style="z-index: 1000; display: none; max-height: 200px; overflow-y: auto;">
                                </div>
                            </div>
                            @error('party_id')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="invoice_number" class="form-label">Invoice Number</label>
                            <input type="text" class="form-control" id="invoice_number" 
                                   value="{{ $invoiceNumber }}" readonly disabled>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="invoice_date" class="form-label">Invoice Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('invoice_date') is-invalid @enderror" 
                                   id="invoice_date" name="invoice_date" 
                                   value="{{ old('invoice_date', date('Y-m-d')) }}" required>
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
            
            <!-- Challan Selection -->
            <div class="card mb-4">
                <div class="card-header">Step 2: Select Challans</div>
                <div class="card-body">
                    <div id="challansLoading" class="text-center py-4" style="display: none;">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 mb-0">Loading challans...</p>
                    </div>
                    
                    <div id="noPartySelected" class="text-center py-4 text-muted">
                        <i class="bi bi-arrow-up-circle display-4 d-block mb-2"></i>
                        Please select a party first
                    </div>
                    
                    <div id="noChallans" class="text-center py-4 text-muted" style="display: none;">
                        <i class="bi bi-file-x display-4 d-block mb-2"></i>
                        No challans found for this party
                    </div>
                    
                    <div id="challansContainer" style="display: none;">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="40"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                                    <th>Challan No.</th>
                                    <th>Date</th>
                                    <th>Items</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody id="challansBody"></tbody>
                        </table>
                    </div>
                    
                    @error('challan_ids')
                        <div class="text-danger mt-2">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <!-- Notes -->
            <div class="card mb-4">
                <div class="card-header">Notes (Optional)</div>
                <div class="card-body">
                    <textarea class="form-control enable-autocomplete" name="notes" rows="2" 
                              placeholder="Additional notes..." data-type="invoice_notes" autocomplete="off">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Calculations -->
            <div class="card mb-4 sticky-top" style="top: 20px;">
                <div class="card-header">Step 3: Calculations</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Selected Challans Subtotal</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="text" class="form-control fs-5 fw-bold" id="subtotal" value="0.00" readonly disabled>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row mb-3">
                        <div class="col-8">
                            <label for="gst_percent" class="form-label">GST (%)</label>
                            <input type="number" step="0.01" min="0" max="100" class="form-control calc-input" 
                                   id="gst_percent" name="gst_percent" value="{{ old('gst_percent', 0) }}">
                        </div>
                        <div class="col-4 d-flex align-items-end">
                            <span class="text-success fw-bold" id="gstAmount">+ ₹0.00</span>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-8">
                            <label for="tds_percent" class="form-label">TDS (%)</label>
                            <input type="number" step="0.01" min="0" max="100" class="form-control calc-input" 
                                   id="tds_percent" name="tds_percent" value="{{ old('tds_percent', 0) }}">
                        </div>
                        <div class="col-4 d-flex align-items-end">
                            <span class="text-danger fw-bold" id="tdsAmount">- ₹0.00</span>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label">Discount</label>
                            <div class="input-group">
                                <select class="form-select calc-input" name="discount_type" id="discount_type" style="max-width: 120px;">
                                    <option value="percentage" {{ old('discount_type') == 'percentage' ? 'selected' : '' }}>Percent %</option>
                                    <option value="fixed" {{ old('discount_type') == 'fixed' ? 'selected' : '' }}>Fixed ₹</option>
                                
                                </select>
                                <input type="number" step="0.01" min="0" class="form-control calc-input" 
                                       id="discount_value" name="discount_value" value="{{ old('discount_value', 0) }}">
                            </div>
                            <div class="text-end mt-1"><span class="text-danger fw-bold" id="discountAmount">- ₹0.00</span></div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fs-5">Final Amount:</span>
                        <span class="highlight-amount" id="finalAmount">₹0.00</span>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 btn-lg" id="submitBtn" disabled>
                        <i class="bi bi-check-lg me-1"></i>Create Invoice
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@include('partials.party-search-script')
@include('partials.autocomplete_script')
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const partySelect = document.getElementById('party_id');
    const challansBody = document.getElementById('challansBody');
    const challansContainer = document.getElementById('challansContainer');
    const noPartySelected = document.getElementById('noPartySelected');
    const noChallans = document.getElementById('noChallans');
    const challansLoading = document.getElementById('challansLoading');
    const selectAll = document.getElementById('selectAll');
    const submitBtn = document.getElementById('submitBtn');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    
    let selectedChallans = [];
    let challanData = [];
    
    // Party selection
    partySelect.addEventListener('change', function() {
        const partyId = this.value;
        
        // Reset
        challansBody.innerHTML = '';
        selectedChallans = [];
        challanData = [];
        updateCalculations();
        
        if (!partyId) {
            noPartySelected.style.display = 'block';
            challansContainer.style.display = 'none';
            noChallans.style.display = 'none';
            document.getElementById('partyDetails').style.display = 'none';
            return;
        }
        
        noPartySelected.style.display = 'none';
        challansLoading.style.display = 'block';
        
        // Fetch challans for party
        fetch(`/api/parties/${partyId}/challans`)
            .then(response => response.json())
            .then(data => {
                challansLoading.style.display = 'none';
                
                // Show party details
                const details = document.getElementById('partyDetails');
                document.getElementById('partyAddress').textContent = data.party.address || '-';
                document.getElementById('partyContact').textContent = data.party.contact_number || '-';
                document.getElementById('partyGst').textContent = data.party.gst_number || '-';
                details.style.display = 'flex';
                
                if (data.challans.length === 0) {
                    noChallans.style.display = 'block';
                    challansContainer.style.display = 'none';
                    return;
                }
                
                challanData = data.challans;
                noChallans.style.display = 'none';
                challansContainer.style.display = 'block';
                
                // Render challans
                challansBody.innerHTML = '';
                data.challans.forEach(challan => {
                    const row = document.createElement('tr');
                    const statusBadge = challan.is_invoiced 
                        ? '<span class="badge bg-success ms-2">Invoiced</span>' 
                        : '<span class="badge bg-warning text-dark ms-2">Pending</span>';
                    row.innerHTML = `
                        <td>
                            <input type="checkbox" class="form-check-input challan-check" 
                                   name="challan_ids[]" value="${challan.id}" 
                                   data-subtotal="${challan.subtotal_raw}">
                        </td>
                        <td><strong>${challan.challan_number}</strong>${statusBadge}</td>
                        <td>${challan.challan_date}</td>
                        <td>${challan.items_count} items</td>
                        <td class="text-end">₹${challan.subtotal}</td>
                    `;
                    challansBody.appendChild(row);
                });
            })
            .catch(error => {
                challansLoading.style.display = 'none';
                console.error('Error fetching challans:', error);
            });
    });
    
    // Auto-trigger if party is pre-selected
    if (partySelect.value) {
        partySelect.dispatchEvent(new Event('change'));
    }
    
    // Select all challans
    selectAll.addEventListener('change', function() {
        document.querySelectorAll('.challan-check').forEach(cb => {
            cb.checked = this.checked;
        });
        updateSelectedChallans();
    });
    
    // Individual challan selection
    challansBody.addEventListener('change', function(e) {
        if (e.target.classList.contains('challan-check')) {
            updateSelectedChallans();
        }
    });
    
    // Calculate on input change
    document.querySelectorAll('.calc-input').forEach(input => {
        input.addEventListener('input', updateCalculations);
    });
    
    function updateSelectedChallans() {
        selectedChallans = [];
        let subtotal = 0;
        
        document.querySelectorAll('.challan-check:checked').forEach(cb => {
            selectedChallans.push(cb.value);
            subtotal += parseFloat(cb.dataset.subtotal);
        });
        
        document.getElementById('subtotal').value = subtotal.toFixed(2);
        submitBtn.disabled = selectedChallans.length === 0;
        updateCalculations();
    }
    
    function updateCalculations() {
        const subtotal = parseFloat(document.getElementById('subtotal').value) || 0;
        const gstPercent = parseFloat(document.getElementById('gst_percent').value) || 0;
        const tdsPercent = parseFloat(document.getElementById('tds_percent').value) || 0;
        const discountType = document.getElementById('discount_type').value;
        const discountValue = parseFloat(document.getElementById('discount_value').value) || 0;
        
        // Step 1: Calculate discount first
        let discountAmount;
        if (discountType === 'percentage') {
            discountAmount = subtotal * (discountValue / 100);
        } else {
            discountAmount = Math.min(discountValue, subtotal);
        }
        discountAmount = Math.round(discountAmount * 100) / 100;
        document.getElementById('discountAmount').textContent = '- ₹' + discountAmount.toFixed(2);
        
        // Step 2: Apply discount to get discounted subtotal
        const discountedSubtotal = Math.round((subtotal - discountAmount) * 100) / 100;
        
        // Step 3: Calculate GST on the discounted amount (after discount)
        const gstAmount = Math.round(discountedSubtotal * (gstPercent / 100) * 100) / 100;
        document.getElementById('gstAmount').textContent = '+ ₹' + gstAmount.toFixed(2);
        
        // Step 4: Calculate TDS on the discounted amount
        const tdsAmount = Math.round(discountedSubtotal * (tdsPercent / 100) * 100) / 100;
        document.getElementById('tdsAmount').textContent = '- ₹' + tdsAmount.toFixed(2);
        
        // Step 5: Final Amount = Discounted Subtotal + GST - TDS
        const finalAmount = Math.max(0, Math.round((discountedSubtotal + gstAmount - tdsAmount) * 100) / 100);
        document.getElementById('finalAmount').textContent = '₹' + finalAmount.toFixed(2);
    }
});
</script>
@endpush
