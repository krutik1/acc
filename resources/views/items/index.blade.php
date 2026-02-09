@extends('layouts.main')

@section('title', 'Item Master')

@section('content')
<div class="page-header">
    <h1>Item Master</h1>
    <a href="{{ route('items.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Add Item
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('items.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="{{ request('search') }}" placeholder="Search by Name or HSN...">
            </div>
            
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-filter me-1"></i>Filter
                </button>
            </div>

            @if(request()->anyFilled(['search', 'status']))
            <div class="col-md-2">
                <a href="{{ route('items.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-x-lg me-1"></i>Clear
                </a>
            </div>
            @endif
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="5%">#</th>
                        <th width="30%">Item Name</th>
                        <th width="15%">HSN Code</th>
                        <th width="15%" class="text-end">Rate</th>
                        <th width="10%">Unit</th>
                        <th width="10%">Status</th>
                        <th width="15%" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $index => $item)
                    <tr>
                        <td>{{ $items->firstItem() + $index }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ $item->name }}</div>
                        </td>
                        <td>
                            @if($item->hsn_code)
                                <span class="badge bg-light text-dark border">{{ $item->hsn_code }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-end">₹{{ number_format($item->rate, 2) }}</td>
                        <td>{{ ucfirst($item->unit) }}</td>
                        <td>
                            @if($item->status == 'active')
                                <span class="badge bg-success-subtle text-success">Active</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('items.edit', $item) }}" class="action-btn bg-info me-1" title="Edit">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                            <form action="{{ route('items.destroy', $item) }}" method="POST" 
                                  onsubmit="return confirm('Are you sure you want to delete this item?');" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn bg-danger" title="Delete">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="bi bi-box display-6 d-block mb-2"></i>
                            No items found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($items->hasPages())
    <div class="card-footer">
        {{ $items->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
