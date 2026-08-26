@extends('backend.layout.main')

@section('content')
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">{{__('db.edit_payment')}}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('field-payments.index') }}">{{__('db.field_payments')}}</a></li>
                    <li class="breadcrumb-item active">{{__('db.edit_payment')}}</li>
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
                    <h5 class="m-b-0">{{__('db.edit_payment')}}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('field-payments.update', $lims_payment_data->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>{{__('db.order_details')}}</h6>
                                <p><strong>Order Ref:</strong> #{{ $lims_field_order_data->reference_no }}</p>
                                <p><strong>Customer:</strong> {{ $lims_field_order_data->customer->name }}</p>
                                <p><strong>Delivery Man:</strong> {{ $lims_field_order_data->deliveryMan->name }}</p>
                                <p><strong>Grand Total:</strong> {{ number_format($lims_field_order_data->grand_total, 2) }}</p>
                                <p><strong>Paid Amount:</strong> {{ number_format($lims_field_order_data->paid_amount, 2) }}</p>
                                <p><strong>Due Amount:</strong> {{ number_format($lims_field_order_data->due_amount, 2) }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6>{{__('db.payment_method')}}</h6>
                                <div class="form-group">
                                    <label>{{__('db.payment_method')}}</label>
                                    <select class="form-control" name="payment_method" id="payment_method" onchange="showPaymentDetails()">
                                        <option value="">Select Method</option>
                                        <option value="cash" {{ $lims_payment_data->payment_method == 'cash' ? 'selected' : '' }}>{{__('db.cash')}}</option>
                                        <option value="card" {{ $lims_payment_data->payment_method == 'card' ? 'selected' : '' }}>{{__('db.card')}}</option>
                                        <option value="cheque" {{ $lims_payment_data->payment_method == 'cheque' ? 'selected' : '' }}>{{__('db.cheque')}}</option>
                                        <option value="credit" {{ $lims_payment_data->payment_method == 'credit' ? 'selected' : '' }}>{{__('db.credit')}}</option>
                                        <option value="gift_card" {{ $lims_payment_data->payment_method == 'gift_card' ? 'selected' : '' }}>{{__('db.gift_card')}}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Cash Payment -->
                        <div id="cash_payment" class="payment-method-section">
                            <h6>{{__('db.cash_payment')}}</h6>
                            <div class="form-group">
                                <label>{{__('db.amount')}}</label>
                                <input type="number" class="form-control" name="amount" min="0" max="{{ $lims_field_order_data->due_amount }}" step="0.01" value="{{ $lims_payment_data->amount }}">
                            </div>
                        </div>
                        
                        <!-- Card Payment -->
                        <div id="card_payment" class="payment-method-section" style="display: none;">
                            <h6>{{__('db.card_payment')}}</h6>
                            <div class="form-group">
                                <label>{{__('db.amount')}}</label>
                                <input type="number" class="form-control" name="amount" min="0" max="{{ $lims_field_order_data->due_amount }}" step="0.01" value="{{ $lims_payment_data->amount }}">
                            </div>
                            <div class="form-group">
                                <label>{{__('db.card_type')}}</label>
                                <select class="form-control" name="card_type">
                                    <option value="visa" {{ $lims_payment_data->card_type == 'visa' ? 'selected' : '' }}>{{__('db.visa')}}</option>
                                    <option value="mastercard" {{ $lims_payment_data->card_type == 'mastercard' ? 'selected' : '' }}>{{__('db.mastercard')}}</option>
                                    <option value="amex" {{ $lims_payment_data->card_type == 'amex' ? 'selected' : '' }}>{{__('db.amex')}}</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>{{__('db.card_last_four')}}</label>
                                <input type="text" class="form-control" name="card_last_four" maxlength="4" value="{{ $lims_payment_data->card_last_four }}">
                            </div>
                            <div class="form-group">
                                <label>{{__('db.approval_code')}}</label>
                                <input type="text" class="form-control" name="approval_code" value="{{ $lims_payment_data->approval_code }}">
                            </div>
                        </div>
                        
                        <!-- Cheque Payment -->
                        <div id="cheque_payment" class="payment-method-section" style="display: none;">
                            <h6>{{__('db.cheque_payment')}}</h6>
                            <div class="form-group">
                                <label>{{__('db.amount')}}</label>
                                <input type="number" class="form-control" name="amount" min="0" max="{{ $lims_field_order_data->due_amount }}" step="0.01" value="{{ $lims_payment_data->amount }}">
                            </div>
                            <div class="form-group">
                                <label>{{__('db.cheque_no')}}</label>
                                <input type="text" class="form-control" name="cheque_no" value="{{ $lims_payment_data->cheque_no }}">
                            </div>
                            <div class="form-group">
                                <label>{{__('db.bank_name')}}</label>
                                <input type="text" class="form-control" name="bank_name" value="{{ $lims_payment_data->bank_name }}">
                            </div>
                            <div class="form-group">
                                <label>{{__('db.cheque_date')}}</label>
                                <input type="date" class="form-control" name="cheque_date" value="{{ $lims_payment_data->cheque_date }}">
                            </div>
                        </div>
                        
                        <!-- Credit/Due Payment -->
                        <div id="credit_payment" class="payment-method-section" style="display: none;">
                            <h6>{{__('db.credit_due_payment')}}</h6>
                            <div class="form-group">
                                <label>{{__('db.amount')}}</label>
                                <input type="number" class="form-control" name="amount" min="0" max="{{ $lims_field_order_data->due_amount }}" step="0.01" value="{{ $lims_payment_data->amount }}">
                            </div>
                        </div>
                        
                        <!-- Gift Card Payment -->
                        <div id="gift_card_payment" class="payment-method-section" style="display: none;">
                            <h6>{{__('db.gift_card_payment')}}</h6>
                            <div class="form-group">
                                <label>{{__('db.amount')}}</label>
                                <input type="number" class="form-control" name="amount" min="0" max="{{ $lims_field_order_data->due_amount }}" step="0.01" value="{{ $lims_payment_data->amount }}">
                            </div>
                            <div class="form-group">
                                <label>{{__('db.select_gift_card')}}</label>
                                <select class="form-control" name="gift_card_id">
                                    <option value="">{{__('db.select_card')}}</option>
                                    @foreach($lims_gift_card_list as $giftCard)
                                        <option value="{{ $giftCard->id }}" {{ $lims_payment_data->gift_card_id == $giftCard->id ? 'selected' : '' }}>{{ $giftCard->card_number }} - {{ number_format($giftCard->balance, 2) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>{{__('db.reference_no')}}</label>
                            <input type="text" class="form-control" name="reference_no" value="{{ $lims_payment_data->reference_no }}">
                        </div>
                        
                        <div class="form-group">
                            <label>{{__('db.note')}}</label>
                            <textarea class="form-control" name="note" rows="3">{{ $lims_payment_data->note }}</textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">{{__('db.update_payment')}}</button>
                        <a href="{{ route('field-payments.index') }}" class="btn btn-secondary">{{__('db.cancel')}}</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showPaymentDetails() {
    const method = document.getElementById('payment_method').value;
    const sections = document.getElementsByClassName('payment-method-section');
    
    for (let section of sections) {
        section.style.display = 'none';
    }
    
    if (method) {
        document.getElementById(method + '_payment').style.display = 'block';
    }
}

// Initialize based on current payment method
window.onload = function() {
    showPaymentDetails();
};
</script>
@endsection