<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">

<style>

@page{
    margin:0;
    size:{{$barcode_details->paper_width}}in {{$barcode_details->paper_height}}in;
}

*{
    box-sizing:border-box;
    margin:0;
    padding:0;
    font-family:Arial, Helvetica, sans-serif;
}

body{
    margin:0;
    padding:0;
}

.label{
    width:{{$barcode_details->width}}in;
    height:{{$barcode_details->height}}in;

    display:flex;
    align-items:center;
    justify-content:space-between;

    overflow:hidden;

    padding:1.5mm;

    page-break-after:avoid;
    break-after:avoid;

    margin-bottom:{{$barcode_details->row_distance}}in;
}

.left,
.right{
    width:44%;
    text-align:center;
    overflow:hidden;
}

.center{
    width:12%;
}

.business{
    font-size:6px;
    font-weight:bold;
    line-height:1;
    margin-bottom:1px;
}

.product{
    font-size:7px;
    font-weight:bold;
    line-height:1;
    max-height:16px;
    overflow:hidden;
    margin-bottom:1px;
}

.brand{
    font-size:6px;
    line-height:1;
    margin-bottom:1px;
}

.price{
    font-size:7px;
    font-weight:bold;
    line-height:1;
    margin-bottom:1px;
}

.barcode{
    width:90%;
    max-height:18px;
    margin:1px auto;
    display:block;
}

.sku{
    font-size:6px;
    line-height:1;
    word-break:break-all;
}

</style>

</head>

<body onload="window.print()">

@foreach($labels as $label)

<div class="label">

    {{-- LEFT WING --}}
    <div class="left">

        @if(!empty($print['business_name']))
            <div class="business">
                {{ $business_name }}
            </div>
        @endif

        @if(!empty($print['name']))
            <div class="product">
                {{ $label['product_actual_name'] }}
            </div>
        @endif

        @if(!empty($print['brand_name']))
            <div class="brand">
                {{ $label['brand_name'] }}
            </div>
        @endif

        @if(!empty($print['price']))
            <div class="price">

                @if(!empty($print['promo_price']) && $label['product_promo_price'] != 'null')

                    <span style="text-decoration:line-through;">
                        {{ format_currency($label['product_price']) }}
                    </span>

                    {{ format_currency($label['product_promo_price']) }}

                @else

                    {{ format_currency($label['product_price']) }}

                @endif

            </div>
        @endif

        <img
            class="barcode"
            src="data:image/png;base64,{{ DNS1D::getBarcodePNG($label['sub_sku'], $label['barcode_type'], 1.5, 20) }}"
        >

        <div class="sku">
            {{ $label['sub_sku'] }}
        </div>

    </div>

    {{-- CENTER (wrap around jewelry) --}}
    <div class="center"></div>

    {{-- RIGHT WING --}}
    <div class="right">

        @if(!empty($print['business_name']))
            <div class="business">
                {{ $business_name }}
            </div>
        @endif

        @if(!empty($print['name']))
            <div class="product">
                {{ $label['product_actual_name'] }}
            </div>
        @endif

        @if(!empty($print['brand_name']))
            <div class="brand">
                {{ $label['brand_name'] }}
            </div>
        @endif

        @if(!empty($print['price']))
            <div class="price">

                @if(!empty($print['promo_price']) && $label['product_promo_price'] != 'null')

                    <span style="text-decoration:line-through;">
                        {{ format_currency($label['product_price']) }}
                    </span>

                    {{ format_currency($label['product_promo_price']) }}

                @else

                    {{ format_currency($label['product_price']) }}

                @endif

            </div>
        @endif

        <img
            class="barcode"
            src="data:image/png;base64,{{ DNS1D::getBarcodePNG($label['sub_sku'], $label['barcode_type'], 1.5, 20) }}"
        >

        <div class="sku">
            {{ $label['sub_sku'] }}
        </div>

    </div>

</div>

@endforeach

</body>
</html>