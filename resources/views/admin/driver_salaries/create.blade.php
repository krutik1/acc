@extends('layouts.main')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Generate Driver Monthly Payment</h6>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('admin.driver-salaries.calculate') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="user_id">Select Driver</label>
                            <select name="user_id" id="user_id" class="form-control" required>
                                <option value="">-- Select Driver --</option>
                                @foreach($drivers as $driver)
                                    <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="month">Select Month</label>
                            <input type="month" name="month" id="month" class="form-control" required value="{{ date('Y-m') }}">
                        </div>

                        <div class="form-group text-right">
                            <a href="{{ route('admin.driver-salaries.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Calculate</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
