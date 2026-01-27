@extends('install.layout')

@section('content')
<div class="text-center">
    <i class="bi bi-box-seam text-primary" style="font-size: 4rem;"></i>
    <h4 class="mt-3">Welcome to the Installer</h4>
    <p class="mb-4">This wizard will guide you through the installation process.</p>
    
    <div class="d-grid gap-2">
        <a href="{{ route('install.requirements') }}" class="btn btn-primary btn-lg">
            Start Installation <i class="bi bi-arrow-right ms-2"></i>
        </a>
    </div>
</div>
@endsection
