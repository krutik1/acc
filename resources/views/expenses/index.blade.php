@extends('layouts.main')

@section('title', 'Expenses')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 text-gray-800">Expenses</h1>
    <a href="{{ route('expenses.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Add Expense
    </a>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-funnel me-2"></i>Filter Expenses</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('expenses.index') }}" class="row g-3">
            <div class="col-md-3">
                <label for="date_from" class="form-label small text-muted">From Date</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-3">
                <label for="date_to" class="form-label small text-muted">To Date</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3">
                <label for="unit_id" class="form-label small text-muted">Unit</label>
                <select class="form-select" id="unit_id" name="unit_id">
                    <option value="">All Units</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="category_id" class="form-label small text-muted">Category</label>
                <select class="form-select" id="category_id" name="category_id">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="payment_mode" class="form-label small text-muted">Payment Mode</label>
                <select class="form-select" id="payment_mode" name="payment_mode">
                    <option value="">All Modes</option>
                    <option value="Cash" {{ request('payment_mode') == 'Cash' ? 'selected' : '' }}>Cash</option>
                    <option value="Bank" {{ request('payment_mode') == 'Bank' ? 'selected' : '' }}>Bank</option>
                    <option value="UPI" {{ request('payment_mode') == 'UPI' ? 'selected' : '' }}>UPI</option>
                    <option value="Cheque" {{ request('payment_mode') == 'Cheque' ? 'selected' : '' }}>Cheque</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="search" class="form-label small text-muted">Search</label>
                <input type="text" class="form-control" id="search" name="search" placeholder="Description, Amount..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i> Apply Filters</button>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                 <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Expenses List -->
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table align-items-center table-flush table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th scope="col" class="ps-4">Date</th>
                    <th scope="col">Unit</th>
                    <th scope="col">Category</th>
                    <th scope="col">Description</th>
                    <th scope="col">Payment Mode</th>
                    <th scope="col" class="text-end">Amount</th>
                    <th scope="col" class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $expense)
                <tr>
                    <td class="ps-4">{{ $expense->date->format('d M, Y') }}</td>
                    <td><span class="badge bg-light text-dark border">{{ $expense->unit->name }}</span></td>
                    <td>{{ $expense->category->name }}</td>
                    <td>
                        <span class="d-inline-block text-truncate" style="max-width: 200px;" title="{{ $expense->description }}">
                            {{ $expense->description ?: '-' }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $expense->payment_mode == 'Cash' ? 'bg-success' : ($expense->payment_mode == 'Bank' ? 'bg-primary' : 'bg-info') }} bg-opacity-75">
                            {{ $expense->payment_mode }}
                        </span>
                        @if($expense->reference_no)
                            <div class="small text-muted mt-1">{{ $expense->reference_no }}</div>
                        @endif
                    </td>
                    <td class="text-end fw-bold">₹ {{ number_format($expense->amount, 2) }}</td>
                    <td class="text-end pe-4">
                        @if($expense->attachment_path)
                            <a href="{{ Storage::url($expense->attachment_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary me-1" title="View Attachment">
                                <i class="bi bi-paperclip"></i>
                            </a>
                        @endif
                        <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-sm btn-info text-white me-1" title="Edit">
                            <i class="bi bi-pencil-fill"></i>
                        </a>
                        <form action="{{ route('expenses.destroy', $expense) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this expense?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger text-white" title="Delete">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <div class="text-muted">
                            <i class="bi bi-wallet2 display-4 mb-3 d-block opacity-50"></i>
                            <p class="h5">No expenses found</p>
                            <p class="small text-muted mb-0">Try adjusting filters or add a new expense.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($expenses->count() > 0)
            <tfoot class="bg-light">
                <tr>
                    <td colspan="5" class="text-end fw-bold py-3">Total (Page):</td>
                    <td class="text-end fw-bold py-3">₹ {{ number_format($expenses->sum('amount'), 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

@if($expenses->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $expenses->links() }}
</div>
@endif
@endsection
