@extends('backend.layout.main') @section('content')
    <x-error-message key="not_permitted" />

    <section class="forms">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h4>{{ __('db.Add Delivery Sale Return') }} - {{ $lims_sale_data->reference_no ?? '' }}</h4>
                        </div>
                        <div class="card-body">
                            <p class="italic">
                                <small>{{ __('db.The field labels marked with are required input fields') }}.</small></p>
                            <form class="sale-return-form" action="{{ route('delivery-return.store') }}" method="post" enctype="multipart/form-data">
                                @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="hidden" name="sale_id" value="{{ $lims_sale_data->id }}">
                                            <input type="hidden" name="warehouse_id" value="{{ $lims_sale_data->warehouse_id }}">
                                            <h5>{{ __('db.Order Table') }} *</h5>
                                            <div class="table-responsive mt-3">
                                                <table id="myTable" class="table table-hover order-list">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ __('db.name') }}</th>
                                                            <th>{{ __('db.Code') }}</th>
                                                            <th>{{ __('db.Batch No') }}</th>
                                                            <th>{{ __('db.Quantity') }}</th>
                                                            <th>{{ __('db.Net Unit Price') }}</th>
                                                            <th>{{ __('db.Discount') }}</th>
                                                            <th>{{ __('db.Tax') }}</th>
                                                            <th>{{ __('db.Subtotal') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($lims_product_sale_data as $key => $product_sale)
                                                            <?php
                                                            $product_data = DB::table('products')->find($product_sale->product_id);
                                                            if (!$product_data) {
                                                                continue;
                                                            }
                                                            $product_variant_id = null;
                                                            if ($product_sale->variant_id) {
                                                                $product_variant_data = \App\Models\ProductVariant::select('id', 'item_code')->FindExactProduct($product_data->id, $product_sale->variant_id)->first();
                                                                if ($product_variant_data) {
                                                                    $product_variant_id = $product_variant_data->id;
                                                                    $product_data->code = $product_variant_data->item_code;
                                                                }
                                                            }
                                                            if ($product_data->tax_method == 1) {
                                                                $product_price = $product_sale->net_unit_price + $product_sale->discount / $product_sale->qty;
                                                            } elseif ($product_data->tax_method == 2) {
                                                                $product_price = $product_sale->total / $product_sale->qty + $product_sale->discount / $product_sale->qty;
                                                            }
                                                            $tax = DB::table('taxes')->where('rate', $product_sale->tax_rate)->first();
                                                            if ($product_data->type == 'standard') {
                                                                $unit = DB::table('units')->select('unit_name')->find($product_sale->sale_unit_id);
                                                                $unit_name = $unit->unit_name ?? 'N/A';
                                                            } else {
                                                                $unit_name = 'n/a';
                                                            }
                                                            $product_batch_data = \App\Models\ProductBatch::select('batch_no')->find($product_sale->product_batch_id);
                                                            ?>
                                                            <tr>
                                                                <td>{{ $product_data->name ?? 'N/A' }}
                                                                    <input type="hidden" class="product-id" name="product_id[]" value="{{ $product_data->id }}">
                                                                    <input type="hidden" class="product-code" name="product_code[]" value="{{ $product_data->code ?? 'N/A' }}">
                                                                    <input type="hidden" name="product_sale_id[]" value="{{ $product_sale->id }}">
                                                                    <input type="hidden" name="product_variant_id[]" value="{{ $product_variant_id }}">
                                                                    <input type="hidden" class="product-price" name="product_price[]" value="{{ $product_price ?? 0 }}">
                                                                    <input type="hidden" class="unit-price" value="{{ $product_sale->total / $product_sale->qty }}">
                                                                    <input type="hidden" class="sale-unit" name="sale_unit[]" value="{{ $unit_name }}">
                                                                    <input type="hidden" class="net_unit_price" name="net_unit_price[]" value="{{ $product_sale->net_unit_price }}">
                                                                    <input type="hidden" class="discount-value" name="discount[]" value="{{ $product_sale->discount }}">
                                                                    <input type="hidden" class="tax-rate" name="tax_rate[]" value="{{ $product_sale->tax_rate }}">
                                                                    <input type="hidden" class="tax-name" value="{{ $tax->name ?? 'No Tax' }}">
                                                                    <input type="hidden" class="tax-method" value="{{ $product_data->tax_method ?? 1 }}">
                                                                    <input type="hidden" class="unit-tax-value" value="{{ $product_sale->tax / $product_sale->qty }}">
                                                                    <input type="hidden" class="tax-value" name="tax_value[]" value="{{ $product_sale->tax }}">
                                                                    <input type="hidden" class="subtotal-value" name="subtotal_value[]" value="{{ $product_sale->total }}">
                                                                </td>
                                                                <td>{{ $product_data->code ?? 'N/A' }}</td>
                                                                @if ($product_batch_data)
                                                                    <td>
                                                                        <input type="hidden" class="product-batch-id" name="product_batch_id[]" value="{{ $product_sale->product_batch_id }}">
                                                                        {{ $product_batch_data->batch_no }}
                                                                    </td>
                                                                @else
                                                                    <td>
                                                                        <input type="hidden" class="product-batch-id" name="product_batch_id[]">
                                                                        N/A
                                                                    </td>
                                                                @endif
                                                                <td>
                                                                    <input type="hidden" name="actual_qty[]" class="actual-qty" value="{{ $product_sale->qty - $product_sale->return_qty }}">
                                                                    <input type="number" class="form-control qty" name="qty[]" value="{{ $product_sale->qty - $product_sale->return_qty }}" required step="any" max="{{ $product_sale->qty - $product_sale->return_qty }}">
                                                                </td>
                                                                <td class="net_unit_price">{{ number_format((float) $product_sale->net_unit_price, config('decimal'), '.', '') }}</td>
                                                                <td class="discount" data-unit_discount="{{ $product_sale->discount / $product_sale->qty }}">{{ number_format((float) $product_sale->discount, config('decimal'), '.', '') }}</td>
                                                                <td class="tax">{{ number_format((float) $product_sale->tax, config('decimal'), '.', '') }}</td>
                                                                <td class="sub-total">{{ number_format((float) $product_sale->total, config('decimal'), '.', '') }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{ __('db.Order Tax') }}</label>
                                                <select class="form-control" name="order_tax_rate">
                                                    <option value="0">No Tax</option>
                                                    @foreach($lims_tax_list as $tax)
                                                        <option value="{{ $tax->rate }}">{{ $tax->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{ __('db.Attach Document') }}</label>
                                                <i class="ti ti-info-circle" data-toggle="tooltip" title="Only jpg, jpeg, png, gif, pdf, csv, docx, xlsx and txt file is supported"></i>
                                                <input type="file" name="document" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{ __('db.Return Discount') }}</label>
                                                <input type="number" name="total_sale_discount" id="discount_value" class="form-control" value="{{ $lims_sale_data->order_discount ?? 0 }}">
                                            </div>
                                        </div>
                                    </div>
                                    @if($lims_sale_data->paid_amount > 0)
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <div class="form-check d-inline-block ml-1 mt-4">
                                                    <input class="form-check-input" type="checkbox" name="refund" id="refund" checked>
                                                    <label style="color:rgb(136, 136, 136);" class="form-check-label" for="refund">
                                                        {{ __('db.issue_refund') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{__('db.Account')}}</label>
                                                <select class="form-control" name="account_id">
                                                    @foreach($lims_account_list as $account)
                                                        <option value="{{$account->id}}" {{ ($account->id == $lims_sale_data->account_id) ? 'selected' : ''}}>{{$account->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{__('db.Paid By')}}</label>
                                                <select class="form-control" name="payment_method">
                                                    <option value="Cash">Cash</option>
                                                    <option value="Bank">Bank</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{ __('db.refund_amount') }}</label>
                                                <input type="number" name="refund_amount" id="refund_amount" class="form-control" value="{{ $lims_sale_data->paid_amount ?? 0 }}" max="{{ $lims_sale_data->paid_amount ?? 0 }}" step="0.01">
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ __('db.Return Note') }}</label>
                                                <textarea rows="5" class="form-control" name="return_note"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ __('db.Staff Note') }}</label>
                                                <textarea rows="5" class="form-control" name="staff_note"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <input type="hidden" name="total_qty" />
                                        <input type="hidden" name="total_discount" />
                                        <input type="hidden" name="total_tax" />
                                        <input type="hidden" name="total_price" />
                                        <input type="hidden" name="item" />
                                        <input type="hidden" name="order_tax" />
                                        <input type="hidden" name="grand_total" />
                                        <input type="hidden" name="change_sale_status" value="0">
                                        <input type="submit" value="{{ __('db.submit') }}" class="btn btn-primary" id="submit-button">
                                        <a href="{{ route('delivery-sale.index') }}" class="btn btn-secondary">{{ __('db.Cancel') }}</a>
                                    </div>
                                </div>
                            </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <table class="table table-bordered table-condensed totals">
                <td><strong>{{ __('db.Items') }}</strong>
                    <span class="pull-right" id="item">{{ number_format(0, config('decimal'), '.', '') }}</span>
                </td>
                <td><strong>{{ __('db.Total') }}</strong>
                    <span class="pull-right" id="subtotal">{{ number_format(0, config('decimal'), '.', '') }}</span>
                </td>
                <td><strong>{{ __('db.Order Tax') }}</strong>
                    <span class="pull-right" id="order_tax">{{ number_format(0, config('decimal'), '.', '') }}</span>
                </td>
                <td><strong>{{ __('db.Return Discount') }}</strong>
                    <span class="pull-right" id="order_discount" data-total_discount="{{ $lims_sale_data->order_discount ?? 0 }}">{{ number_format($lims_sale_data->order_discount ?? 0, config('decimal'), '.', '') }}</span>
                </td>
                <td><strong>{{ __('db.grand total') }}</strong>
                    <span class="pull-right" id="grand_total">{{ number_format(0, config('decimal'), '.', '') }}</span>
                </td>
            </table>
        </div>
    </section>
