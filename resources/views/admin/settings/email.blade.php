@extends('layouts.main')

@section('title', 'Email Settings')

@section('content')
<div class="page-header">
    <h1>Email Settings</h1>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                Configuration
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form action="{{ route('admin.settings.email.update') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="mail_mailer" class="form-label">Mailer</label>
                        <select class="form-select" id="mail_mailer" name="mail_mailer">
                            <option value="smtp" {{ $settings['mail_mailer'] == 'smtp' ? 'selected' : '' }}>SMTP</option>
                            <option value="log" {{ $settings['mail_mailer'] == 'log' ? 'selected' : '' }}>Log (Testing)</option>
                        </select>
                    </div>

                    <div id="smtp-settings" style="{{ $settings['mail_mailer'] == 'log' ? 'display: none;' : '' }}">
                        <div class="mb-3">
                            <label for="mail_host" class="form-label">Host</label>
                            <input type="text" class="form-control" id="mail_host" name="mail_host" value="{{ $settings['mail_host'] }}" placeholder="ex: smtp.gmail.com">
                        </div>

                        <div class="mb-3">
                            <label for="mail_port" class="form-label">Port</label>
                            <input type="number" class="form-control" id="mail_port" name="mail_port" value="{{ $settings['mail_port'] }}" placeholder="ex: 587">
                            <div class="form-text">Common ports: 25, 465, 587</div>
                        </div>

                        <div class="mb-3">
                            <label for="mail_username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="mail_username" name="mail_username" value="{{ $settings['mail_username'] }}">
                        </div>

                        <div class="mb-3">
                            <label for="mail_password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="mail_password" name="mail_password" value="{{ $settings['mail_password'] }}">
                        </div>

                        <div class="mb-3">
                            <label for="mail_encryption" class="form-label">Encryption</label>
                            <select class="form-select" id="mail_encryption" name="mail_encryption">
                                <option value="tls" {{ $settings['mail_encryption'] == 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ $settings['mail_encryption'] == 'ssl' ? 'selected' : '' }}>SSL</option>
                                <option value="null" {{ empty($settings['mail_encryption']) || $settings['mail_encryption'] == 'null' ? 'selected' : '' }}>None</option>
                            </select>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label for="mail_from_address" class="form-label">From Address</label>
                        <input type="email" class="form-control" id="mail_from_address" name="mail_from_address" value="{{ $settings['mail_from_address'] }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="mail_from_name" class="form-label">From Name</label>
                        <input type="text" class="form-control" id="mail_from_name" name="mail_from_name" value="{{ $settings['mail_from_name'] }}" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('mail_mailer').addEventListener('change', function() {
        var smtpSettings = document.getElementById('smtp-settings');
        if (this.value === 'smtp') {
            smtpSettings.style.display = 'block';
        } else {
            smtpSettings.style.display = 'none';
        }
    });
</script>
@endpush
@endsection
