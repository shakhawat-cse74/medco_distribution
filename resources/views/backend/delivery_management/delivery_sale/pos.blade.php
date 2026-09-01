@extends('backend.layout.top-head')

@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.9/css/intlTelInput.css" />
    <style type="text/css">
        :root{--theme-color:{{ $theme_color ?? '#7c5cc4' }}
        nav.navbar{background:#FFF;border-radius:10px;box-shadow:rgba(37,83,185,0.1) 0px 2px 6px 0px;height:50px;margin-bottom:20px;padding:5px 20px;z-index:999}
        body{background:#fafbfd;color:#1a1a1a;overflow-x:hidden}
        .mb-0{margin-bottom:0!important}
        table{font-size:.85em}
        .badge{background-color:unset;font-weight:600;font-size:11px}
        h2{color:#555}
        .card{border:none;margin-bottom:30px;box-shadow:rgba(37,83,185,0.1) 0px 2px 6px 0px;border-radius:10px}
        .card-body{padding:1.5rem}
        .btn-primary,.btn-primary:disabled{background-color:var(--theme-color);border-color:var(--theme-color)}
        .form-control:focus{border:1px solid var(--theme-color);box-shadow:none}
        .table thead th{border-bottom:1px solid #e4e6fc;font-weight:600}
        .table td{border-bottom:1px solid #ebe9f1;align-items:center}
        .dropdown-menu{z-index:1000;min-width:12rem;padding:1rem .5rem;margin:.125rem 0 0;font-size:13px}
        .pos .bootstrap-select.form-control:not([class*=col-]){width:100px}
        .minus,.plus{padding:.35rem .75rem}
        .numkey.qty{font-size:13px;padding:0 0;max-width:50px;text-align:center}
        .sub-total{font-weight:500}
        .pos-page .container-fluid{padding:0 15px}
        section.pos-section{padding:5px 0}
        .pos-text{line-height:1.8}
        .pos-page .order-list .btn{padding:2px 5px}
        .payment-amount{background-color:#d6deff;text-align:center}
        .payment-amount h2{color:var(--theme-color);margin-bottom:0}
        .totals .totals-title{color:#7d7d7d;display:inline-block;width:100px}
        .filter-window{width:100%;height:100vh;background-color:#fff;overflow-y:auto;padding:0 10px;position:absolute;top:0;right:0;z-index:999999;display:none}
    </style>
@endpush

@section('content')
<?php
    $lims_pos_setting_data = $lims_pos_setting_data ?? null;
    if ($lims_pos_setting_data) {
        $options = explode(',', $lims_pos_setting_data->payment_options);
    } else {
        $options = [];
    }
?>
<section class="pos-page">
    <nav class="navbar" style="margin-bottom:0;">
        <div class="container-fluid">
            <div class="nav-menu">
                <div class="row w-100">
                    <div class="col-4">
                        <a href="{{ route('delivery-sale.index') }}" class="btn btn-secondary">
                            <i class="ti ti-arrow-left"></i> <span>Back</span>
                        </a>
                    </div>
                    <div class="col-8 text-right">
                        <span class="btn btn-primary" id="currentDateTime"></span>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid pos-section">
        <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-12">
                                <h5 class="mb-0">{{ __('db.Delivery Sale POS') }}</h5>
                            </div>
                        </div>
                        <form id="pos-form" action="{{ route('delivery-sale.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{__('db.Warehouse')}} *</label>
                                        <select name="warehouse_id" id="warehouse_id" class="form-control selectpicker" data-live-search="true" required>
                                            @foreach($lims_warehouse_list as $warehouse)
                                                <option value="{{$warehouse->id}}">{{$warehouse->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{__('db.customer')}} *</label>
                                        <select name="customer_id" id="customer_id" class="form-control selectpicker" data-live-search="true" required>
                                            @foreach($lims_customer_list as $customer)
                                                <option value="{{$customer->id}}">{{$customer->name}} - {{$customer->phone_number}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{__('db.Route')}} *</label>
                                        <select name="route_id" id="route_id" class="form-control selectpicker" data-live-search="true" required>
                                            <option value="">Select Route</option>
                                            @foreach($lims_route_list as $route)
                                                <option value="{{$route->id}}">{{$route->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{__('db.Delivery Man')}} *</label>
                                        <select name="delivery_man_id" id="delivery_man_id" class="form-control selectpicker" data-live-search="true" required>
                                            <option value="">Select Delivery Man</option>
                                            @foreach($lims_delivery_man_list as $dm)
                                                <option value="{{$dm->id}}">{{$dm->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>{{__('db.Biller')}}</label>
                                        <select name="biller_id" class="form-control selectpicker" data-live-search="true">
                                            @foreach($lims_biller_list as $biller)
                                                <option value="{{$biller->id}}">{{$biller->name}} - {{$biller->company_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>{{__('db.Search Products')}}</label>
                                        <input type="text" id="search-product" class="form-control" placeholder="Search by name or code...">
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <h6>{{__('db.Order Items')}}</h6>
                                    <table class="table table-bordered" id="pos-order-table">
                                        <thead>
                                            <tr>
                                                <th>{{__('db.Product')}}</th>
                                                <th>{{__('db.Qty')}}</th>
                                                <th>{{__('db.Unit Price')}}</th>
                                                <th>{{__('db.Total')}}</th>
                                                <th><i class="ti ti-trash"></i></th>
                                            </tr>
                                        </thead>
                                        <tbody id="pos-order-list">
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{__('db.Order Tax')}} (%)</label>
                                        <input type="number" name="order_tax_rate" id="order_tax_rate" class="form-control" value="0" step="0.01">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{__('db.Discount')}}</label>
                                        <input type="number" name="order_discount_value" id="order_discount_value" class="form-control" value="0" step="0.01">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{__('db.Shipping Cost')}}</label>
                                        <input type="number" name="shipping_cost" id="shipping_cost" class="form-control" value="0" step="0.01">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{__('db.Payment Status')}}</label>
                                        <select name="payment_status" id="payment_status" class="form-control">
                                            <option value="4">{{__('db.Paid')}}</option>
                                            <option value="2">{{__('db.Due')}}</option>
                                            <option value="3">{{__('db.Partial')}}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row totals mt-3">
                                <div class="col-12">
                                    <table class="table">
                                        <tr>
                                            <td class="totals-title">{{__('db.Sub Total')}}</td>
                                            <td class="sub-total"><span id="sub_total">0.00</span></td>
                                        </tr>
                                        <tr>
                                            <td class="totals-title">{{__('db.Order Tax')}}</td>
                                            <td><span id="order_tax">0.00</span></td>
                                        </tr>
                                        <tr>
                                            <td class="totals-title">{{__('db.Discount')}}</td>
                                            <td><span id="order_discount">0.00</span></td>
                                        </tr>
                                        <tr>
                                            <td class="totals-title">{{__('db.Shipping Cost')}}</td>
                                            <td><span id="shipping_cost_display">0.00</span></td>
                                        </tr>
                                        <tr>
                                            <td class="totals-title"><strong>{{__('db.Grand Total')}}</strong></td>
                                            <td><strong><span id="grand_total">0.00</span></strong></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <label>{{__('db.Paid Amount')}}</label>
                                    <input type="number" name="paid_amount" id="paid_amount" class="form-control" value="0" step="0.01">
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{__('db.Payment Method')}}</label>
                                        <select name="payment_method" class="form-control">
                                            @if(in_array("cash",$options))
                                                <option value="Cash">Cash</option>
                                            @endif
                                            @if(in_array("card",$options))
                                                <option value="Credit Card">Credit Card</option>
                                            @endif
                                            @if(in_array("cheque",$options))
                                                <option value="Cheque">Cheque</option>
                                            @endif
                                            @if(in_array("gift_card",$options))
                                                <option value="Gift Card">Gift Card</option>
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{__('db.Sale Note')}}</label>
                                        <textarea name="sale_note" class="form-control" rows="2"></textarea>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="pos" value="1">
                            <input type="hidden" name="sale_status" id="sale_status" value="1">
                            <input type="hidden" name="product_id" id="product_ids">
                            <input type="hidden" name="qty" id="product_qtys">
                            <input type="hidden" name="unit_price" id="unit_prices">
                            <input type="hidden" name="total" id="totals">

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <button type="button" id="clear-cart" class="btn btn-secondary btn-block">{{__('db.Clear')}}</button>
                                </div>
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-primary btn-block">{{__('db.Complete Sale')}}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{__('db.Products')}}</h5>
                        <div class="row mt-2">
                            <div class="col-12">
                                <input type="text" id="product_search" class="form-control" placeholder="Search products...">
                            </div>
                        </div>
                    </div>
                    <div class="card-body" style="max-height: 70vh; overflow-y: auto;">
                        <div class="row" id="product-list">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script type="text/javascript">
    let cart = [];
    let products = [];

    $(document).ready(function() {
        updateDateTime();
        setInterval(updateDateTime, 1000);

        $('#route_id').on('change', function() {
            var routeId = $(this).val();
            if (routeId) {
                $.ajax({
                    url: '{{ route("delivery-sale.getDeliveryMenByRoute", "") }}/' + routeId,
                    type: 'GET',
                    success: function(data) {
                        $('#delivery_man_id').empty();
                        $('#delivery_man_id').append('<option value="">Select Delivery Man</option>');
                        $.each(data, function(key, value) {
                            $('#delivery_man_id').append('<option value="'+value.id+'">'+value.name+'</option>');
                        });
                        $('#delivery_man_id').selectpicker('refresh');
                    }
                });
            }
        });

        loadProducts();

        $('#product_search').on('keyup', function() {
            var query = $(this).val().toLowerCase();
            filterProducts(query);
        });

        $('#search-product').on('keyup', function() {
            var query = $(this).val().toLowerCase();
            filterProducts(query);
        });

        $('#order_tax_rate, #order_discount_value, #shipping_cost, #paid_amount').on('input', function() {
            calculateTotals();
        });

        $('#pos-form').on('submit', function(e) {
            e.preventDefault();

            if (cart.length === 0) {
                alert('Please add at least one product to the cart');
                return;
            }

            var productIds = [];
            var qtys = [];
            var unitPrices = [];
            var tot = [];

            cart.forEach(function(item) {
                productIds.push(item.id);
                qtys.push(item.qty);
                unitPrices.push(item.price);
                tot.push(item.qty * item.price);
            });

            $('#product_ids').val(productIds.join(','));
            $('#product_qtys').val(qtys.join(','));
            $('#unit_prices').val(unitPrices.join(','));
            $('#totals').val(tot.join(','));

            this.submit();
        });

        $('#clear-cart').on('click', function() {
            cart = [];
            renderCart();
            calculateTotals();
        });
    });

    function updateDateTime() {
        var now = new Date();
        var dateTimeStr = now.toLocaleString('en-US', {
            weekday: 'short', year: 'numeric', month: 'short', day: 'numeric',
            hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true
        });
        $('#currentDateTime').text(dateTimeStr);
    }

    function loadProducts() {
        var warehouseId = $('#warehouse_id').val();
        $.ajax({
            url: '{{ url("sales/getproduct") }}/' + warehouseId,
            type: 'GET',
            success: function(data) {
                products = data;
                renderProducts(products);
            }
        });
    }

    function renderProducts(productData) {
        var html = '';
        productData.forEach(function(product) {
            html += '<div class="col-lg-3 col-md-4 col-sm-6 mb-3">';
            html += '<div class="card product-card" style="cursor:pointer;" onclick="addToCart('+product.id+')">';
            html += '<div class="card-body p-2 text-center">';
            html += '<h6 class="mb-1">'+product.name+'</h6>';
            html += '<p class="mb-1 text-muted">'+product.code+'</p>';
            html += '<p class="mb-0"><strong>'+product.price+'</strong></p>';
            html += '</div></div></div>';
        });
        $('#product-list').html(html);
    }

    function filterProducts(query) {
        if (!query) {
            renderProducts(products);
            return;
        }
        var filtered = products.filter(function(product) {
            return product.name.toLowerCase().indexOf(query) !== -1 ||
                   product.code.toLowerCase().indexOf(query) !== -1;
        });
        renderProducts(filtered);
    }

    function addToCart(productId) {
        var product = products.find(p => p.id == productId);
        if (!product) return;

        var existingItem = cart.find(item => item.id == productId);
        if (existingItem) {
            existingItem.qty += 1;
        } else {
            cart.push({
                id: product.id,
                name: product.name,
                code: product.code,
                qty: 1,
                price: parseFloat(product.price)
            });
        }
        renderCart();
        calculateTotals();
    }

    function removeFromCart(index) {
        cart.splice(index, 1);
        renderCart();
        calculateTotals();
    }

    function updateQty(index, change) {
        var newQty = cart[index].qty + change;
        if (newQty <= 0) {
            removeFromCart(index);
        } else {
            cart[index].qty = newQty;
            renderCart();
            calculateTotals();
        }
    }

    function renderCart() {
        var html = '';
        cart.forEach(function(item, index) {
            html += '<tr>';
            html += '<td>'+item.name+'<br><small class="text-muted">'+item.code+'</small></td>';
            html += '<td><button class="btn btn-sm btn-secondary" onclick="updateQty('+index+', -1)">-</button> '+item.qty+' <button class="btn btn-sm btn-secondary" onclick="updateQty('+index+', 1)">+</button></td>';
            html += '<td>'+item.price.toFixed(2)+'</td>';
            html += '<td>'+(item.qty * item.price).toFixed(2)+'</td>';
            html += '<td><button class="btn btn-sm btn-danger" onclick="removeFromCart('+index+')"><i class="ti ti-trash"></i></button></td>';
            html += '</tr>';
        });
        if (cart.length === 0) {
            html = '<tr><td colspan="5" class="text-center text-muted">No items in cart</td></tr>';
        }
        $('#pos-order-list').html(html);
    }

    function calculateTotals() {
        var subtotal = 0;
        cart.forEach(function(item) {
            subtotal += item.qty * item.price;
        });

        var taxRate = parseFloat($('#order_tax_rate').val()) || 0;
        var discount = parseFloat($('#order_discount_value').val()) || 0;
        var shipping = parseFloat($('#shipping_cost').val()) || 0;

        var taxAmount = subtotal * (taxRate / 100);
        var grandTotal = subtotal + taxAmount - discount + shipping;

        $('#sub_total').text(subtotal.toFixed(2));
        $('#order_tax').text(taxAmount.toFixed(2));
        $('#order_discount').text(discount.toFixed(2));
        $('#shipping_cost_display').text(shipping.toFixed(2));
        $('#grand_total').text(grandTotal.toFixed(2));
    }

    $('#warehouse_id').on('change', function() {
        loadProducts();
    });

    $("ul#delivery").siblings('a').attr('aria-expanded','true');
    $("ul#delivery").addClass("show");
    $("ul#delivery #delivery-sale-menu").addClass("active");
</script>
@endpush
