@extends('backend.layout.main')

@section('content')
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">{{__('db.split_payment')}}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('field-payments.index') }}">{{__('db.field_payments')}}</a></li>
                    <li class="breadcrumb-item active">{{__('db.split_payment')}}</li>
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
                    <h5 class="m-b-0">{{__('db.split_payment')}}</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <strong>{{__('db.order_info')}}</strong><br>
                                Order #{{ $lims_field_order_data->order_number }}<br>
                                Customer: {{ $lims_field_order_data->customer->name }}<br>
                                Due Amount: {{ number_format($lims_field_order_data->due_amount, 2) }}
                            </div>
                        </div>
                    </div>
                    
                    <form action="{{ route('field-payments.store') }}" method="POST" id="splitPaymentForm">
                        @csrf
                        <input type="hidden" name="field_order_id" value="{{ $lims_field_order_data->id }}">
                        
                        <div id="payment-methods-container">
                            <!-- Payment methods will be added dynamically -->
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{__('db.note')}}</label>
                                    <textarea class="form-control" name="note" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="m-b-0">{{__('db.payment_summary')}}</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <strong>{{__('db.total_paid')}}</strong>
                                            </div>
                                            <div class="col-6 text-right">
                                                <span id="total_paid">0.00</span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <strong>{{__('db.remaining_due')}}</strong>
                                            </div>
                                            <div class="col-6 text-right">
                                                <span id="remaining_due">{{ number_format($lims_field_order_data->due_amount, 2) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary" id="submit_split_payment" disabled>{{__('db.submit_split_payment')}}</button>
                                <button type="button" class="btn btn-secondary" onclick="addPaymentMethod()">+ {{__('db.add_payment_method')}}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let paymentMethodCount = 0;

function addPaymentMethod() {
    const container = document.getElementById('payment-methods-container');
    const dueAmount = {{ $lims_field_order_data->due_amount }};
    
    const methodHtml = `
        <div class="payment-method-row card mb-3" id="payment-method-${paymentMethodCount}">
            <div class="card-header">
                <h6 class="m-b-0">Payment Method ${paymentMethodCount + 1}</h6>
                <button type="button" class="btn btn-danger btn-sm float-right" onclick="removePaymentMethod(${paymentMethodCount})">
                    <i class="ti ti-trash"></i>
                </button>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Payment Method</label>
                            <select class="form-control" name="payment_methods[${paymentMethodCount}][method]" onchange="updatePaymentFields(this, ${paymentMethodCount})" required>
                                <option value="">{{__('db.select_method')}}</option>
                                <option value="cash">{{__('db.cash')}}</option>
                                <option value="card">{{__('db.card')}}</option>
                                <option value="cheque">{{__('db.cheque')}}</option>
                                <option value="credit">{{__('db.credit')}}</option>
                                <option value="gift_card">{{__('db.gift_card')}}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Amount</label>
                            <input type="number" class="form-control" name="payment_methods[${paymentMethodCount}][amount]" 
                                   min="0" max="${dueAmount}" step="0.01" oninput="calculateTotals()" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Note</label>
                            <input type="text" class="form-control" name="payment_methods[${paymentMethodCount}][note]">
                        </div>
                    </div>
                </div>
                
                <div id="fields-${paymentMethodCount}" class="mt-3">
                    <!-- Additional fields will be populated based on payment method -->
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', methodHtml);
    updatePaymentFields(container.lastElementChild.querySelector('select'), paymentMethodCount);
    paymentMethodCount++;
    calculateTotals();
}

function removePaymentMethod(index) {
    document.getElementById(`payment-method-${index}`).remove();
    calculateTotals();
}

function updatePaymentFields(selectElement, index) {
    const method = selectElement.value;
    const fieldsContainer = document.getElementById(`fields-${index}`);
    let fieldsHtml = '';
    
    fieldsContainer.innerHTML = '';
    
    if (method === 'card') {
        fieldsHtml = `
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Card Type</label>
                        <select class="form-control" name="payment_methods[${index}][card_type]">
                            <option value="visa">Visa</option>
                            <option value="mastercard">Mastercard</option>
                            <option value="amex">Amex</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Last Four</label>
                        <input type="text" class="form-control" name="payment_methods[${index}][card_last_four]" maxlength="4">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Approval Code</label>
                        <input type="text" class="form-control" name="payment_methods[${index}][approval_code]">
                    </div>
                </div>
            </div>
        `;
    } else if (method === 'cheque') {
        fieldsHtml = `
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Cheque No</label>
                        <input type="text" class="form-control" name="payment_methods[${index}][cheque_no]">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Bank Name</label>
                        <input type="text" class="form-control" name="payment_methods[${index}][bank_name]">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Cheque Date</label>
                        <input type="date" class="form-control" name="payment_methods[${index}][cheque_date]">
                    </div>
                </div>
            </div>
        `;
    } else if (method === 'gift_card') {
        fieldsHtml = `
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Gift Card ID</label>
                        <select class="form-control" name="payment_methods[${index}][gift_card_id]">
                            <option value="">{{__('db.select_card')}}</option>
                            @foreach($lims_gift_card_list as $giftCard)
                                <option value="{{ $giftCard->id }}">{{ $giftCard->card_number }} - {{ number_format($giftCard->balance, 2) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        `;
    }
    
    fieldsContainer.insertAdjacentHTML('beforeend', fieldsHtml);
}

function calculateTotals() {
    let totalPaid = 0;
    const dueAmount = {{ $lims_field_order_data->due_amount }};
    const methodRows = document.getElementsByClassName('payment-method-row');
    
    for (let row of methodRows) {
        const amountInput = row.querySelector('input[name*="[amount]"]');
        if (amountInput && amountInput.value) {
            totalPaid += parseFloat(amountInput.value) || 0;
        }
    }
    
    document.getElementById('total_paid').textContent = totalPaid.toFixed(2);
    
    const remainingDue = dueAmount - totalPaid;
    document.getElementById('remaining_due').textContent = remainingDue.toFixed(2);
    
    const submitBtn = document.getElementById('submit_split_payment');
    if (Math.abs(totalPaid - dueAmount) < 0.01) {
        submitBtn.disabled = false;
    } else {
        submitBtn.disabled = true;
    }
}

// Add first payment method by default
addPaymentMethod();
</script>
@endsection