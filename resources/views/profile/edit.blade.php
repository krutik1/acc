@extends('layouts.main')

@section('title', 'Profile')

@section('content')
<div class="page-header">
    <h1>Profile</h1>
</div>

<div class="row">
    <div class="col-md-12 d-flex flex-column gap-4">
        <div class="card mb-4">
            <div class="card-header">
                Profile Information
            </div>
            <div class="card-body">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                Update Password
            </div>
            <div class="card-body">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="card mb-4 border-danger">
            <div class="card-header bg-danger text-white">
                Delete Account
            </div>
            <div class="card-body">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</div>
@endsection
