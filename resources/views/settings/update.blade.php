@extends('layouts.main')

@section('title', 'System Update')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                System Update
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <h5 class="text-muted mb-3">Current Version</h5>
                    <div class="display-4 text-primary font-weight-bold">{{ $status['current_version'] }}</div>
                </div>

                <hr>

                <div class="mt-4">
                    @if($status['available'])
                        <div class="alert alert-info">
                            <h5 class="alert-heading"><i class="bi bi-info-circle me-2"></i>New Version Available: v{{ $status['latest_version'] }}</h5>
                            <hr>
                            <p class="mb-0" style="white-space: pre-line;">{{ $status['description'] }}</p>
                        </div>

                        <div class="text-center mt-4">
                            <form action="{{ route('admin.settings.updates.perform') }}" method="POST">
                                @csrf
                                <p class="text-muted mb-3">
                                    <small><i class="bi bi-exclamation-triangle"></i> This will backup the database, update the system, and automatically run maintenance tasks (migrations, storage check, cache clear).</small>
                                </p>
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="bi bi-cloud-arrow-down me-2"></i> Update Now
                                </button>
                            </form>
                        </div>
                    @else
                        @if(isset($status['message']))
                            <div class="alert alert-danger text-center">
                                <i class="bi bi-exclamation-circle me-2"></i> {{ $status['message'] }}
                            </div>
                        @endif

                        <div class="text-center text-muted">
                            <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">You are using the latest version.</h5>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