@endsection
@push('scripts')
    <script type="text/javascript">
        var rowindex;
        var customer_group_rate;
        var row_product_price;
        var changeSaleStatus;

        $('.selectpicker').selectpicker({
            style: 'btn-link',
        });
        $('[data-toggle="tooltip"]').tooltip();

        $("#myTable").on('input', '.qty', function() {
            rowindex = $(this).closest('tr').index();
            calculateTotal();
        });

        $('select[name="order_tax_rate"], #discount_value').on("keyup change", function() {
            calculateGrandTotal();
        });

        function calculateTotal() {
            var total_qty = 0;
            var total_discount = 0;
            var total_tax = 0;
            var total = 0;
            var item = 0;
            changeSaleStatus = 1;

            $(".qty").each(function(i) {
                var actual_qty = parseFloat($('table.order-list tbody tr:nth-child(' + (i + 1) + ') .actual-qty').val());
                var qty = parseFloat($(this).val());

                if (qty != actual_qty) {
                    changeSaleStatus = 0;
                }
                if (qty > actual_qty) {
                    alert('Quantity can not be bigger than the actual quantity!');
                    qty = actual_qty;
                    $(this).val(actual_qty);
                }

                var discount = qty * $('table.order-list tbody tr:nth-child(' + (i + 1) + ') .discount').data('unit_discount');
                $('table.order-list tbody tr:nth-child(' + (i + 1) + ') .discount').text(discount.toFixed({{ config('decimal') }}));

                var tax = $('table.order-list tbody tr:nth-child(' + (i + 1) + ') .unit-tax-value').val() * qty;
                var tax_method = $('table.order-list tbody tr:nth-child(' + (i + 1) + ') .tax-method').val();
                var unit_price = $('table.order-list tbody tr:nth-child(' + (i + 1) + ') .unit-price').val();

                total_qty += parseFloat(qty);
                total_discount += parseFloat(discount);
                total_tax += parseFloat(tax);
                total += parseFloat(unit_price * qty);

                if(tax_method == 1) {
                    var row_sub_total = (unit_price * qty) + tax;
                }
                else if(tax_method == 2) {
                    var row_sub_total = (unit_price * qty);
                }

                $('table.order-list tbody tr:nth-child(' + (i + 1) + ') .discount-value').val(total_discount);
                $('table.order-list tbody tr:nth-child(' + (i + 1) + ') .subtotal-value').val(unit_price * qty);
                $('table.order-list tbody tr:nth-child(' + (i + 1) + ') .sub-total').text(parseFloat(row_sub_total).toFixed({{ config('decimal') }}));
                $('table.order-list tbody tr:nth-child(' + (i + 1) + ') .tax-value').val(parseFloat(tax).toFixed({{ config('decimal') }}));
                $('table.order-list tbody tr:nth-child(' + (i + 1) + ') .tax').text(parseFloat(tax).toFixed({{ config('decimal') }}));
                item++;
            });

            if (changeSaleStatus)
                $('input[name="change_sale_status"]').val(changeSaleStatus);

            $('input[name="total_qty"]').val(total_qty);
            $('input[name="total_tax"]').val(total_tax.toFixed({{ config('decimal') }}));
            $('input[name="total_price"]').val(total.toFixed({{ config('decimal') }}));
            $('input[name="item"]').val(item);

            item += '(' + total_qty + ')';
            $('#item').text(item);

            calculateGrandTotal();
        }

        function calculateGrandTotal() {
            var subtotal = parseFloat($('input[name="total_price"]').val());
            var order_tax_rate = parseFloat($('select[name="order_tax_rate"]').val());
            var order_discount = parseFloat($('#discount_value').val()) || 0;

            var order_tax = subtotal * (order_tax_rate / 100);
            var sale_discount = $('input[name="total_sale_discount"]').val() || 0;
            var grand_total = (subtotal + order_tax) - sale_discount;

            $('#subtotal').text(subtotal.toFixed({{ config('decimal') }}));
            $('#order_tax').text(order_tax.toFixed({{ config('decimal') }}));
            $('input[name="order_tax"]').val(order_tax.toFixed({{ config('decimal') }}));
            $('#grand_total').text(grand_total.toFixed({{ config('decimal') }}));
            $('input[name="grand_total"]').val(grand_total.toFixed({{ config('decimal') }}));
        }

        $(window).keydown(function(e) {
            if (e.which == 13) {
                var $targ = $(e.target);
                if (!$targ.is("textarea") && !$targ.is(":button,:submit")) {
                    var focusNext = false;
                    $(this).find(":input:visible:not([disabled],[readonly]), a").each(function() {
                        if (this === e.target) {
                            focusNext = true;
                        } else if (focusNext) {
                            $(this).focus();
                            return false;
                        }
                    });
                    return false;
                }
            }
        });

        $('.sale-return-form').on('submit', function(e) {
            var rownumber = $('table.order-list tbody tr:last').index();
            if (rownumber < 0) {
                alert("Please insert product to order table!");
                e.preventDefault();
            }
        });

        calculateTotal();
    </script>
@endpush
