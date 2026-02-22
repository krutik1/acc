@extends('layouts.main')

@section('title', 'Record Payment')

@section('content')
<div class="page-header">
    <h1>Record Payment</h1>
    <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to List
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('payments.store') }}" method="POST">
                    @csrf
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="payment_number" class="form-label">Payment Number</label>
                            <input type="text" class="form-control" value="{{ $paymentNumber }}" readonly disabled>
                        </div>
                        <div class="col-md-6">
                            <label for="payment_date" class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('payment_date') is-invalid @enderror" 
                                   id="payment_date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" required>
                            @error('payment_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                            <label for="party_search" class="form-label">Party <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="text" class="form-control" id="party_search" 
                                       placeholder="Type to search party..." autocomplete="off">
                                <input type="hidden" name="party_id" id="party_id" value="{{ old('party_id') }}">
                                <div id="party_suggestions" class="list-group position-absolute w-100 shadow" 
                                     style="z-index: 1000; display: none; max-height: 200px; overflow-y: auto;">
                                </div>
                            </div>
                            @error('party_id')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                    </div>

                    <div id="invoice_selection_container" style="display: none;" class="mb-4">
                        <label class="form-label fw-bold">Select Invoices to Pay (Optional)</label>
                        <div id="no_invoices_msg" class="alert alert-info py-2 small" style="display: none;">
                            No pending invoices found for this party.
                        </div>
                        <div id="invoice_table_container" class="table-responsive border rounded bg-light p-2" style="max-height: 300px;">
                            <table class="table table-sm table-hover mb-0">

                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 40px;">Pay</th>
                                        <th>Invoice #</th>
                                        <th>Date (FY)</th>
                                        <th class="text-end">Total</th>
                                        <th class="text-end">Balance</th>
                                        <th class="text-end" style="width: 150px;">Amount to Allocate</th>

                                    </tr>
                                </thead>
                                <tbody id="invoice_list">
                                    <!-- Populated via JS -->
                                </tbody>
                                <tfoot>
                                    <tr class="table-light fw-bold">
                                        <td colspan="5" class="text-end">Total Allocated:</td>
                                        <td class="text-end" id="total_allocated">₹ 0.00</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="form-text mt-2">
                            <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none" id="apply_total_btn" style="display: none;">
                                <i class="bi bi-arrow-down-circle me-1"></i>Apply total allocated to Payment Amount
                            </button>
                        </div>
                    </div>


                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="type" class="form-label">Payment Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                <option value="received" {{ old('type') == 'received' ? 'selected' : '' }}>Received (In)</option>
                                <option value="sent" {{ old('type') == 'sent' ? 'selected' : '' }}>Sent (Out)</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror" 
                                       id="amount" name="amount" value="{{ old('amount') }}" required>
                            </div>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="mode" class="form-label">Payment Mode <span class="text-danger">*</span></label>
                            <select class="form-select @error('mode') is-invalid @enderror" id="mode" name="mode" required>
                                <option value="cash" {{ old('mode') == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="cheque" {{ old('mode') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                <option value="bank_transfer" {{ old('mode') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer / NEFT / RTGS</option>
                                <option value="upi" {{ old('mode') == 'upi' ? 'selected' : '' }}>UPI</option>
                                <option value="other" {{ old('mode') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('mode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="reference_number" class="form-label">Reference / Cheque No.</label>
                            <input type="text" class="form-control enable-autocomplete @error('reference_number') is-invalid @enderror" 
                                   id="reference_number" name="reference_number" value="{{ old('reference_number') }}"
                                   data-type="payment_reference" autocomplete="off">
                            @error('reference_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control enable-autocomplete @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="3" data-type="payment_notes" autocomplete="off">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">Record Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@include('partials.party-search-script')
@include('partials.autocomplete_script')

<script>
document.addEventListener('DOMContentLoaded', function() {
    const partyIdInput = document.getElementById('party_id');
    const invoiceContainer = document.getElementById('invoice_selection_container');
    const invoiceList = document.getElementById('invoice_list');
    const invoiceTableContainer = document.getElementById('invoice_table_container');
    const noInvoicesMsg = document.getElementById('no_invoices_msg');
    const paymentAmountInput = document.getElementById('amount');
    const totalAllocatedText = document.getElementById('total_allocated');
    const applyTotalBtn = document.getElementById('apply_total_btn');

    partyIdInput.addEventListener('change', function() {
        const partyId = this.value;
        console.log('Party changed:', partyId);
        if (!partyId) {
            invoiceContainer.style.display = 'none';
            invoiceList.innerHTML = '';
            return;
        }

        const url = "{{ route('api.parties.pending-invoices', ':party') }}".replace(':party', partyId);
        console.log('Fetching invoices from:', url);
        
        invoiceContainer.style.display = 'block';
        noInvoicesMsg.style.display = 'none';
        invoiceTableContainer.style.display = 'none';
        invoiceList.innerHTML = '<tr><td colspan="6" class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div> Loading...</td></tr>';

        fetch(url)
            .then(response => {
                console.log('API Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Invoices data received:', data);
                if (data.length > 0) {
                    invoiceList.innerHTML = '';
                    data.forEach((invoice, index) => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td class="align-middle">
                                <div class="form-check">
                                    <input class="form-check-input invoice-checkbox" type="checkbox" 
                                           id="inv_cb_${invoice.id}" data-index="${index}" data-id="${invoice.id}" data-pending="${invoice.pending_amount}">
                                </div>
                            </td>
                            <td class="align-middle">${invoice.invoice_number}</td>
                            <td class="align-middle">
                                ${new Date(invoice.invoice_date).toLocaleDateString()}<br>
                                <small class="text-muted">${invoice.financial_year}</small>
                            </td>
                            <td class="align-middle text-end">₹ ${parseFloat(invoice.final_amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>

                            <td class="align-middle text-end text-danger">₹ ${parseFloat(invoice.pending_amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>

                            <td>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" step="0.01" class="form-control text-end invoice-amount-input" 
                                           name="invoices[${index}][amount]" value="0" min="0" max="${invoice.pending_amount}" 
                                           data-index="${index}" disabled>
                                    <input type="hidden" name="invoices[${index}][id]" value="${invoice.id}" 
                                           class="invoice-id-input" data-index="${index}" disabled>
                                </div>
                            </td>

                        `;
                        invoiceList.appendChild(row);
                    });
                    invoiceTableContainer.style.display = 'block';
                    setupInvoiceListeners();
                } else {
                    noInvoicesMsg.style.display = 'block';
                    invoiceTableContainer.style.display = 'none';
                    invoiceList.innerHTML = '';
                }
            })
            .catch(error => {
                console.error('Error fetching invoices:', error);
                invoiceContainer.style.display = 'none';
            });
    });


    function setupInvoiceListeners() {
        const checkboxes = document.querySelectorAll('.invoice-checkbox');
        
        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                const index = this.dataset.index;
                const amountInput = document.querySelector(`.invoice-amount-input[data-index="${index}"]`);
                const idInput = document.querySelector(`.invoice-id-input[data-index="${index}"]`);
                
                if (this.checked) {
                    amountInput.disabled = false;
                    idInput.disabled = false;
                    if (parseFloat(amountInput.value) === 0) {
                        amountInput.value = this.dataset.pending;
                    }
                } else {
                    amountInput.disabled = true;
                    idInput.disabled = true;
                }
                updateTotalAllocated();
            });
        });

        document.querySelectorAll('.invoice-amount-input').forEach(input => {
            input.addEventListener('input', function() {
                updateTotalAllocated();
            });
        });
    }


    function updateTotalAllocated() {
        let total = 0;
        document.querySelectorAll('.invoice-amount-input:not(:disabled)').forEach(input => {
            total += parseFloat(input.value) || 0;
        });
        totalAllocatedText.textContent = total.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        
        if (total > 0) {
            applyTotalBtn.style.display = 'inline-block';
        } else {
            applyTotalBtn.style.display = 'none';
        }
    }

    applyTotalBtn.addEventListener('click', function() {
        let total = 0;
        document.querySelectorAll('.invoice-amount-input:not(:disabled)').forEach(input => {
            total += parseFloat(input.value) || 0;
        });
        paymentAmountInput.value = total.toFixed(2);
    });
});
</script>
@endsection

