@extends('layouts.main')

@section('title', 'Companies')

@section('content')
<div class="page-header">
    <h1>Companies</h1>
    <a href="{{ route('companies.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Add Company
    </a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-9 ms-auto">
                 <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0" placeholder="Search functionality to be implemented if needed..." disabled>
                </div>
            </div>
             <div class="col-md-3 text-end">
                <!-- Search in companies is processed via collection or future backend implementation. 
                     For now, sticking to a static look or future-proofing. 
                     Since the controller doesn't handle search, I'll just keep a simple placeholder or similar. 
                     Actually, the user asked for UI consistency. Let's make it look like others. -->
                 <a href="{{ route('companies.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </div>
    </div>
</div>

<!-- Companies List -->
<div class="card">

    <div class="table-responsive">
        <table class="table align-items-center table-flush table-hover mb-0">
            <thead>
                <tr>
                    <th>Company Name</th>
                    <th>GST Number</th>
                    <th>State Code</th>
                    <th>Address</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($companies as $company)
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <span class="avatar avatar-sm rounded-circle bg-primary bg-opacity-10 text-primary me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                {{ substr($company->name, 0, 1) }}
                            </span>
                            <span class="fw-bold text-dark">{{ $company->name }}</span>
                        </div>
                    </td>
                    <td>{{ $company->gst_number ?: '-' }}</td>
                    <td>{{ $company->state_code ?: '-' }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($company->address, 30) }}</td>
                    <td>
                        @if($company->is_default)
                            <span class="badge bg-success">Default</span>
                        @else
                            <span class="badge bg-light text-muted border">-</span>
                        @endif
                    </td>
                    <td class="text-end">
                        @if(session('selected_company_id') != $company->id)
                        <form action="{{ route('companies.select.store') }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="company_id" value="{{ $company->id }}">
                            <button type="submit" class="action-btn bg-success me-1" title="Switch to this Company">
                                <i class="bi bi-arrow-repeat text-white" style="font-size: 0.8rem;"></i>
                            </button>
                        </form>
                        @endif
                        
                        <a href="{{ route('companies.show', $company) }}" class="action-btn bg-warning me-1" title="View">
                            <i class="bi bi-eye-fill text-white" style="font-size: 0.8rem;"></i>
                        </a>
                        <a href="{{ route('companies.edit', $company) }}" class="action-btn bg-info me-1" title="Edit">
                            <i class="bi bi-pencil-fill text-white" style="font-size: 0.8rem;"></i>
                        </a>
                        <form action="{{ route('companies.destroy', $company) }}" method="POST" class="d-inline" 
                              onsubmit="return confirm('Are you sure you want to delete this company?');">
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
                    <td colspan="6" class="text-center py-5">
                        <div class="text-muted">
                            <i class="bi bi-building display-4 mb-3 d-block opacity-50"></i>
                            <p class="h5">No companies found</p>
                            <a href="{{ route('companies.create') }}" class="btn btn-sm btn-primary mt-2">Add New Company</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</div>

@if($companies->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $companies->links() }}
</div>
@endif
@endsection
