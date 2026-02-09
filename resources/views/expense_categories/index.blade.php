@extends('layouts.main')

@section('title', 'Expense Categories')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 text-gray-800">Expense Categories</h1>
    <a href="{{ route('expense-categories.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Add Category
    </a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('expense-categories.index') }}" class="row g-3">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search categories..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Search</button>
            </div>
             @if(request('search'))
            <div class="col-md-2">
                 <a href="{{ route('expense-categories.index') }}" class="btn btn-outline-secondary w-100">Clear</a>
            </div>
            @endif
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table align-items-center table-flush table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th scope="col" class="ps-4">Name</th>
                    <th scope="col">Description</th>
                    <th scope="col" class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                <tr>
                    <td class="ps-4 fw-bold text-dark">{{ $category->name }}</td>
                    <td>{{ Str::limit($category->description, 50) ?: '-' }}</td>
                    <td class="text-end pe-4">
                        <a href="{{ route('expense-categories.edit', $category) }}" class="btn btn-sm btn-info text-white me-1" title="Edit">
                            <i class="bi bi-pencil-fill"></i>
                        </a>
                        <form action="{{ route('expense-categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this category?');">
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
                    <td colspan="3" class="text-center py-5">
                        <div class="text-muted">
                            <i class="bi bi-tags display-4 mb-3 d-block opacity-50"></i>
                            <p class="h5">No categories found</p>
                            <p class="small text-muted mb-0">Get started by creating a new category.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($categories->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $categories->links() }}
</div>
@endif
@endsection
