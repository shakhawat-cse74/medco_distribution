@extends('backend.layout.main')

@section('content')
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">Cash Deposit</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item active">Cash Deposit</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="page-block">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="m-b-0">Record Cash Deposit</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('cash-deposits.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="delivery_man_id">Delivery Man <span class="text-danger">*</span></label>
                                <select class="form-control selectpicker" id="delivery_man_id" name="delivery_man_id" data-live-search="true" required>
                                    <option value="">Select Delivery Man</option>
                                    @foreach($lims_delivery_men as $dm)
                                        <option value="{{ $dm->id }}">{{ $dm->name }} - {{ $dm->phone_number }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="amount">Amount <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="deposit_method">Deposit Method <span class="text-danger">*</span></label>
                                <select class="form-control selectpicker" id="deposit_method" name="deposit_method" data-live-search="true" required>
                                    <option value="">Select Method</option>
                                    <option value="cash">Cash</option>
                                    <option value="bank">Bank Transfer</option>
                                    <option value="check">Cheque</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group" id="bank_fields" style="display:none;">
                                <label for="bank_name">Bank Name</label>
                                <input type="text" class="form-control" id="bank_name" name="bank_name" required>
                            </div>
                            <div class="col-md-6 form-group" id="check_fields" style="display:none;">
                                <label for="cheque_no">Cheque Number</label>
                                <input type="text" class="form-control" id="cheque_no" name="cheque_no">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 form-group">
                                <label for="reference_no">Reference Number</label>
                                <input type="text" class="form-control" id="reference_no" name="reference_no">
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-12 form-group">
                                <label>Note</label>
                                <textarea class="form-control" rows="3" name="note" placeholder="Additional notes"></textarea>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Record Deposit</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection