@extends('layouts.main')

@section('title', 'Bank Statement')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">{{ $bankAccount->bank_name }}</h1>
            <p class="text-muted mb-0">{{ $bankAccount->account_number }} | {{ $bankAccount->account_type }}</p>
        </div>
        <div class="text-end">
            <h6 class="text-uppercase text-muted mb-1">Current Balance</h6>
            <h2 class="mb-0 fw-bold {{ $bankAccount->current_balance >= 0 ? 'text-success' : 'text-danger' }}">
                ₹{{ formatIndianCurrency($bankAccount->current_balance) }}
            </h2>
        </div>
    </div>

    <!-- Filters & Actions -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <div class="row g-3 align-items-end">
                <div class="col-md-8">
                    <form method="GET" class="row g-2">
                        <div class="col-auto">
                            <label class="form-label small fw-bold">From</label>
                            <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
                        </div>
                        <div class="col-auto">
                            <label class="form-label small fw-bold">To</label>
                            <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
                        </div>
                        <div class="col-auto">
                            <label class="form-label small fw-bold">Type</label>
                            <select name="type" class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="credit" {{ request('type') == 'credit' ? 'selected' : '' }}>Credit</option>
                                <option value="debit" {{ request('type') == 'debit' ? 'selected' : '' }}>Debit</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <label class="form-label d-block text-white">.</label> <!-- Spacer -->
                            <button type="submit" class="btn btn-dark btn-sm">Filter</button>
                            @if(request()->hasAny(['from_date', 'to_date', 'type']))
                                <a href="{{ route('bank-accounts.show', $bankAccount) }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                            @endif
                        </div>
                    </form>
                </div>
                <div class="col-md-4 text-end">
                    <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#transactionModal" onclick="resetModal()">
                        <i class="bi bi-plus-lg me-1"></i> Add Entry
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Ledger Table -->
    <div class="card shadow border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Date</th>
                        <th>Description</th>
                        <th>Ref No.</th>
                        <th class="text-end text-success">Credit</th>
                        <th class="text-end text-danger">Debit</th>
                        <th class="text-end bg-light fw-bold">Balance</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Opening Balance Row -->
                    <tr class="table-warning bg-opacity-10 fw-medium">
                        <td class="ps-4 text-muted">
                            {{ request('from_date') ? \Carbon\Carbon::parse(request('from_date'))->format('d M Y') : $bankAccount->opening_balance_date->format('d M Y') }}
                        </td>
                        <td colspan="4" class="text-muted fst-italic">
                            Opening Balance {{ request('from_date') ? '(Brought Forward)' : '' }}
                        </td>
                        <td class="text-end fw-bold text-dark">
                            {{ formatIndianCurrency($openingBalanceForPeriod) }}
                        </td>
                        <td class="text-center text-muted"><i class="bi bi-lock-fill"></i></td>
                    </tr>

                    <!-- Transactions -->
                    @forelse($transactions as $txn)
                        <tr>
                            <td class="ps-4 text-secondary">{{ $txn->transaction_date->format('d M Y') }}</td>
                            <td>
                                <div class="text-dark fw-medium text-truncate" style="max-width: 200px;" title="{{ $txn->description }}">{{ $txn->description }}</div>
                                @if($txn->category)
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border fw-normal">{{ $txn->category }}</span>
                                @endif
                            </td>
                            <td class="text-muted small">{{ $txn->reference_number ?? '-' }}</td>
                            <td class="text-end fw-bold text-success">
                                {{ $txn->type === 'credit' ? formatIndianCurrency($txn->amount) : '' }}
                            </td>
                            <td class="text-end fw-bold text-danger">
                                {{ $txn->type === 'debit' ? formatIndianCurrency($txn->amount) : '' }}
                            </td>
                            <td class="text-end fw-bold text-dark bg-light">
                                {{ formatIndianCurrency($txn->running_balance) }}
                            </td>
                            <td class="text-center">
                                @if(!$txn->related_id)
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-primary btn-sm border-0" 
                                            onclick="editTransaction({{ json_encode($txn) }}, '{{ route('bank-transactions.update', $txn->id) }}')"
                                            data-bs-toggle="tooltip" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <form action="{{ route('bank-transactions.destroy', $txn->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this entry?');" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm border-0" data-bs-toggle="tooltip" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="badge bg-light text-secondary border"><i class="bi bi-gear-fill me-1"></i>System</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-clipboard-x display-6 d-block mb-3"></i>
                                No transactions found for this period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Transaction Modal -->
<div class="modal fade" id="transactionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="modalTitle">Add New Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="transactionForm" method="POST" action="{{ route('bank-transactions.store', $bankAccount) }}">
                @csrf
                <div id="methodField"></div> 
                
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Date</label>
                            <input type="date" name="transaction_date" id="txn_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Type</label>
                            <select name="type" id="txn_type" class="form-select" required>
                                <option value="debit">Debit (Expense)</option>
                                <option value="credit">Credit (Income)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" step="0.01" name="amount" id="txn_amount" class="form-control fw-bold" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">Description</label>
                            <input type="text" name="description" id="txn_description" class="form-control" placeholder="e.g. Office Rent" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted text-optional">Category</label>
                            <input type="text" name="category" id="txn_category" class="form-control" placeholder="Optional" list="categories">
                            <datalist id="categories">
                                <option value="Sales">
                                <option value="Purchase">
                                <option value="Salary">
                                <option value="Fuel">
                                <option value="Office Expense">
                                <option value="Transfer">
                            </datalist>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted text-optional">Reference No</label>
                            <input type="text" name="reference_number" id="txn_reference" class="form-control" placeholder="Optional">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Entry</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function resetModal() {
        document.getElementById('modalTitle').innerText = 'Add New Entry';
        document.getElementById('transactionForm').action = "{{ route('bank-transactions.store', $bankAccount) }}";
        document.getElementById('methodField').innerHTML = ''; // Remove PUT method
        
        document.getElementById('txn_date').value = "{{ date('Y-m-d') }}";
        document.getElementById('txn_type').value = 'debit';
        document.getElementById('txn_amount').value = '';
        document.getElementById('txn_description').value = '';
        document.getElementById('txn_category').value = '';
        document.getElementById('txn_reference').value = '';
    }

    function editTransaction(txn, url) {
        document.getElementById('modalTitle').innerText = 'Edit Entry';
        document.getElementById('transactionForm').action = url;
        document.getElementById('methodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
        
        // Parse date for input (YYYY-MM-DD)
        let dateVal = new Date(txn.transaction_date).toISOString().split('T')[0];
        document.getElementById('txn_date').value = dateVal;
        
        document.getElementById('txn_type').value = txn.type;
        document.getElementById('txn_amount').value = txn.amount;
        document.getElementById('txn_description').value = txn.description;
        document.getElementById('txn_category').value = txn.category || '';
        document.getElementById('txn_reference').value = txn.reference_number || '';
        
        // Show Modal
        var myModal = new bootstrap.Modal(document.getElementById('transactionModal'));
        myModal.show();
    }
</script>
@endpush
@endsection
