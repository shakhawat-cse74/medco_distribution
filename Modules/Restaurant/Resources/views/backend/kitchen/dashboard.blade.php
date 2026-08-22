@extends('backend.layout.main')
@push('css')
<style>
    nav.navbar a.menu-btn {
        opacity: 0;
    }
    .side-navbar {
        width: 0;
        opacity: 0;
    }
    .page {
        margin-left: 0;
        width: 100%;
    }
    .order-card {
        background-color: #f4f4f4;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 20px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
    .order-card h5 {
        margin-bottom: 10px;
    }
    .order-card .btn {
        width: 100%;
        margin-top: 10px;
    }
</style>
@endpush
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
            @endif

            @if(!empty($kitchen_list) && count($kitchen_list) > 0)
            <!-- Navigation Tabs -->
            <ul class="nav nav-tabs" id="myTab" role="tablist" style="border-bottom:none">
                @foreach($kitchen_list as $key => $kitchen)
                <li class="nav-item" role="presentation">
                    <button class="btn btn-primary @if($key == 0) active @endif mr-2" 
                            id="{{ $kitchen->name }}-tab" 
                            data-toggle="tab" 
                            data-target="#{{ $kitchen->name }}" 
                            type="button" 
                            role="tab" 
                            aria-controls="{{ $kitchen->name }}" 
                            aria-selected="{{ $key == 0 ? 'true' : 'false' }}">
                        {{ $kitchen->name }}
                    </button>
                </li>
                @endforeach
            </ul>
            @endif

            <!-- Tab Content -->
            <div class="tab-content" id="myTabContent">
                @if(!empty($kitchen_list) && count($kitchen_list) > 0)
                    @foreach($kitchen_list as $key => $kitchen)
                    <div class="tab-pane fade @if($key == 0) show active @endif" 
                        id="{{ $kitchen->name }}" 
                        role="tabpanel" 
                        aria-labelledby="{{ $kitchen->name }}-tab">
                        <div class="row">
                            <!-- Sales Data Cards -->
                            @foreach($salesData->where('kitchen_id', $kitchen->id) as $sale)

                            @php
                                $sale_status = __('db.Processing');

                                $currency = DB::table('currencies')->find($sale->currency_id);

                                if (!empty($sale->coupon_id)) {
                                    $coupon = DB::table('coupons')->find($sale->coupon_id);
                                    $coupon_code = $coupon->code;
                                } else {
                                    $coupon_code = '';
                                }

                                $sale_date = \Carbon\Carbon::parse($sale->sale_date)->format($dateFormat);

                                // Construct the sale array
                                $sale_array = [
                                    $sale_date,
                                    $sale->reference_no,
                                    $sale_status,
                                    $sale->biller_name,
                                    $sale->biller_company_name,
                                    $sale->biller_email,
                                    $sale->biller_phone_number,
                                    $sale->biller_address,
                                    $sale->biller_city,
                                    $sale->customer_name,
                                    $sale->customer_phone_number,
                                    $sale->customer_address,
                                    $sale->customer_city,
                                    $sale->sale_id,
                                    $sale->total_tax,
                                    $sale->total_discount,
                                    $sale->total_price,
                                    $sale->order_tax,
                                    $sale->order_tax_rate,
                                    $sale->order_discount,
                                    $sale->shipping_cost,
                                    $sale->grand_total,
                                    $sale->paid_amount,
                                    preg_replace('/[\n\r]/', "<br>", $sale->sale_note),
                                    preg_replace('/[\n\r]/', "<br>", $sale->staff_note),
                                    $sale->user_name,
                                    $sale->user_email,
                                    $sale->warehouse,
                                    $coupon_code,
                                    $sale->coupon_discount,
                                    $sale->document,
                                    $currency->code,
                                    $sale->exchange_rate,
                                ];

                                // Convert the sale array to a JSON string
                                $sale_json = json_encode($sale_array);
                            @endphp
                            <div class="col-md-3 mt-4">
                                <div class="order-card">
                                    <h5>Order #{{ $sale->sale_id }}</h5>
                                    <p><strong>Placed at:</strong> {{ $sale_date }} {{ \Carbon\Carbon::parse($sale->sale_date)->format('H:i') }}</p>
                                    <div class="alert alert-secondary p-2 mb-2">
                                        <p class="mb-1" style="font-size: 1.1em;"><strong>{{ (int)$sale->total_quantity }}x {{ $sale->product_name }}</strong></p>
                                        @if($sale->modifier_labels)
                                        <p class="mb-0 text-muted" style="font-size: 0.9em;"><i class="ti ti-check-box"></i> {{ $sale->modifier_labels }}</p>
                                        @endif
                                    </div>
                                    <p><strong>Order Status:</strong> <span class="badge badge-primary">{{ __('db.Processing') }}</span></p>
                                    <p><strong>Customer:</strong> {{ $sale->customer_name }}</p>
                                    @if(!empty($sale->table_name))<p><strong>Table:</strong> {{ $sale->table_name  }}</p>@endif
                                    <button class="btn btn-warning" id="sale-staus" data-id="{{ $sale->sale_id }}"  data-status="cooked">{{ __('db.Mark as cooked') }}</button>
                                    <button class="btn btn-info view-sale"  data-toggle="modal" data-target="#get-sale-details" value="{{$sale->sale_id}}">{{ __('db.Order details') }}</button>
                                </div>
                            </div>
                            @endforeach

                        </div>
                        @if ($salesData->where('kitchen_id', $kitchen->id)->isEmpty())
                        <div style="height:70vh" class="d-flex justify-content-center align-items-center">
                            <div class="text-center">
                                <i class="ti ti-cutlery mb-3" style="font-size: 90px;"></i>
                                <p>{{ __('db.No orders found for this kitchen') }}</p>
                            </div>
                        </div>
                        @endif

                    </div>
                    @endforeach
                @else
                    <div class="row">
                        <!-- Sales Data Cards -->
                        @foreach($salesData as $sale)

                        @php
                            $sale_status = __('db.Cooked');

                            $currency = DB::table('currencies')->find($sale->currency_id);

                            if (!empty($sale->coupon_id)) {
                                $coupon = DB::table('coupons')->find($sale->coupon_id);
                                $coupon_code = $coupon->code;
                            } else {
                                $coupon_code = '';
                            }

                            $sale_date = \Carbon\Carbon::parse($sale->sale_date)->format($dateFormat);

                            // Construct the sale array
                            $sale_array = [
                                $sale_date,
                                $sale->reference_no,
                                $sale_status,
                                $sale->biller_name,
                                $sale->biller_company_name,
                                $sale->biller_email,
                                $sale->biller_phone_number,
                                $sale->biller_address,
                                $sale->biller_city,
                                $sale->customer_name,
                                $sale->customer_phone_number,
                                $sale->customer_address,
                                $sale->customer_city,
                                $sale->sale_id,
                                $sale->total_tax,
                                $sale->total_discount,
                                $sale->total_price,
                                $sale->order_tax,
                                $sale->order_tax_rate,
                                $sale->order_discount,
                                $sale->shipping_cost,
                                $sale->grand_total,
                                $sale->paid_amount,
                                preg_replace('/[\n\r]/', "<br>", $sale->sale_note),
                                preg_replace('/[\n\r]/', "<br>", $sale->staff_note),
                                $sale->user_name,
                                $sale->user_email,
                                $sale->warehouse,
                                $coupon_code,
                                $sale->coupon_discount,
                                $sale->document,
                                $currency->code,
                                $sale->exchange_rate,
                            ];

                            // Convert the sale array to a JSON string
                            $sale_json = json_encode($sale_array);
                        @endphp
                        <div class="col-md-3 mt-4">
                            <div class="order-card">
                                <h5>Order #{{ $sale->sale_id }}</h5>
                                <p><strong>Placed at:</strong> {{ $sale_date }} {{ \Carbon\Carbon::parse($sale->sale_date)->format('H:i') }}</p>
                                <div class="alert alert-secondary p-2 mb-2">
                                    <p class="mb-1" style="font-size: 1.1em;"><strong>{{ (int)$sale->total_quantity }}x {{ $sale->product_name }}</strong></p>
                                    @if($sale->modifier_labels)
                                    <p class="mb-0 text-muted" style="font-size: 0.9em;"><i class="ti ti-check-box"></i> {{ $sale->modifier_labels }}</p>
                                    @endif
                                </div>
                                <p><strong>Order Status:</strong> <span class="badge badge-primary">{{ __('db.Cooked') }}</span></p>
                                <p><strong>Customer:</strong> {{ $sale->customer_name }}</p>
                                @if(!empty($sale->table_name))<p><strong>Table:</strong> {{ $sale->table_name  }}</p>@endif
                                <button class="btn btn-warning" id="sale-staus" data-id="{{ $sale->sale_id }}" data-status="served">{{ __('db.Mark as served') }}</button>
                                <button class="btn btn-info view-sale"  data-toggle="modal" data-target="#get-sale-details" value="{{$sale->sale_id}}">{{ __('db.Order details') }}</button>
                            </div>
                        </div>
                        @endforeach

                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Sale detaisl -->
<div id="get-sale-details" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog">
        <div class="modal-content">
            <div class="container mt-3 pb-2 border-bottom">
                <div class="row">
                    <div class="col-md-6 d-print-none">
                        <button id="print-btn" type="button" class="btn btn-default btn-sm"><i class="ti ti-printer"></i> {{__('db.Print')}}</button>

                        <form action="{{ route('sale.sendmail') }}" method="post" class="sendmail-form">
                            @csrf
                            <input type="hidden" name="sale_id">
                            <button class="btn btn-default btn-sm d-print-none"><i class="ti ti-mail"></i> {{__('db.Email')}}</button>
                        </form>
                    </div>
                    <div class="col-md-6 d-print-none">
                        <button type="button" id="close-btn" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="ti ti-cross"></i></span></button>
                    </div>
                    <div class="col-md-4 text-left">
                        <img src="{{url('logo', $general_setting->site_logo)}}" width="90px;">
                    </div>
                    <div class="col-md-4 text-center">
                        <h3 id="exampleModalLabel" class="modal-title container-fluid">{{$general_setting->site_title}}</h3>
                    </div>
                    <div class="col-md-4 text-right">
                        <i style="font-size: 15px;">{{__('db.Sale Details')}}</i>
                    </div>
                </div>
            </div>
            <div id="sale-content" class="modal-body">
            </div>
            <br>
            <table class="table table-bordered product-sale-list">
                <thead>
                    <th>#</th>
                    <th>{{__('db.product')}}</th>
                    <th>{{__('db.Batch No')}}</th>
                    <th>{{__('db.Qty')}}</th>
                    <th>{{__('db.Returned')}}</th>
                    <th>{{__('db.Unit Price')}}</th>
                    <th>{{__('db.Tax')}}</th>
                    <th>{{__('db.Discount')}}</th>
                    <th>{{__('db.Subtotal')}}</th>
                    <th>{{__('db.Delivered')}}</th>
                </thead>
                <tbody>
                </tbody>
            </table>
            <div id="sale-footer" class="modal-body"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">

    $(document).on('click', '#sale-staus', function() {
        var sale_id = $(this).data('id');
        var status = $(this).data('status');
        if(status == 'cooked'){
            var url = '{{ route("restaurant.sale.status.cooked", ":id") }}';
        }else if(status == 'served'){
            var url = '{{ route("restaurant.sale.status.served", ":id") }}';
        }

        url = url.replace(':id', sale_id);

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                if (response.status == 'success') {
                    location.reload();
                }
            },
            error: function(xhr, status, error) {
                alert('Error: ' + error);
            }
        });
    });

    $(document).on('click', '.view-sale', function() {
        sale_id = $(this).val();

        $.ajax({
            url: '{{url("sales/get-sale/")}}/' + sale_id,
            type: 'GET',
            success: function(sale) {
                saleDetails(sale);
            },
            error: function(xhr, status, error) {
                alert('Error: ' + error);
            }
        });
    });

    function saleDetails(sale){

        var htmltext = '<strong>{{__("db.date")}}: </strong>'+sale[0]+
            '<br><strong>{{__("db.reference")}}: </strong>'+sale[1]+
            '<br><strong>{{__("db.Warehouse")}}: </strong>'+sale[27]+
            '<br><strong>{{__("db.Sale Status")}}: </strong>'+sale[2]+
            '<br><strong>{{__("db.Currency")}}: </strong>'+sale[31];

        if(sale[32])
            htmltext += '<br><strong>{{__("db.Exchange Rate")}}: </strong>'+sale[32]+'<br>';
        else
            htmltext += '<br><strong>{{__("db.Exchange Rate")}}: </strong>N/A<br>';
        if(sale[33])
            htmltext += '<strong>{{__("db.Table")}}: </strong>'+sale[33]+'<br>';
        if(sale[30])
            htmltext += '<strong>{{__("db.Attach Document")}}: </strong><a href="documents/sale/'+sale[30]+'">Download</a><br>';

        htmltext += '<br><div class="row"><div class="col-md-6"><strong>{{__("db.From")}}:</strong><br>'+sale[3]+'<br>'+sale[4]+'<br>'+sale[5]+'<br>'+sale[6]+'<br>'+sale[7]+'<br>'+sale[8]+
        '</div><div class="col-md-6"><div class="float-right"><strong>{{__("db.To")}}:</strong><br>'+sale[9]+'<br>'+sale[10]+'<br>'+sale[11]+'<br>'+sale[12]+'</div></div></div>';

        $.get('{{url("sales/product_sale/")}}/' + sale[13], function(data){
            $(".product-sale-list tbody").remove();
            var name_code = data[0];
            var qty = data[1];
            var unit_code = data[2];
            var tax = data[3];
            var tax_rate = data[4];
            var discount = data[5];
            var subtotal = data[6];
            var batch_no = data[7];
            var return_qty = data[8];
            var is_delivered = data[9];
            // Check if data[10] exists
            var toppings = data[10] ? data[10] : []; 
            var total_qty = 0;
            var newBody = $("<tbody>");

            $.each(name_code, function(index){
                var newRow = $("<tr>");
                var cols = '';
                cols += '<td><strong>' + (index+1) + '</strong></td>';
                cols += '<td>' + name_code[index];

                // Append topping names if toppings[index] exists
                if (toppings[index]) {
                    try {
                        // Parse and extract topping names
                        var toppingData = JSON.parse(toppings[index]);
                        var toppingNames = toppingData.map(topping => topping.name).join(', ');
                        cols += ' (' + toppingNames + ')';
                    } catch (error) {
                        console.error('Error parsing toppings for index', index, toppings[index], error);
                    }
                }

                cols += '</td>';
                cols += '<td>' + batch_no[index] + '</td>';
                cols += '<td>' + qty[index] + ' ' + unit_code[index] + '</td>';
                cols += '<td>' + return_qty[index] + '</td>';
                // Calculate unit price
                var unitPrice = parseFloat(subtotal[index] / qty[index]).toFixed({{$general_setting->decimal}});

                // Calculate topping prices if toppings[index] exists
                var toppingPrices = '';
                if (toppings[index]) {
                    try {
                        var toppingData = JSON.parse(toppings[index]); // Parse topping data
                        toppingPrices = toppingData
                            .map(topping => parseFloat(topping.price).toFixed({{$general_setting->decimal}})) // Extract and format each topping price
                            .join(' + '); // Join prices with '+'
                    } catch (error) {
                        console.error('Error calculating topping prices for index', index, toppings[index], error);
                    }
                }

                cols += '<td>' + unitPrice + ' (' + toppingPrices + ')</td>';
                cols += '<td>' + tax[index] + '(' + tax_rate[index] + '%)' + '</td>';
                cols += '<td>' + discount[index] + '</td>';
                // Update subtotal to include topping prices
                var toppingPricesRowTotal = 0;
                if (toppings[index]) {
                    try {
                        var toppingData = JSON.parse(toppings[index]);
                        toppingPricesRowTotal = toppingData.reduce((sum, topping) => sum + parseFloat(topping.price), 0);
                    } catch (error) {
                        console.error('Error calculating topping prices for index', index, toppings[index], error);
                    }
                }
                subtotal[index] = parseFloat(subtotal[index]) + toppingPricesRowTotal;
                cols += '<td>' + subtotal[index].toFixed({{$general_setting->decimal}}) + '</td>';
                cols += '<td>' + is_delivered[index] + '</td>';
                total_qty += parseFloat(qty[index]);
                newRow.append(cols);
                newBody.append(newRow);
            });

            var newRow = $("<tr>");
            cols = '';
            cols += '<td colspan=3><strong>{{__("db.Total")}}:</strong></td>';
            cols += '<td>' + total_qty + '</td>';
            cols += '<td colspan=2></td>';
            cols += '<td>' + sale[14] + '</td>';
            cols += '<td>' + sale[15] + '</td>';
            cols += '<td>' + sale[16] + '</td>';
            cols += '<td></td>';
            newRow.append(cols);
            newBody.append(newRow);

            var newRow = $("<tr>");
            cols = '';
            cols += '<td colspan=9><strong>{{__("db.Order Tax")}}:</strong></td>';
            cols += '<td>' + sale[17] + '(' + sale[18] + '%)' + '</td>';
            newRow.append(cols);
            newBody.append(newRow);

            var newRow = $("<tr>");
            cols = '';
            cols += '<td colspan=9><strong>{{__("db.Order Discount")}}:</strong></td>';
            cols += '<td>' + sale[19] + '</td>';
            newRow.append(cols);
            newBody.append(newRow);
            if(sale[28]) {
                var newRow = $("<tr>");
                cols = '';
                cols += '<td colspan=9><strong>{{__("db.Coupon Discount")}} ['+sale[28]+']:</strong></td>';
                cols += '<td>' + sale[29] + '</td>';
                newRow.append(cols);
                newBody.append(newRow);
            }

            var newRow = $("<tr>");
            cols = '';
            cols += '<td colspan=9><strong>{{__("db.Shipping Cost")}}:</strong></td>';
            cols += '<td>' + sale[20] + '</td>';
            newRow.append(cols);
            newBody.append(newRow);

            var newRow = $("<tr>");
            cols = '';
            cols += '<td colspan=9><strong>{{__("db.grand total")}}:</strong></td>';
            cols += '<td>' + sale[21] + '</td>';
            newRow.append(cols);
            newBody.append(newRow);

            var newRow = $("<tr>");
            cols = '';
            cols += '<td colspan=9><strong>{{__("db.Paid Amount")}}:</strong></td>';
            cols += '<td>' + sale[22] + '</td>';
            newRow.append(cols);
            newBody.append(newRow);

            var newRow = $("<tr>");
            cols = '';
            cols += '<td colspan=9><strong>{{__("db.Due")}}:</strong></td>';
            cols += '<td>' + parseFloat(sale[21] - sale[22]).toFixed({{$general_setting->decimal}}) + '</td>';
            newRow.append(cols);
            newBody.append(newRow);

            $("table.product-sale-list").append(newBody);
        });
        var htmlfooter = '<p><strong>{{__("db.Sale Note")}}:</strong> '+sale[23]+'</p><p><strong>{{__("db.Staff Note")}}:</strong> '+sale[24]+'</p><strong>{{__("db.Created By")}}:</strong><br>'+sale[25]+'<br>'+sale[26];
        $('#sale-content').html(htmltext);
        $('#sale-footer').html(htmlfooter);
        $('#sale-details').modal('show');
    }
</script>
@endpush
