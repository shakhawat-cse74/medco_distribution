@extends('backend.layout.main')

@section('content')
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">{{__('db.field_payments')}}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item active">{{__('db.field_payments')}}</li>
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
                    <h5 class="m-b-0">{{__('db.field_payments')}}</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="delivery_man_filter">{{__('db.filter_by_delivery_man')}}</label>
                            <select class="form-control" id="delivery_man_filter">
                                <option value="">{{__('db.all')}}</option>
                                @foreach($lims_delivery_man_list as $dm)
                                    <option value="{{ $dm->id }}">{{ $dm->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-striped" id="fieldPaymentTable">
                            <thead>
                                <tr>
                                    <th>{{__('db.reference')}}</th>
                                    <th>{{__('db.field_order')}}</th>
                                    <th>{{__('db.delivery_man')}}</th>
                                    <th>{{__('db.customer')}}</th>
                                    <th>{{__('db.payment_method')}}</th>
                                    <th>{{__('db.amount')}}</th>
                                    <th>{{__('db.date')}}</th>
                                    <th>{{__('db.status')}}</th>
                                    <th>{{__('db.actions')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lims_payment_list as $payment)
                                <tr>
                                    <td>{{ $payment->reference_no }}</td>
                                    <td>#{{ $payment->fieldOrder->order_number ?? 'N/A' }}</td>
                                    <td>{{ $payment->fieldOrder->deliveryMan->name ?? 'N/A' }}</td>
                                    <td>{{ $payment->fieldOrder->customer->name ?? 'N/A' }}</td>
                                    <td>{{ $payment->payment_method }}</td>
                                    <td>{{ number_format($payment->amount, 2) }}</td>
                                    <td>{{ $payment->created_at->format('Y-m-d') }}</td>
                                    <td>
                                        @if($payment->fieldOrder->status == 'paid')
                                            <span class="badge badge-success">{{__('db.paid')}}</span>
                                        @elseif($payment->fieldOrder->status == 'partial')
                                            <span class="badge badge-warning">{{__('db.partial')}}</span>
                                        @else
                                            <span class="badge badge-danger">{{__('db.pending')}}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('field-payments.show', $payment->id) }}" class="btn btn-info btn-sm" title="{{__('db.view')}}">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                        <a href="{{ route('field-payments.edit', $payment->id) }}" class="btn btn-primary btn-sm" title="{{__('db.edit')}}">
                                            <i class="ti ti-edit"></i>
                                        </a>
                                        @if($all_permission && in_array('field-payments-delete', $all_permission))
                                            <form class="d-inline" action="{{ route('field-payments.destroy', $payment->id) }}" method="POST" onsubmit="return confirm('{{__('db.are_you_sure')}}');">
                                                <input type="hidden" name="_method" value="DELETE">
                                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                <button type="submit" class="btn btn-danger btn-sm" title="{{__('db.delete')}}">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection