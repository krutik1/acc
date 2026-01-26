@extends('layouts.main')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Preview Driver Payment: {{ $user->name }} ({{ $month }})</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.driver-salaries.store') }}" method="POST" id="salaryForm">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}">
                <input type="hidden" name="month" value="{{ $month }}">
                <input type="hidden" name="total_trips" value="{{ $totalTrips }}">
                <input type="hidden" name="total_quantity" value="{{ $totalQuantity }}">
                <input type="hidden" name="fixed_trip_amount" value="{{ $fixedTripAmount }}">
                <input type="hidden" name="pcs_trip_amount" value="{{ $pcsTripAmount }}">
                
                <div class="row">
                    <div class="col-md-6 border-right">
                        <h5 class="text-gray-800 mb-3">Trip Summary</h5>
                        <table class="table table-sm table-bordered">
                            <tr>
                                <th>Fixed Trip Amount</th>
                                <td class="text-right">
                                    {{ number_format($fixedTripAmount, 2) }}
                                </td>
                            </tr>
                            <tr>
                                <th>PCS-Based Trip Amount</th>
                                <td class="text-right">
                                    {{ number_format($pcsTripAmount, 2) }}
                                </td>
                            </tr>
                            <tr class="table-active font-weight-bold">
                                <th>Total Trip Earnings</th>
                                <td class="text-right">
                                    <input type="number" step="0.01" name="total_amount" id="total_amount" class="form-control text-right font-weight-bold" value="{{ number_format($totalAmount, 2, '.', '') }}" readonly>
                                </td>
                            </tr>
                            <tr>
                                <th>Less: Advance Payment</th>
                                <td class="text-right text-danger">
                                    <input type="number" step="0.01" name="advance_amount" id="advance_amount" class="form-control text-right text-danger" value="{{ number_format($advanceAmount, 2, '.', '') }}" readonly>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <h5 class="text-gray-800 mb-3">Adjustments</h5>
                        <div class="form-group row">
                            <label class="col-sm-6 col-form-label">Bonus (+)</label>
                            <div class="col-sm-6">
                                <input type="number" step="0.01" name="bonus" id="bonus" class="form-control text-right" value="0.00" oninput="calculateNetPayable()">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-6 col-form-label">Deduction (-)</label>
                            <div class="col-sm-6">
                                <input type="number" step="0.01" name="deduction" id="deduction" class="form-control text-right" value="0.00" oninput="calculateNetPayable()">
                            </div>
                        </div>
                        <hr>
                        <div class="form-group row">
                            <label class="col-sm-6 col-form-label font-weight-bold">Net Payable</label>
                            <div class="col-sm-6">
                                <input type="number" step="0.01" name="payable_amount" id="payable_amount" class="form-control text-right font-weight-bold" value="{{ number_format($payableAmount, 2, '.', '') }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group mt-3">
                    <label>Remarks</label>
                    <textarea name="remarks" class="form-control" rows="2"></textarea>
                </div>

                <div class="text-right mt-4">
                    <a href="{{ route('admin.driver-salaries.create') }}" class="btn btn-secondary">Back</a>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg"></i> Finalize & Generate
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function calculateNetPayable() {
        let totalAmount = parseFloat(document.getElementById('total_amount').value) || 0;
        let advanceAmount = parseFloat(document.getElementById('advance_amount').value) || 0;
        let bonus = parseFloat(document.getElementById('bonus').value) || 0;
        let deduction = parseFloat(document.getElementById('deduction').value) || 0;

        let netPayable = (totalAmount - advanceAmount) + bonus - deduction;
        
        document.getElementById('payable_amount').value = netPayable.toFixed(2);
    }
</script>
@endsection
