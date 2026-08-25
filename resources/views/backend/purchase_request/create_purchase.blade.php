@extends('backend.layout.main') 

@section('content')

<x-error-message key="not_permitted" />

<section class="forms">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h4>{{__('db.Add Purchase')}} ({{ $lims_request_data->reference_no }})</h4>
                    </div>
                    <div class="card-body">
                        <p class="italic"><small>{{__('db.The field labels marked with are required input fields')}}.</small></p>
                        <form action="{{ route('purchases.store') }}" method="POST" enctype="multipart/form-data" id="purchase-form">
                            @csrf
                            <input type="hidden" name="purchase_request_id" value="{{ $lims_request_data->id }}" />

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{__('db.Warehouse')}} *</label>
                                                <select required name="warehouse_id" class="selectpicker form-control" data-live-search="true" title="Select warehouse...">
                                                    @foreach($lims_warehouse_list as $warehouse)
                                                    <option value="{{$warehouse->id}}" @if($warehouse->id == $lims_request_data->warehouse_id) selected @endif>{{$warehouse->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{__('db.Supplier')}}</label>
                                                <select name="supplier_id" class="selectpicker form-control" data-live-search="true" id="supplier-id" title="Select supplier...">
                                                    @foreach($lims_supplier_list as $supplier)
                                                    <option value="{{$supplier->id}}" @if($supplier->id == $lims_request_data->supplier_id) selected @endif>{{$supplier->name .' ('. ($supplier->company_name ?: 'Individual') .')'}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{__('db.Purchase Status')}}</label>
                                                <select name="status" class="form-control">
                                                    <option value="1">{{__('db.Recieved')}}</option>
                                                    <option value="2">{{__('db.Partial')}}</option>
                                                    <option value="3">{{__('db.Pending')}}</option>
                                                    <option value="4">{{__('db.Ordered')}}</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{__('db.Attach Document')}}</label>
                                                <i class="ti ti-info-circle" data-toggle="tooltip" title="Only jpg, jpeg, png, gif, pdf, csv, docx, xlsx and txt file is supported"></i>
                                                <input type="file" name="document" class="form-control" />
                                                @if($errors->has('extension'))
                                                    <span>
                                                       <strong class="text-danger">{{ $errors->first('extension') }}</strong>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-5">
                                        <div class="col-md-12">
                                            <h5>{{__('db.Order Table')}} *</h5>
                                            <div class="table-responsive mt-3">
                                                <table id="myTable" class="table table-hover order-list">
                                                    <thead>
                                                        <tr>
                                                            <th style="width: 4%; text-align: center;">
                                                                <input type="checkbox" id="check-all" checked style="transform: scale(1.3); cursor: pointer;" title="Select/Deselect All" />
                                                            </th>
                                                            <th>{{__('db.name')}}</th>
                                                            <th>{{__('db.Code')}}</th>
                                                            <th>{{__('db.Batch No')}}</th>
                                                            <th>{{__('db.Expired Date')}}</th>
                                                            <th>{{__('db.Quantity')}}</th>
                                                            <th>{{__('db.Net Unit Cost')}}</th>
                                                            <th>{{__('db.Discount')}}</th>
                                                            <th>{{__('db.Tax')}}</th>
                                                            <th>{{__('db.Subtotal')}}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="receive-tbody">
                                                        @foreach($lims_product_request_data as $key => $item)
                                                            @php
                                                                $product = \App\Models\Product::find($item->product_id);
                                                                $unit = $item->purchase_unit_id ? \App\Models\Unit::find($item->purchase_unit_id) : ($product && $product->unit_id ? \App\Models\Unit::find($product->unit_id) : null);
                                                                $unitName = $unit ? $unit->unit_name : 'piece';
                                                            @endphp
                                                            <tr class="item-row" data-id="{{ $item->product_id }}">
                                                                <td class="text-center align-middle">
                                                                    <input type="checkbox" class="row-checkbox" checked style="transform: scale(1.3); cursor: pointer;" />
                                                                </td>
                                                                <td>
                                                                    <strong>{{ $product ? $product->name : 'Product' }}</strong>
                                                                    <input type="hidden" class="product-id" name="product_id[]" value="{{ $item->product_id }}"/>
                                                                    <input type="hidden" class="product-code" name="product_code[]" value="{{ $product ? $product->code : '' }}"/>
                                                                    <input type="hidden" class="purchase-unit" name="purchase_unit[]" value="{{ $unitName }}"/>
                                                                    <input type="hidden" class="discount-value" name="discount[]" value="0" />
                                                                    <input type="hidden" class="tax-rate" name="tax_rate[]" value="0"/>
                                                                    <input type="hidden" class="tax-value" name="tax[]" value="0" />
                                                                    <input type="hidden" class="subtotal-value" name="subtotal[]" value="{{ $item->total }}" />
                                                                    <input type="hidden" class="imei-number" name="imei_number[]" value="" />
                                                                    <input type="hidden" class="net_unit_margin" name="net_unit_margin[]" value="0" />
                                                                    <input type="hidden" class="net_unit_margin_type" name="net_unit_margin_type[]" value="percentage" />
                                                                    <input type="hidden" class="net_unit_price" name="net_unit_price[]" value="{{ $product ? $product->price : 0 }}" />
                                                                </td>
                                                                <td>{{ $product ? $product->code : '' }}</td>
                                                                <td><input type="text" class="form-control batch-no" name="batch_no[]" placeholder="Batch No" /></td>
                                                                <td><input type="date" class="form-control expired-date" name="expired_date[]" /></td>
                                                                <td>
                                                                    <input type="number" class="form-control qty field-qty" name="qty[]" value="{{ $item->qty }}" step="any" min="0" required />
                                                                    <input type="hidden" name="recieved[]" value="{{ $item->qty }}" class="field-recieved" />
                                                                </td>
                                                                <td>
                                                                    <input type="number" class="form-control field-cost" name="net_unit_cost[]" value="{{ number_format($item->net_unit_cost, 2, '.', '') }}" step="any" min="0" required />
                                                                    <input type="hidden" name="unit_cost[]" value="{{ $item->net_unit_cost }}" class="field-unit-cost" />
                                                                </td>
                                                                <td class="discount">0.00</td>
                                                                <td class="tax">0.00</td>
                                                                <td class="sub-total">{{ number_format($item->total, 2, '.', '') }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot class="tfoot active">
                                                        <th></th>
                                                        <th colspan="2">{{__('db.Total')}}</th>
                                                        <th></th>
                                                        <th></th>
                                                        <th id="total-qty">0.00</th>
                                                        <th></th>
                                                        <th id="total-discount">0.00</th>
                                                        <th id="total-tax">0.00</th>
                                                        <th id="total">0.00</th>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-2"><input type="hidden" name="total_qty" id="hidden_total_qty" /></div>
                                        <div class="col-md-2"><input type="hidden" name="total_discount" value="0" /></div>
                                        <div class="col-md-2"><input type="hidden" name="total_tax" value="0" /></div>
                                        <div class="col-md-2"><input type="hidden" name="total_cost" id="hidden_total_cost" /></div>
                                        <div class="col-md-2">
                                            <input type="hidden" name="item" id="hidden_item" />
                                            <input type="hidden" name="order_tax" id="hidden_order_tax" value="0" />
                                        </div>
                                        <div class="col-md-2"><input type="hidden" name="grand_total" id="hidden_grand_total" /></div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{__('db.Order Tax')}}</label>
                                                <select class="form-control" name="order_tax_rate" id="order-tax-rate">
                                                    <option value="0">{{__('db.No Tax')}}</option>
                                                    @foreach($lims_tax_list as $tax)
                                                    <option value="{{$tax->rate}}">{{$tax->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><strong>{{__('db.Discount')}}</strong></label>
                                                <input type="number" name="order_discount" id="order-discount" class="form-control" value="0" step="any" />
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><strong>{{__('db.Shipping Cost')}}</strong></label>
                                                <input type="number" name="shipping_cost" id="shipping-cost" class="form-control" value="0" step="any" />
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Payment Status & Account -->
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{__('db.Payment Status')}} *</label>
                                                <select name="payment_status" id="payment_status" class="form-control">
                                                    <option value="1">{{__('db.Due')}}</option>
                                                    <option value="3">{{__('db.Partial')}}</option>
                                                    <option value="4">{{__('db.Paid')}}</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div id="account-list" class="col-md-4" style="display: none;">
                                            <div class="form-group">
                                                <label>{{__('db.Account')}}</label>
                                                <select name="account_id" id="account_id" class="form-control">
                                                    @foreach($lims_account_list as $account)
                                                        <option value="{{$account->id}}" @if($account->is_default) selected @endif>{{$account->name}} [{{$account->account_no}}]</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Payment Box matching screenshot -->
                                    <div id="payment" style="display: none;">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>{{__('db.Paid By')}}</label>
                                                    <select name="paid_by_id[]" id="paid_by_id" class="form-control">
                                                        <option value="1">{{ __('db.Cash') }}</option>
                                                        <option value="3">{{ __('db.Credit Card') }}</option>
                                                        <option value="4">{{ __('db.Cheque') }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>{{__('db.Recieved Amount')}} *</label>
                                                    <input type="number" name="paying_amount[]" class="form-control" id="paying-amount" step="any" />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>{{__('db.Paying Amount')}} *</label>
                                                    <input type="number" name="amount[]" class="form-control" id="paid-amount" step="0.01"/>
                                                    <input type="hidden" name="paid_amount" id="hidden-paid-amount" value="0" />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>{{__('db.Payment Receiver')}}</label>
                                                    <input type="text" name="payment_receiver" class="form-control" id="payment-receiver"/>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>{{__('db.Change')}}</label>
                                                    <p id="change" class="ml-2 font-weight-bold">{{ number_format(0, gen_setting()->decimal, '.', '') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row" id="cheque-row" style="display: none;">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>{{__('db.Cheque Number')}} *</label>
                                                    <input type="text" name="cheque_no" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label>{{__('db.Payment Note')}}</label>
                                                <textarea rows="3" class="form-control" name="payment_note"></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-2">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>{{__('db.Note')}}</label>
                                                <textarea rows="5" class="form-control" name="note">Converted from Purchase Request {{ $lims_request_data->reference_no }}</textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mt-3">
                                        <input type="submit" value="{{__('db.submit')}}" class="btn btn-primary" id="submit-button">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Quotation-style Total Summary Strip -->
    <div class="container-fluid">
        <table class="table table-bordered table-condensed totals">
            <td><strong>{{__('db.Items')}}</strong>
                <span class="pull-right" id="item">{{number_format(0, gen_setting()->decimal, '.', '')}}</span>
            </td>
            <td><strong>{{__('db.Total')}}</strong>
                <span class="pull-right" id="subtotal">{{number_format(0, gen_setting()->decimal, '.', '')}}</span>
            </td>
            <td><strong>{{__('db.Order Tax')}}</strong>
                <span class="pull-right" id="order_tax">{{number_format(0, gen_setting()->decimal, '.', '')}}</span>
            </td>
            <td><strong>{{__('db.Order Discount')}}</strong>
                <span class="pull-right" id="order_discount">{{number_format(0, gen_setting()->decimal, '.', '')}}</span>
            </td>
            <td><strong>{{__('db.Shipping Cost')}}</strong>
                <span class="pull-right" id="shipping_cost">{{number_format(0, gen_setting()->decimal, '.', '')}}</span>
            </td>
            <td><strong>{{__('db.grand total')}}</strong>
                <span class="pull-right" id="grand_total">{{number_format(0, gen_setting()->decimal, '.', '')}}</span>
            </td>
        </table>
    </div>
</section>

@endsection

@push('scripts')
<script type="text/javascript">
    $(document).ready(function() {
        
        // Select / Deselect All Checkboxes
        $('#check-all').on('change', function() {
            let isChecked = $(this).is(':checked');
            $('.row-checkbox').prop('checked', isChecked);
            toggleRowDisabled();
            calculateSummary();
        });

        // Individual Checkbox change
        $(document).on('change', '.row-checkbox', function() {
            toggleRowDisabled();
            calculateSummary();
        });

        // Toggle row inputs enabled/disabled state based on checkbox
        function toggleRowDisabled() {
            $('.item-row').each(function() {
                let isChecked = $(this).find('.row-checkbox').is(':checked');
                if (isChecked) {
                    $(this).removeClass('text-muted');
                    $(this).find('input:not(.row-checkbox)').prop('disabled', false);
                } else {
                    $(this).addClass('text-muted');
                    $(this).find('input:not(.row-checkbox)').prop('disabled', true);
                }
            });
        }

        // Live recalculate on received qty or cost edit
        $(document).on('input', '.field-qty, .field-cost, #order-tax-rate, #order-discount, #shipping-cost', function() {
            let row = $(this).closest('tr');
            if (row.length > 0) {
                let qty = parseFloat(row.find('.field-qty').val()) || 0;
                let unitCost = parseFloat(row.find('.field-cost').val()) || 0;
                let subtotal = (qty * unitCost).toFixed(2);
                row.find('.sub-total').text(subtotal);
                row.find('.subtotal-value').val(subtotal);
                row.find('.field-unit-cost').val(unitCost);
                row.find('.field-recieved').val(qty);
            }
            calculateSummary();
        });

        function calculateSummary() {
            let totalItems = 0;
            let totalQty = 0;
            let totalCost = 0;

            $('.item-row').each(function() {
                let isChecked = $(this).find('.row-checkbox').is(':checked');
                if (isChecked) {
                    totalItems++;
                    let qty = parseFloat($(this).find('.field-qty').val()) || 0;
                    let cost = parseFloat($(this).find('.field-cost').val()) || 0;
                    let sub = qty * cost;
                    totalQty += qty;
                    totalCost += sub;
                }
            });

            let orderTaxRate = parseFloat($('#order-tax-rate').val()) || 0;
            let orderDiscount = parseFloat($('#order-discount').val()) || 0;
            let shippingCost = parseFloat($('#shipping-cost').val()) || 0;

            let orderTax = (totalCost * orderTaxRate) / 100;
            let grandTotal = totalCost + orderTax + shippingCost - orderDiscount;

            // Table footer
            $('#total-qty').text(totalQty.toFixed(2));
            $('#total').text(totalCost.toFixed(2));

            // Bottom Quotation strip
            $('#item').text(totalItems.toFixed(2));
            $('#subtotal').text(totalCost.toFixed(2));
            $('#order_tax').text(orderTax.toFixed(2));
            $('#order_discount').text(orderDiscount.toFixed(2));
            $('#shipping_cost').text(shippingCost.toFixed(2));
            $('#grand_total').text(grandTotal.toFixed(2));

            // Hidden inputs
            $('#hidden_item').val(totalItems);
            $('#hidden_total_qty').val(totalQty);
            $('#hidden_total_cost').val(totalCost);
            $('#hidden_order_tax').val(orderTax);
            $('#hidden_grand_total').val(grandTotal);

            handlePaymentStatusChange();
        }

        // Payment status change handling
        $('#payment_status').on('change', function() {
            handlePaymentStatusChange();
        });

        function handlePaymentStatusChange() {
            let status = $('#payment_status').val();
            let grandTotal = parseFloat($('#hidden_grand_total').val()) || 0;

            if (status === '3' || status === '4') { // Partial or Paid
                $('#payment').show();
                $('#account-list').show();
                $('#paying-amount').prop('required', true);
                $('#paid-amount').prop('required', true);

                if (status === '4') { // Paid
                    $('#paid-amount').prop('readonly', true).val(grandTotal.toFixed(2));
                    $('#paying-amount').val(grandTotal.toFixed(2));
                    $('#hidden-paid-amount').val(grandTotal.toFixed(2));
                    $('#change').text('0.00');
                } else { // Partial
                    $('#paid-amount').prop('readonly', false);
                    $('#paying-amount').val('');
                    $('#paid-amount').val('');
                    $('#hidden-paid-amount').val('0');
                    $('#change').text('0.00');
                }
            } else { // Due (1)
                $('#payment').hide();
                $('#account-list').hide();
                $('#paying-amount').prop('required', false).val('');
                $('#paid-amount').prop('required', false).val('');
                $('#hidden-paid-amount').val('0');
            }
        }

        // Paid By dropdown handling (Cheque, Card, Cash)
        $('#paid_by_id').on('change', function() {
            let val = $(this).val();
            if (val === '4') {
                $('#cheque-row').show();
            } else {
                $('#cheque-row').hide();
            }
        });

        // Live calculation of Change when typing payment
        $('#paying-amount, #paid-amount').on('input', function() {
            let paying = parseFloat($('#paying-amount').val()) || 0;
            let paid = parseFloat($('#paid-amount').val()) || 0;
            $('#hidden-paid-amount').val(paid.toFixed(2));
            let change = (paying - paid);
            $('#change').text(change >= 0 ? change.toFixed(2) : '0.00');
        });

        // Initial calculation
        toggleRowDisabled();
        calculateSummary();

        // Validate on submit
        $('#purchase-form').on('submit', function(e) {
            let checkedCount = $('.row-checkbox:checked').length;
            if (checkedCount === 0) {
                e.preventDefault();
                alert("Please select at least one product item to convert to purchase!");
                return false;
            }
            $("#submit-button").prop('disabled', true);
        });
    });
</script>
@endpush
