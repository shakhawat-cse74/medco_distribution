@extends('backend.layout.main')

@section('content')
<x-success-message key="message" />
<x-error-message key="not_permitted" />

<section>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">{{ __('db.Edit Delivery Sale') }} - {{ $lims_sale_data->reference_no }}</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('delivery-sale.update', $lims_sale_data->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{__('db.Warehouse')}} *</label>
                                <select name="warehouse_id" class="form-control selectpicker" data-live-search="true" required>
                                    @foreach($lims_warehouse_list as $warehouse)
                                        <option value="{{$warehouse->id}}" {{ $lims_sale_data->warehouse_id == $warehouse->id ? 'selected' : '' }}>{{$warehouse->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{__('db.customer')}} *</label>
                                <select name="customer_id" class="form-control selectpicker" data-live-search="true" required>
                                    @foreach($lims_customer_list as $customer)
                                        <option value="{{$customer->id}}" {{ $lims_sale_data->customer_id == $customer->id ? 'selected' : '' }}>{{$customer->name}} - {{$customer->phone_number}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{__('db.Biller')}}</label>
                                <select name="biller_id" class="form-control selectpicker" data-live-search="true">
                                    @foreach($lims_biller_list as $biller)
                                        <option value="{{$biller->id}}" {{ $lims_sale_data->biller_id == $biller->id ? 'selected' : '' }}>{{$biller->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{__('db.Route')}} *</label>
                                <select name="route_id" id="route_id" class="form-control selectpicker" data-live-search="true" required>
                                    @foreach($lims_route_list as $route)
                                        <option value="{{$route->id}}" {{ $lims_sale_data->deliveryMan && $lims_sale_data->deliveryMan->routes->contains($route->id) ? 'selected' : '' }}>{{$route->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{__('db.Delivery Man')}} *</label>
                                <select name="delivery_man_id" id="delivery_man_id" class="form-control selectpicker" data-live-search="true" required>
                                    @foreach($lims_delivery_man_list as $dm)
                                        <option value="{{$dm->id}}" {{ $lims_sale_data->delivery_man_id == $dm->id ? 'selected' : '' }}>{{$dm->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{__('db.Sale Status')}}</label>
                                <select name="sale_status" class="form-control">
                                    <option value="1" {{ $lims_sale_data->sale_status == 1 ? 'selected' : '' }}>{{__('db.Completed')}}</option>
                                    <option value="2" {{ $lims_sale_data->sale_status == 2 ? 'selected' : '' }}>{{__('db.Pending')}}</option>
                                    <option value="3" {{ $lims_sale_data->sale_status == 3 ? 'selected' : '' }}>{{__('db.Draft')}}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{__('db.Payment Status')}}</label>
                                <select name="payment_status" class="form-control">
                                    <option value="4" {{ $lims_sale_data->payment_status == 4 ? 'selected' : '' }}>{{__('db.Paid')}}</option>
                                    <option value="2" {{ $lims_sale_data->payment_status == 2 ? 'selected' : '' }}>{{__('db.Due')}}</option>
                                    <option value="3" {{ $lims_sale_data->payment_status == 3 ? 'selected' : '' }}>{{__('db.Partial')}}</option>
                                    <option value="1" {{ $lims_sale_data->payment_status == 1 ? 'selected' : '' }}>{{__('db.Pending')}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{__('db.Paid Amount')}}</label>
                                <input type="number" name="paid_amount" class="form-control" value="{{ $lims_sale_data->paid_amount }}" step="0.01">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>{{__('db.Sale Note')}}</label>
                                <textarea name="sale_note" class="form-control" rows="3">{{ $lims_sale_data->sale_note }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">{{__('db.update')}}</button>
                        <a href="{{ route('delivery-sale.index') }}" class="btn btn-secondary">{{__('db.Cancel')}}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
