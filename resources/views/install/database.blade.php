@extends('install.layout')

@section('content')
<h5 class="mb-4">3. Database Configuration</h5>

<form action="{{ route('install.database.store') }}" method="POST">
    @csrf
    
    <div class="mb-3">
        <label class="form-label">Database Host</label>
        <input type="text" name="host" class="form-control" value="127.0.0.1" required>
    </div>
    
    <div class="mb-3">
        <label class="form-label">Database Port</label>
        <input type="text" name="port" class="form-control" value="3306" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Database Name</label>
        <input type="text" name="database" class="form-control" placeholder="invoice_db" required>
        <div class="form-text">Please ensure this database exists.</div>
    </div>

    <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" value="root" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" placeholder="">
    </div>

    <div class="d-grid gap-2 mt-4">
        <button type="submit" class="btn btn-primary">
            Save & Migrate <i class="bi bi-arrow-right ms-2"></i>
        </button>
    </div>
</form>
@endsection
