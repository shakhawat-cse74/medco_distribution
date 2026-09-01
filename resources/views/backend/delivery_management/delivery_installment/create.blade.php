@extends('backend.layout.main')
@section('content')
@push('css')
<style>
    @media print {
        .hidden-print {
            display: none !important;
        }
    }
    .sale-section {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        padding: 20px 20px 10px 20px;
        margin-bottom: 20px;
        background: #fff;
    }
</style>
@endpush

<x-error-message key="not_permitted" />
<x-error-message key="error" />

<section id="pos-layout" class="forms hidden-print">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <h4>{{__('db.Add Installment')}}</h4>
                <p class="italic"><small>{{__('db.The field labels marked with are required input fields')}}.</small></p>
                <form action="{{ route('delivery-installment.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{__('db.date')}}</label>
                                        <input type="text" name="created_at" class="form-control date" placeholder="{{ __('db.Choose date') }}" value="{{date(gen_setting()->date_format,strtotime('now'))}}" />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{__('db.Reference No')}}</label>
                                        <input type="text" name="reference_no" class="form-control" />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{__('db.customer')}} *</label>
                                        <select name="customer_id" id="customer_id" class="selectpicker form-control" data-live-search="true" required>
                                            @foreach($lims_customer_list as $customer)
                                            <option value="{{$customer->id}}">{{$customer->name}} @if($customer->phone_number)({{$customer->phone_number}})@endif</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{__('db.Warehouse')}} *</label>
                                        <select name="warehouse_id" id="warehouse_id" class="selectpicker form-control" data-live-search="true" required>
                                            @foreach($lims_warehouse_list as $warehouse)
                                            <option value="{{$warehouse->id}}">{{$warehouse->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{__('db.Route')}} *</label>
                                        <select name="route_id" id="route_id" class="form-control selectpicker" data-live-search="true" required>
                                            @foreach($lims_route_list as $route)
                                            <option value="{{$route->id}}">{{$route->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{__('db.Delivery Man')}} *</label>
                                        <select name="delivery_man_id" id="delivery_man_id" class="form-control selectpicker" data-live-search="true" required>
                                            @foreach($lims_delivery_man_list as $dm)
                                            <option value="{{$dm->id}}">{{$dm->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{__('db.Total Amount')}} *</label>
                                        <input type="number" name="grand_total" id="grand_total" class="form-control" step="0.01" min="0" required />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{__('db.Down Payment')}} *</label>
                                        <input type="number" name="down_payment" id="down_payment" class="form-control" step="0.01" min="0" value="0" required />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{__('db.Installment Amount')}} *</label>
                                        <input type="number" name="installment_amount" id="installment_amount" class="form-control" step="0.01" min="0" required />
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{__('db.Number of Months')}} *</label>
                                        <input type="number" name="installment_months" id="installment_months" class="form-control" min="1" value="12" required />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{__('db.Start Date')}} *</label>
                                        <input type="text" name="start_date" id="start_date" class="form-control date" required />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{__('db.Payment Method')}} *</label>
                                        <select name="payment_method" id="payment_method" class="form-control" required>
                                            <option value="cash">Cash</option>
                                            <option value="bank">Bank</option>
                                            <option value="cheque">Cheque</option>
                                            <option value="card">Card</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{__('db.Note')}}</label>
                                        <textarea name="note" rows="3" class="form-control"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{__('db.Staff Note')}}</label>
                                        <textarea name="staff_note" rows="3" class="form-control"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">{{__('db.submit')}}</button>
                        <a href="{{ route('delivery-installment.index') }}" class="btn btn-secondary">{{__('db.Cancel')}}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script type="text/javascript">
    $(document).ready(function() {
        $('.date').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true
        });
        
        // Calculate installment amount when total or down payment changes
        $('#grand_total, #down_payment, #installment_months').on('input', function() {
            calculateInstallment();
        });
        
        function calculateInstallment() {
            var total = parseFloat($('#grand_total').val()) || 0;
            var downPayment = parseFloat($('#down_payment').val()) || 0;
            var months = parseInt($('#installment_months').val()) || 1;
            
            if (total > 0 && months > 0) {
                var remaining = total - downPayment;
                var installment = remaining / months;
                $('#installment_amount').val(installment.toFixed(2));
            }
        }
    });
</script>
@endpush
