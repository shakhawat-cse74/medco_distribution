@extends('ecommerce::frontend.layout.main')

@section('title') {{ $ecommerce_setting->site_title ?? '' }} @endsection

@section('description')  @endsection

@section('content')
	<!--Breadcrumb Area start-->
    <div class="breadcrumb-section">
        <div class="container">
            <div class="row">
                <div class="col">
                    <h1 class="page-title">Terms & Conditions</h1>
                    <ul>
                        <li><a href="{{ url('/') }}">Home</a></li>
                        <li class="active">Terms & Conditions</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!--Breadcrumb Area ends-->
    <!--Section starts-->
    <section class="">
        <div class="container">
            <p>Welcome to Lion-Coders.com. Before assessing the terms & conditions please note that terms & conditions might change anytime. Therefore, please check the terms & conditions every time you place an order.</p>
            <h4 class="mar-top-30">Important Notice:</h4>
            <p>In-case of unavailability, alternative products/available products will be delivered. We are unable to deliver many of your desired products in this crisis situation. We are extremely sorry for this inconvenience. All prices are approximate. Product will be delivered at current store/body price. For any changes, you will get a call from 01810061001 before delivery.</p>
            <h4 class="mar-top-30">Order Policy:</h4>
            <ul>
                <li>Order is possible for available items only.</li>
                <li>There is no minimum order value.</li>
                <li>Paid orders cannot be cancelled or refunded, for both Cash on Delivery (COD) & Online Payments.</li>
                <li>Orders will be processed after the confirmation from our Customer Care representative</li>
                <li>Please contact our Customer Care for any kind of order related issues.</li>
                <li>Customer Care contact number: 01810061001   (Service available Everyday, Time: 8:00AM-8:00PM)</li>
            </ul>
            <h4 class="mar-top-30">Order Limit:</h4>
            <ul>
                <li>A customer is eligible to place 5 live orders.</li>
                <li>A customer cannot place his 5th order if his 5 orders are still live.</li>
                <li>Live order refers to all those orders which are not delivered.</li>
                <li>Single customer can place 4 different orders at once.</li>
            </ul>
            <h4 class="mar-top-30">Pricing policy:</h4>
            <ul>
                <li>The Company aims to ensure that prices of all products offered for sale are true and correct. However, from time to time, the prices of certain products may not be current or may be inaccurate on account of technical issues, typographical errors. In each such case, notwithstanding anything to the contrary, the Company reserves the right to cancel the order without any further liability.</li>
                <li>Technical error in pricing information might take place due to system malfunction. In such a situation, LionCoders has the authority to cancel the order. We apologize for any inconvenience that may occur.</li>
            </ul>
            <h4 class="mar-top-30">Offer policy:</h4>
            <ul>
                <li>Use Coupon Codes sent to you or from the website during our offers to get a discount.</li>
                <li>If any item is found in the cart that is already on discount then Coupon Code will not work.</li>
            </ul>
            <h4 class="mar-top-30">Shipping Policy:</h4>
            <p>We are committed to delivering your order accurately, in good condition, and on time. Please note our shipping policy as follows:</p>
            <ul>
                <li>We provide home delivery only in the Chattogram city area.</li>
                <li>Deliver Period: within 1 business day.</li>
                <li>Delivery fee is 49 taka per shipment.</li>
                <li>Customer can avail FREE DELIVERY at his/her intended address for a purchase of or more than BDT 5000.00</li>
            </ul>
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