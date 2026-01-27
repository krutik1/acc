@extends('install.layout')

@section('content')
<h5 class="mb-4">2. Folder Permissions</h5>

<ul class="list-group mb-4">
    @foreach($permissions as $label => $met)
    <li class="list-group-item d-flex justify-content-between align-items-center">
        {{ $label }}
        @if($met)
            <span class="badge bg-success rounded-pill"><i class="bi bi-check"></i> Writable</span>
        @else
            <span class="badge bg-danger rounded-pill"><i class="bi bi-x"></i> Not Writable</span>
        @endif
    </li>
    @endforeach
</ul>

<div class="d-grid gap-2">
    @if($allMet)
        <a href="{{ route('install.database') }}" class="btn btn-primary">
            Next: Database Setup <i class="bi bi-arrow-right ms-2"></i>
        </a>
    @else
        <button class="btn btn-secondary" disabled>Please fix permissions to proceed</button>
        <a href="{{ route('install.permissions') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-clockwise sales-2"></i> Refresh
        </a>
    @endif
</div>
@endsection
