@extends('layouts.main')

@section('title', 'Share Invoice')

@section('content')
<div class="page-header">
    <h1>Share Invoice via Email</h1>
</div>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">Invoices</a></li>
        <li class="breadcrumb-item active">Share Invoice #{{ $invoice->invoice_number }}</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="bi bi-envelope me-1"></i>
            Compose Email
        </div>
        <div class="card-body">
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form action="{{ route('invoices.share.email.send', $invoice) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="recipient" class="form-label">To (Email Address) <span class="text-danger">*</span></label>
                    <input type="email" class="form-control @error('recipient') is-invalid @enderror" id="recipient" name="recipient" value="{{ old('recipient', $invoice->party->email) }}" required>
                    @error('recipient')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="cc" class="form-label">CC (Optional)</label>
                    <input type="email" class="form-control @error('cc') is-invalid @enderror" id="cc" name="cc" value="{{ old('cc') }}">
                    @error('cc')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Separate multiple emails with commas.</div>
                </div>

                <div class="mb-3">
                    <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('subject') is-invalid @enderror" id="subject" name="subject" value="{{ old('subject', 'Invoice ' . $invoice->invoice_number . ' from ' . $invoice->company->name) }}" required>
                    @error('subject')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="body" class="form-label">Message <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('body') is-invalid @enderror" id="body" name="body" rows="6" required>{{ old('body', "Dear " . $invoice->party->name . ",\n\nPlease find attached the invoice " . $invoice->invoice_number . " dated " . $invoice->invoice_date . ".\n\nIf you have any questions, feel free to contact us.\n\nRegards,\n" . $invoice->company->name) }}</textarea>
                    @error('body')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Attachment</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-file-earmark-pdf"></i></span>
                        <input type="text" class="form-control" value="Invoice-{{ $invoice->invoice_number }}.pdf" disabled readonly>
                        <span class="input-group-text text-muted">Auto-attached</span>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('invoices.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-send-fill me-1"></i> Send Email</button>
                </div>
            </form>
        </div>
    </div>
@endsection
