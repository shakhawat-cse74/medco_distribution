@extends('backend.layout.main')

@section('content')
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">{{__('db.payment_details')}}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('field-payments.index') }}">{{__('db.field_payments')}}</a></li>
                    <li class="breadcrumb-item active">{{__('db.view_payment')}}</li>
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
                    <h5 class="m-b-0">{{__('db.payment_details')}}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>{{__('db.field_order_info')}}</h6>
                            <p><strong>{{__('db.order_reference')}}</strong> #{{ $lims_payment_data->fieldOrder->order_number ?? 'N/A' }}</p>
                            <p><strong>{{__('db.date')}}</strong> {{ $lims_payment_data->fieldOrder->created_at->format('Y-m-d') ?? 'N/A' }}</p>
                            <p><strong>{{__('db.customer')}}</strong> {{ $lims_payment_data->fieldOrder->customer->name ?? 'N/A' }}</p>
                            <p><strong>{{__('db.delivery_man')}}</strong> {{ $lims_payment_data->fieldOrder->deliveryMan->name ?? 'N/A' }}</p>
                            <p><strong>{{__('db.status')}}</strong>
                                @if($lims_payment_data->fieldOrder->status == 'paid')
                                    <span class="badge badge-success">{{__('db.paid')}}</span>
                                @elseif($lims_payment_data->fieldOrder->status == 'partial')
                                    <span class="badge badge-warning">{{__('db.partial')}}</span>
                                @else
                                    <span class="badge badge-danger">{{__('db.pending')}}</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6>{{__('db.payment_info')}}</h6>
                            <p><strong>{{__('db.payment_method')}}</strong> {{ $lims_payment_data->payment_method }}</p>
                            <p><strong>{{__('db.amount')}}</strong> {{ number_format($lims_payment_data->amount, 2) }}</p>
                            <p><strong>{{__('db.reference')}}</strong> {{ $lims_payment_data->reference_no ?? 'N/A' }}</p>
                            <p><strong>{{__('db.date')}}</strong> {{ $lims_payment_data->created_at->format('Y-m-d H:i:s') }}</p>
                        </div>
                    </div>
                    
                    @if($lims_payment_data->payment_method == 'card')
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h6>{{__('db.card_details')}}</h6>
                            <p><strong>{{__('db.card_type')}}</strong> {{ $lims_payment_data->card_type ?? 'N/A' }}</p>
                            <p><strong>{{__('db.card_last_four')}}</strong> {{ $lims_payment_data->card_last_four ?? 'N/A' }}</p>
                            <p><strong>{{__('db.approval_code')}}</strong> {{ $lims_payment_data->approval_code ?? 'N/A' }}</p>
                        </div>
                    </div>
                    @endif
                    
                    @if($lims_payment_data->payment_method == 'cheque')
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h6>{{__('db.cheque_details')}}</h6>
                            <p><strong>{{__('db.cheque_no')}}</strong> {{ $lims_payment_data->cheque_no ?? 'N/A' }}</p>
                            <p><strong>{{__('db.bank_name')}}</strong> {{ $lims_payment_data->bank_name ?? 'N/A' }}</p>
                            <p><strong>{{__('db.cheque_date')}}</strong> {{ $lims_payment_data->cheque_date ?? 'N/A' }}</p>
                        </div>
                    </div>
                    @endif
                    
                    @if($lims_payment_data->payment_method == 'gift_card')
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h6>{{__('db.gift_card_info')}}</h6>
                            <p><strong>{{__('db.gift_card_id')}}</strong> {{ $lims_payment_data->giftCard->card_number ?? 'N/A' }}</p>
                            <p><strong>{{__('db.balance')}}</strong> {{ number_format($lims_payment_data->giftCard->balance ?? 0, 2) }}</p>
                        </div>
                    </div>
                    @endif
                    
                    @if($lims_payment_data->note)
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <h6>{{__('db.note')}}</h6>
                            <p>{{ $lims_payment_data->note }}</p>
                        </div>
                    </div>
                    @endif
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <a href="{{ route('field-payments.receipt', $lims_payment_data->id) }}" class="btn btn-primary" target="_blank">
                                <i class="ti ti-printer"></i> {{__('db.print_receipt')}}
                            </a>
                            <a href="{{ route('field-payments.sendReceipt', $lims_payment_data->id) }}" class="btn btn-success">
                                <i class="ti ti-mail"></i> {{__('db.send_receipt')}}
                            </a>
                            <a href="{{ route('field-payments.index') }}" class="btn btn-secondary">
                                <i class="ti ti-arrow-left"></i> {{__('db.back_to_list')}}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection