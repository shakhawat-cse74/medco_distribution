@extends('backend.layout.main') @section('content')
    <x-error-message key="not_permitted" />

    <section class="forms">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h4>{{ __('db.Update Return') }}</h4>
                        </div>
                        <div class="card-body">
                            <p class="italic">
                                <small>{{ __('db.The field labels marked with are required input fields') }}.</small>
                            </p>
                            <form action="{{ route('delivery-return.update', $lims_return_data->id) }}" method="post" enctype="multipart/form-data" id="payment-form">
                                @csrf
                                @method('PUT')
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{ __('db.reference') }}</label>
                                                <p><strong>{{ $lims_return_data->reference_no }}</strong></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ __('db.Sale Reference') }}</label>
                                                <p><strong>{{ $lims_return_data->sale->reference_no ?? 'N/A' }}</strong></p>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="warehouse_id" value="{{ $lims_return_data->warehouse_id ?? 1 }}">
                                    <input type="hidden" name="customer_id" value="{{ $lims_return_data->customer_id ?? 1 }}">
                                    <div class="row mt-5">
                                        <div class="col-md-12">
                                            <h5>{{ __('db.Order Table') }} *</h5>
                                            <div class="table-responsive mt-3">
                                                <table id="myTable" class="table table-hover order-list">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ __('db.name') }}</th>
                                                            <th>{{ __('db.Code') }}</th>
                                                            <th>{{ __('db.Batch No') }}</th>
                                                            <th>{{ __('db.Sale Quantity') }} <x-info title="Actual Sale Quantity"/></th>
                                                            <th>{{ __('db.Return Quantity') }} <x-info title="Current Return Quantity"/></th>
                                                            <th>{{ __('db.Net Unit Price') }} <x-info title="Product Price - Unit Discount = Unit Price"/></th>
                                                            <th>{{ __('db.Discount') }} <x-info title="Total unit discount / Total Qty = Unit Dicount"/></th>
                                                            <th>{{ __('db.Tax') }}</th>
                                                            <th>{{ __('db.Subtotal') }} <x-info title="Qty * Unit Price = SubTotal"/></th>
                                                            <th><i class="ti ti-trash"></i></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($lims_product_return_data as $key => $product_return)
                                                            <tr>
                                                                <?php
                                                                $product_data = DB::table('products')->find($product_return->product_id);
                                                                if (!$product_data) {
                                                                    continue;
                                                                }

                                                                $product_variant_id = null;
                                                                if ($product_return->variant_id) {
                                                                    $product_variant_data = \App\Models\ProductVariant::select('id', 'item_code')->FindExactProduct($product_data->id, $product_return->variant_id)->first();
                                                                    $product_variant_id = $product_variant_data->id ?? null;
                                                                    $product_data->code = $product_variant_data->item_code ?? $product_data->code;
                                                                }

                                                                $qty = $product_return->qty > 0 ? $product_return->qty : 1;
                                                                if ($product_data->tax_method == 1) {
                                                                    $product_price = $product_return->net_unit_price + ($qty > 0 ? $product_return->discount / $qty : 0);
                                                                } elseif ($product_data->tax_method == 2) {
                                                                    $product_price = ($qty > 0 ? $product_return->total / $qty : $product_return->net_unit_price) + ($qty > 0 ? $product_return->discount / $qty : 0);
                                                                } else {
                                                                    $product_price = $product_return->net_unit_price;
                                                                }

                                                                $tax = DB::table('taxes')->where('rate', $product_return->tax_rate)->first();
                                                                $product_batch_data = \App\Models\ProductBatch::select('batch_no')->find($product_return->product_batch_id);
                                                                ?>
                                                                <td>{{ $product_data->name ?? 'N/A' }}</td>
                                                                <td>{{ $product_data->code ?? 'N/A' }}</td>
                                                                @if ($product_batch_data)
                                                                    <td>{{ $product_batch_data->batch_no }}</td>
                                                                @else
                                                                    <td>N/A</td>
                                                                @endif
                                                                <td>{{ $product_return->qty }}</td>
                                                                <td><input type="number" class="form-control qty" name="qty[]" value="{{ $product_return->qty }}" step="any"></td>
                                                                <td class="net_unit_price">{{ number_format((float) $product_return->net_unit_price, config('decimal'), '.', '') }}</td>
                                                                <td class="discount">{{ number_format((float) $product_return->discount, config('decimal'), '.', '') }}</td>
                                                                <td class="tax">{{ number_format((float) $product_return->tax, config('decimal'), '.', '') }}</td>
                                                                <td class="sub-total">{{ number_format((float) $product_return->total, config('decimal'), '.', '') }}</td>
                                                                <td><button type="button" class="ibtnDel btn btn-danger btn-sm">×</button></td>
                                                                <input type="hidden" name="product_id[]" value="{{ $product_data->id }}">
                                                                <input type="hidden" name="product_return_id[]" value="{{ $product_return->id }}">
                                                                <input type="hidden" name="product_code[]" value="{{ $product_data->code ?? 'N/A' }}">
                                                                <input type="hidden" name="product_variant_id[]" value="{{ $product_variant_id }}">
                                                                <input type="hidden" name="product_batch_id[]" value="{{ $product_return->product_batch_id }}">
                                                                <input type="hidden" name="sale_unit[]" value="{{ $product_return->sale_unit_id }}">
                                                                <input type="hidden" name="net_unit_price[]" value="{{ $product_return->net_unit_price }}">
                                                                <input type="hidden" name="discount[]" value="{{ $product_return->discount }}">
                                                                <input type="hidden" name="tax_rate[]" value="{{ $product_return->tax_rate }}">
                                                                <input type="hidden" name="tax[]" value="{{ $product_return->tax }}">
                                                                <input type="hidden" name="subtotal[]" value="{{ $product_return->total }}">
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{ __('db.Order Tax') }}</label>
                                                <select class="form-control" name="order_tax_rate">
                                                    <option value="0">No Tax</option>
                                                    @foreach($lims_tax_list as $tax)
                                                        <option value="{{ $tax->rate }}" {{ $lims_return_data->order_tax_rate == $tax->rate ? 'selected' : '' }}>{{ $tax->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{ __('db.Attach Document') }}</label>
                                                <i class="ti ti-info-circle" data-toggle="tooltip" title="Only jpg, jpeg, png, gif, pdf, csv, docx, xlsx and txt file is supported"></i>
                                                <input type="file" name="document" class="form-control">
                                                @if($lims_return_data->document)
                                                    <a href="{{ url('documents/sale_return', $lims_return_data->document) }}" target="_blank">{{ $lims_return_data->document }}</a>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{ __('db.Return Discount') }}</label>
                                                <input type="number" name="total_discount" id="discount_value" class="form-control" value="{{ $lims_return_data->total_discount ?? 0 }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ __('db.Return Note') }}</label>
                                                <textarea rows="5" class="form-control" name="return_note">{{ $lims_return_data->return_note ?? '' }}</textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{ __('db.Staff Note') }}</label>
                                                <textarea rows="5" class="form-control" name="staff_note">{{ $lims_return_data->staff_note ?? '' }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <input type="hidden" name="total_qty" />
                                        <input type="hidden" name="total_tax" />
                                        <input type="hidden" name="total_price" />
                                        <input type="hidden" name="grand_total" />
                                        <input type="submit" value="{{ __('db.submit') }}" class="btn btn-primary" id="submit-button">
                                        <a href="{{ route('delivery-return.index') }}" class="btn btn-secondary">{{ __('db.Cancel') }}</a>
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
                    <span class="pull-right" id="order_discount">{{ number_format(0, config('decimal'), '.', '') }}</span>
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
        $("ul#delivery").siblings('a').attr('aria-expanded', 'true');
        $("ul#delivery").addClass("show");
        $("ul#delivery #delivery-sale-return-menu").addClass("active");

        $(".ibtnDel").on("click", function() {
            $(this).closest("tr").remove();
            calculateTotal();
        });

        function calculateTotal() {
            var total_qty = 0;
            var total_tax = 0;
            var total_price = 0;
            var item = 0;

            $(".qty").each(function() {
                var qty = parseFloat($(this).val()) || 0;
                var row = $(this).closest('tr');
                var tax = parseFloat(row.find('.tax').text()) || 0;
                var subtotal = parseFloat(row.find('.sub-total').text()) || 0;

                total_qty += qty;
                total_tax += tax;
                total_price += subtotal;
                item++;
            });

            var order_tax_rate = parseFloat($('select[name="order_tax_rate"]').val()) || 0;
            var order_discount = parseFloat($('#discount_value').val()) || 0;
            var order_tax = total_price * (order_tax_rate / 100);
            var grand_total = (total_price + order_tax) - order_discount;

            $('input[name="total_qty"]').val(total_qty);
            $('input[name="total_tax"]').val(order_tax.toFixed({{ config('decimal') }}));
            $('input[name="total_price"]').val(total_price.toFixed({{ config('decimal') }}));
            $('input[name="grand_total"]').val(grand_total.toFixed({{ config('decimal') }}));

            $('#item').text(item + '(' + total_qty + ')');
            $('#subtotal').text(total_price.toFixed({{ config('decimal') }}));
            $('#order_tax').text(order_tax.toFixed({{ config('decimal') }}));
            $('#order_discount').text(order_discount.toFixed({{ config('decimal') }}));
            $('#grand_total').text(grand_total.toFixed({{ config('decimal') }}));
        }

        $('select[name="order_tax_rate"], #discount_value').on("keyup change", function() {
            calculateTotal();
        });

        calculateTotal();
    </script>
@endpush
