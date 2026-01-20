@extends('layouts.main')

@section('title', 'Parties')

@section('content')
<div class="page-header">
    <h1>Parties</h1>
    <a href="{{ route('parties.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Add Party
    </a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('parties.index') }}" class="row g-3">
            <div class="col-md-9 ms-auto">
                 <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search..." value="{{ request('search') }}">
                </div>
            </div>
             <div class="col-md-3 text-end">
                 <button type="submit" class="btn btn-primary me-2"><i class="bi bi-filter me-1"></i>Filter</button>
                 <a href="{{ route('parties.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Parties List -->
<div class="card">

    <div class="table-responsive">
        <table class="table align-items-center table-flush table-hover mb-0">
            <thead>
                <tr>
                    <th>Party Name</th>
                    <th>Contact</th>
                    <th>GST Number</th>
                    <th>Address</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($parties as $party)
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <span class="avatar avatar-sm rounded-circle bg-primary bg-opacity-10 text-primary me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                {{ substr($party->name, 0, 1) }}
                            </span>
                            <span class="fw-bold text-dark">{{ $party->name }}</span>
                        </div>
                    </td>
                    <td>{{ $party->contact_number }}</td>
                    <td>{{ $party->gst_number ?: '-' }}</td>
                    <td>{{ Str::limit($party->address, 30) }}</td>
                    <td class="text-end">
                        <a href="{{ route('parties.edit', $party) }}" class="action-btn bg-info me-1" title="Edit">
                            <i class="bi bi-pencil-fill text-white" style="font-size: 0.8rem;"></i>
                        </a>
                        <form action="{{ route('parties.destroy', $party) }}" method="POST" class="d-inline" 
                              onsubmit="return confirm('Are you sure you want to delete this party?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-btn bg-danger" title="Delete">
                                <i class="bi bi-trash-fill text-white" style="font-size: 0.8rem;"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5">
                        <div class="text-muted">
                            <i class="bi bi-people display-4 mb-3 d-block opacity-50"></i>
                            <p class="h5">No parties found</p>
                            <a href="{{ route('parties.create') }}" class="btn btn-sm btn-primary mt-2">Add New Party</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Card Footer for Pagination -->

</div>

@if($parties->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $parties->links() }}
</div>
@endif
@endsection
