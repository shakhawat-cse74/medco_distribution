@extends('backend.layout.main')

@section('content')
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">Field Return</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item active">Field Return</li>
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
                    <h5 class="m-b-0">Initiate Field Return</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('field-returns.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="field_order_id">Field Order <span class="text-danger">*</span></label>
                                <select class="form-control selectpicker" id="field_order_id" name="field_order_id" data-live-search="true" required>
                                    <option value="">Select Field Order</option>
                                    @foreach($lims_field_orders as $order)
                                        <option value="{{ $order->id }}">{{ $order->reference_no }} - {{ $order->grand_total }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="delivery_man_id">Delivery Man <span class="text-danger">*</span></label>
                                <select class="form-control selectpicker" id="delivery_man_id" name="delivery_man_id" data-live-search="true" required>
                                    <option value="">Select Delivery Man</option>
                                    @foreach($lims_delivery_men as $dm)
                                        <option value="{{ $dm->id }}">{{ $dm->name }} - {{ $dm->phone_number }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="customer_id">Customer <span class="text-danger">*</span></label>
                                <select class="form-control selectpicker" id="customer_id" name="customer_id" data-live-search="true" required>
                                    <option value="">Select Customer</option>
                                    @foreach($lims_customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }} - {{ $customer->phone_number }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="return_reason">Return Reason <span class="text-danger">*</span></label>
                                <select class="form-control selectpicker" id="return_reason" name="return_reason" data-live-search="true" required>
                                    <option value="">Select Reason</option>
                                    <option value="damaged">Damaged Product</option>
                                    <option value="wrong_product">Wrong Product Delivered</option>
                                    <option value="customer_refused">Customer Refused</option>
                                    <option value="expired">Product Expired</option>
                                    <option value="quality_issue">Quality Issue</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>

                        <hr>

                        <h6>Returned Products</h6>
                        <div class="table-responsive">
                            <table class="table table-striped" id="returnedProductsTable">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Code</th>
                                        <th>Qty Returned</th>
                                        <th>Unit Price</th>
                                        <th>Sub Total</th>
                                        <th>Photo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <!-- Products will be added dynamically -->
                                </tbody>
                            </table>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Refund Amount</label>
                                <input type="text" class="form-control" readonly>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Status</label>
                                <select class="form-control" readonly>
                                    <option value="pending">Pending</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Create Return</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection