@extends('ecommerce::frontend.layout.main')

@section('title') {{ $ecommerce_setting->site_title ?? '' }} @endsection

@section('description')  @endsection

@section('content')
	<!--Breadcrumb Area start-->
    <div class="breadcrumb-section">
        <div class="container">
            <div class="row">
                <div class="col">
                    <h1 class="page-title">Return & Refund</h1>
                    <ul>
                        <li><a href="{{ url('/') }}">Home</a></li>
                        <li class="active">Return & Refund</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!--Breadcrumb Area ends-->
    <!--Section starts-->
    <section class="">
        <div class="container">
            <h4 class="mar-top-30">Return Policy</h4>
            <p>A customer may return any product during the time of delivery, or within 24 hours if:</p>
            <ul>
                <li>Product does not meet the customer's expectations.</li>
                <li>Found damaged during delivery.</li>
                <li>Have doubt about the product quality and quantity.</li>
                <li>Received in an unhealthy/ unexpected condition.</li>
                <li>For any return/exchange, customer needs to inform LionCoders Customer Care by calling:  01810061001 or email at: info@Lion-Coders.com</li>
            </ul>
            <p>A customer may return any unopened item within 24 hours of receiving the item. But following products may not be eligible for return or replacement:</p>
            <ul>
                <li>Damages due to misuse of product.</li>
                <li>Incidental damage due to malfunctioning of product.</li>
                <li>Any consumable item which has been used/installed.</li>
                <li>Products with tampered or missing serial/UPC numbers.</li>
                <li>Any damage/defect which are not covered under the manufacturer's warranty</li>
                <li>Any product that is returned without all original packaging and accessories, including the box, manufacturer's packaging if any, and all other items originally included with the product/s delivered.</li>
            </ul>
            <h4 class="mar-top-30">Refund Policy</h4>
            <p>LionCoders tries its best to serve the customers. But if under any circumstances, we fail to fulfill our commitment or to provide the service, we will notify the customer within 24 hours via phone/ text/ email. If the service that LionCoders fails to complete, requires any refund, it will be done maximum within 10 to 12 Days after our acknowledgement.
            Refund requests will be processed under mentioned situation:</p>
            <ul>
                <li>Unable to serve with any product.</li>
                <li>Customer returns any product from a paid order.</li>
            </ul>
            <h4 class="mar-top-30">Information Inconsistency Disclaimers:</h4>
            <p>The website might display inaccurate information regarding product price, availability, pictures, size & color at times due to technical malfunctions. LionCoders reserves the authority to correct & update those information from time to time.</p>

        </div>
    </section>
    <!--Section ends-->
@endsection

@section('script')

@endsection