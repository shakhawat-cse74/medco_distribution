@extends('backend.layout.main')

 @section('content')

<x-success-message key="message" />
<x-error-message key="not_permitted" />

<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">{{__('db.edit')}} {{__('db.Warehouse Product')}}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('warehouse-products.index') }}">{{__('db.Warehouse Products')}}</a></li>
                    <li class="breadcrumb-item active">{{__('db.edit')}}</li>
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
                    <h5 class="m-b-0">{{__('db.edit')}} {{__('db.Warehouse Product')}}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('warehouse-products.update', $lims_warehouse_product->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>{{__('db.Warehouse')}} <span class="text-danger">*</span></label>
                                <select class="form-control selectpicker" name="warehouse_id" data-live-search="true" required>
                                    <option value="">{{__('db.Select Warehouse')}}</option>
                                    @foreach($lims_warehouse_list as $warehouse)
                                        <option value="{{ $warehouse->id }}" {{ $lims_warehouse_product->warehouse_id == $warehouse->id ? 'selected' : '' }}>
                                            {{ $warehouse->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{__('db.Product')}} <span class="text-danger">*</span></label>
                                <select class="form-control selectpicker" name="product_id" data-live-search="true" required>
                                    <option value="">{{__('db.Select Product')}}</option>
                                    @foreach($lims_product_list as $product)
                                        <option value="{{ $product->id }}" {{ $lims_warehouse_product->product_id == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }} ({{ $product->code ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{__('db.Qty')}} <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="qty" value="{{ $lims_warehouse_product->qty }}" required min="0" step="0.01">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{__('db.Price')}} <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="price" value="{{ $lims_warehouse_product->price }}" required min="0" step="0.01">
                            </div>
                        </div>
                        <div class="form-group mt-3">
                            <button type="submit" class="btn btn-primary"><i class="ti ti-check"></i> {{__('db.update')}}</button>
                            <a href="{{ route('warehouse-products.index') }}" class="btn btn-secondary">{{__('db.cancel')}}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
