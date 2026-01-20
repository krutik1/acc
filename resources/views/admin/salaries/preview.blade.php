@extends('layouts.main')

@section('title', 'Preview Salary')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Preview Salary Calculation</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.salaries.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                    <input type="hidden" name="month" value="{{ $month }}">
                    <input type="hidden" name="salary_type" value="{{ $salaryType }}">
                    <input type="hidden" name="fixed_salary" value="{{ $fixedSalary }}">
                    <input type="hidden" name="piece_rate" value="{{ $pieceRate }}">
                    <input type="hidden" name="total_pieces" value="{{ $totalPieces }}">
                    <input type="hidden" name="total_amount" value="{{ $totalAmount }}">
                    <input type="hidden" name="total_upaad" value="{{ $totalUpaad }}">
                    <input type="hidden" name="payable_amount" value="{{ $payableAmount }}">

                    <div class="d-flex justify-content-between mb-3">
                        <strong>Employee:</strong>
                        <span>{{ $user->name }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Month:</strong>
                        <span>{{ \Carbon\Carbon::parse($month)->format('F Y') }}</span>
                    </div>
                    <hr>

                    <h6 class="text-muted">Earnings</h6>
                    @if($salaryType == 'fixed')
                        <div class="d-flex justify-content-between mb-2">
                            <span>Fixed Salary</span>
                            <span>₹{{ number_format($fixedSalary, 2) }}</span>
                        </div>
                    @else
                        <div class="d-flex justify-content-between mb-2">
                            <span>Piece Rate</span>
                            <span>{{ number_format($totalPieces) }} pcs × ₹{{ number_format($pieceRate, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Earnings</span>
                            <span>₹{{ number_format($totalAmount, 2) }}</span>
                        </div>
                    @endif

                    <h6 class="text-muted mt-3">Deductions</h6>
                    <div class="d-flex justify-content-between mb-2 text-danger">
                        <span>Advance (Upaad)</span>
                        <span>-₹{{ number_format($totalUpaad, 2) }}</span>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between mb-4">
                        <h5 class="mb-0">Net Payable Amount</h5>
                        <h4 class="mb-0 text-success">₹{{ number_format($payableAmount, 2) }}</h4>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Remarks (Optional)</label>
                        <textarea name="remarks" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('admin.salaries.create') }}" class="btn btn-secondary">Back</a>
                        <button type="submit" class="btn btn-success">Confirm & Generate Salary</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
