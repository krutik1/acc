@extends('layouts.main')

@section('title', 'Drivers')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <h1>Drivers</h1>
    <a href="{{ route('admin.drivers.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Add Driver
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Added On</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($drivers as $driver)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-2 bg-light rounded-circle d-flex align-items-center justify-content-center text-primary" style="width: 32px; height: 32px;">
                                        <i class="bi bi-person"></i>
                                    </div>
                                    <span class="fw-medium">{{ $driver->name }}</span>
                                </div>
                            </td>
                            <td>{{ $driver->email }}</td>
                            <td>{{ $driver->created_at->format('Y-m-d') }}</td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    {{-- Block/Unblock --}}
                                    <form action="{{ route('admin.drivers.toggle-block', $driver) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm {{ $driver->is_blocked ? 'btn-outline-danger' : 'btn-outline-success' }} border-0" 
                                            onclick="return confirm('Are you sure you want to {{ $driver->is_blocked ? 'unblock' : 'block' }} this driver?')" 
                                            title="{{ $driver->is_blocked ? 'Unblock' : 'Block' }}">
                                            <i class="bi {{ $driver->is_blocked ? 'bi-lock-fill' : 'bi-unlock' }}"></i>
                                        </button>
                                    </form>

                                    {{-- Edit --}}
                                    <a href="{{ route('admin.drivers.edit', $driver) }}" class="btn btn-sm btn-outline-primary border-0" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    {{-- Delete --}}
                                    <form action="{{ route('admin.drivers.destroy', $driver) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger border-0" onclick="return confirm('Are you sure you want to delete this driver? All their trips will also be deleted.')" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">
                                <i class="bi bi-person-x display-6 d-block mb-2"></i>
                                No drivers found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($drivers->hasPages())
            <div class="card-footer bg-white border-top-0">
                {{ $drivers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
