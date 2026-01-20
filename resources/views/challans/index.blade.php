@extends('layouts.main')

@section('title', 'Challans')

@section('content')
<div class="page-header">
    <h1>Challans</h1>
    <a href="{{ route('challans.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Create Challan
    </a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('challans.index') }}" class="row g-3">
            <div class="col-md-3">
                <select name="party_id" class="form-select">
                    <option value="">All Parties</option>
                    @foreach($parties as $party)
                        <option value="{{ $party->id }}" {{ request('party_id') == $party->id ? 'selected' : '' }}>
                            {{ $party->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" placeholder="From">
            </div>
            <div class="col-md-2">
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}" placeholder="To">
            </div>
            <div class="col-md-2">
                 <select name="invoiced" class="form-select">
                    <option value="">All Status</option>
                    <option value="no" {{ request('invoiced') == 'no' ? 'selected' : '' }}>Pending</option>
                    <option value="yes" {{ request('invoiced') == 'yes' ? 'selected' : '' }}>Invoiced</option>
                </select>
            </div>
            <div class="col-md-3">
                <div class="input-group">
                    <input type="number" name="min_amount" class="form-control" placeholder="Min" value="{{ request('min_amount') }}">
                    <span class="input-group-text">-</span>
                    <input type="number" name="max_amount" class="form-control" placeholder="Max" value="{{ request('max_amount') }}">
                </div>
            </div>
            <div class="col-md-9">
                <input type="text" name="search" class="form-control" placeholder="Search Challan No..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3 text-end">
                <button type="submit" class="btn btn-primary me-2"><i class="bi bi-filter me-1"></i>Filter</button>
                <a href="{{ route('challans.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <!-- Header with entries per page if needed, or simple header -->


    <div class="table-responsive">
        <table class="table align-items-center table-flush table-hover mb-0">
            <thead>
                <tr>
                    <th>Challan No.</th>
                    <th>Date</th>
                    <th>Party</th>
                    <th>Items</th>
                    <th class="text-end">Subtotal</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($challans as $challan)
                <tr>
                    <td>
                        <a href="{{ route('challans.show', $challan) }}" class="fw-bold text-primary text-decoration-none">
                            {{ $challan->challan_number }}
                        </a>
                    </td>
                    <td>{{ $challan->challan_date->format('d/m/Y') }}</td>
                    <td>
                         <div class="d-flex align-items-center">
                            <span class="avatar avatar-xs rounded-circle bg-light text-dark me-2 d-flex align-items-center justify-content-center border" style="width: 24px; height: 24px; font-size: 10px;">
                                {{ substr($challan->party->name, 0, 1) }}
                            </span>
                            {{ Str::limit($challan->party->name, 20) }}
                        </div>
                    </td>
                    <td>{{ $challan->items_count ?? $challan->items->count() }}</td>
                    <td class="text-end fw-bold">₹{{ number_format($challan->subtotal, 2) }}</td>
                    <td>
                        @if($challan->is_invoiced)
                            <span class="badge bg-success">Invoiced</span>
                        @else
                            <span class="badge bg-warning">Pending</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('challans.show', $challan) }}" class="action-btn bg-warning me-1" title="View">
                            <i class="bi bi-eye-fill text-white" style="font-size: 0.8rem;"></i>
                        </a>
                        <a href="{{ route('challans.edit', $challan) }}" class="action-btn bg-info me-1" title="Edit">
                            <i class="bi bi-pencil-fill text-white" style="font-size: 0.8rem;"></i>
                        </a>
                         @if(!$challan->is_invoiced)
                        <form action="{{ route('challans.destroy', $challan) }}" method="POST" class="d-inline" 
                              onsubmit="return confirm('Are you sure you want to delete this challan?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-btn bg-danger" title="Delete">
                                <i class="bi bi-trash-fill text-white" style="font-size: 0.8rem;"></i>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                       <div class="text-muted">
                            <i class="bi bi-file-earmark-text display-4 mb-3 d-block opacity-50"></i>
                            <p class="h5">No challans found</p>
                            <a href="{{ route('challans.create') }}" class="btn btn-sm btn-primary mt-2">Create New Challan</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($challans->hasPages())
    <div class="card-footer border-0 py-4">
        <div class="d-flex justify-content-end">
            {{ $challans->links() }}
        </div>
    </div>
    @endif
</div>


@endsection
