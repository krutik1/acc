@extends('install.layout')

@section('content')
<h5 class="mb-4">4. Create Admin Account</h5>

<form action="{{ route('install.admin.store') }}" method="POST">
    @csrf
    
    <div class="mb-3">
        <label class="form-label">Full Name</label>
        <input type="text" name="name" class="form-control" required placeholder="John Doe">
    </div>
    
    <div class="mb-3">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control" required placeholder="admin@example.com">
    </div>

    <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required minlength="8">
    </div>

    <div class="mb-3">
        <label class="form-label">Confirm Password</label>
        <input type="password" name="password_confirmation" class="form-control" required minlength="8">
    </div>

    <div class="d-grid gap-2 mt-4">
        <button type="submit" class="btn btn-primary">
            Complete Installation <i class="bi bi-check-circle ms-2"></i>
        </button>
    </div>
</form>
@endsection
