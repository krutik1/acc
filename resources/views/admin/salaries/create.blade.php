@extends('layouts.main')

@section('title', 'Generate Salary')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center bg-white py-3">
                <h5 class="mb-0">Generate Monthly Salary</h5>
                <a href="{{ route('admin.salaries.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back
                </a>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.salaries.calculate') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label">Month <span class="text-danger">*</span></label>
                        <input type="month" name="month" class="form-control" value="{{ date('Y-m') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Employee <span class="text-danger">*</span></label>
                        <select name="user_id" id="user_id" class="form-select" required onchange="checkSalaryType()">
                            <option value="">Select Employee</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" data-mode="{{ $emp->payment_mode }}">{{ $emp->name }} ({{ ucfirst($emp->payment_mode) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="pieces_input" class="mb-3" style="display: none;">
                        <label class="form-label">Total Pieces Completed <span class="text-danger">*</span></label>
                        <input type="number" name="total_pieces" class="form-control" min="0">
                        <div class="form-text">Required for Piece Rate employees.</div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Calculate & Preview</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function checkSalaryType() {
        const select = document.getElementById('user_id');
        const option = select.options[select.selectedIndex];
        const mode = option.getAttribute('data-mode');
        
        // Show pieces input if mode is 'pcs' (piece rate)
        // Also showing for 'trip' if we want manual entry? No, let's keep it simple as per prompt.
        // If mode is 'pcs', show input.
        const piecesDiv = document.getElementById('pieces_input');
        if (mode === 'pcs') {
            piecesDiv.style.display = 'block';
            piecesDiv.querySelector('input').required = true;
        } else {
            piecesDiv.style.display = 'none';
            piecesDiv.querySelector('input').required = false;
        }
    }
</script>
@endsection
