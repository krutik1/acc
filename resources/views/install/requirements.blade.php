@extends('install.layout')

@section('content')
<h5 class="mb-4">1. Server Requirements</h5>

<ul class="list-group mb-4">
    @foreach($requirements as $label => $met)
    <li class="list-group-item d-flex justify-content-between align-items-center">
        {{ $label }}
        @if($met)
            <span class="badge bg-success rounded-pill"><i class="bi bi-check"></i> OK</span>
        @else
            <span class="badge bg-danger rounded-pill"><i class="bi bi-x"></i> Fail</span>
        @endif
    </li>
    @endforeach
</ul>

<div class="d-grid gap-2">
    @if($allMet)
        <a href="{{ route('install.permissions') }}" class="btn btn-primary">
            Next: Permissions <i class="bi bi-arrow-right ms-2"></i>
        </a>
    @else
        <button class="btn btn-secondary" disabled>Please fix requirements to proceed</button>
        <a href="{{ route('install.requirements') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-clockwise sales-2"></i> Refresh
        </a>
    @endif
</div>
@endsection
