@extends('backend.layout.main')

@section('content')
<x-success-message key="message" />
<x-error-message key="not_permitted" />

<section>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header mt-2">
                <h3 class="text-center">{{ __('db.Delivery Sale Exchange Details') }}</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <th>{{ __('db.date') }}</th>
                            <th>{{ __('db.reference') }}</th>
                            <th>{{ __('db.Sale Reference') }}</th>
                            <th>{{ __('db.customer') }}</th>
                            <th>{{ __('db.Warehouse') }}</th>
                            <th>{{ __('db.Delivery Man') }}</th>
                            <th>{{ __('db.Type') }}</th>
                            <th>{{ __('db.Amount') }}</th>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ date(config('date_format'), strtotime($lims_exchange_data->created_at)) }}</td>
                                <td>{{ $lims_exchange_data->reference_no }}</td>
                                <td>{{ $lims_exchange_data->sale ? $lims_exchange_data->sale->reference_no : 'N/A' }}</td>
                                <td>{{ $lims_exchange_data->customer->name ?? 'N/A' }}</td>
                                <td>{{ $lims_exchange_data->warehouse->name ?? 'N/A' }}</td>
                                <td>{{ $lims_exchange_data->sale && $lims_exchange_data->sale->deliveryMan ? $lims_exchange_data->sale->deliveryMan->name : 'N/A' }}</td>
                                <td>{!! $lims_exchange_data->payment_type == 'pay' ? '<span class="badge badge-danger">Pay</span>' : '<span class="badge badge-success">Receive</span>' !!}</td>
                                <td>{{ number_format($lims_exchange_data->amount, config('decimal')) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
