@extends('ecommerce::frontend.layout.main')

@section('title') {{ $ecommerce_setting->site_title ?? '' }} @endsection

@section('description')  @endsection

@section('content')
	<!--Breadcrumb Area start-->
    <div class="breadcrumb-section">
        <div class="container">
            <div class="row">
                <div class="col">
                    <h1 class="page-title">Privacy policy</h1>
                    <ul>
                        <li><a href="{{ url('/') }}">Home</a></li>
                        <li class="active">Privacy policy</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!--Breadcrumb Area ends-->
    <!--Section starts-->
    <section class="">
        <div class="container">
            <p>When you use our Website, we collect and store your personal information which is provided by you from time to time. Our primary goal in doing so is to provide you a safe, efficient, smooth and customized experience. This allows us to provide services and features that most likely meet your needs, and to customize our website to make your experience safer and easier. More importantly, while doing so, we collect personal information from you that we consider necessary for achieving this purpose.</p>
            
            <p class="mar-top-30">Below are some of the ways in which we collect and store your information:</p>
            <ul class="mar-top-30">
                <li>We receive and store any information you enter on our website or give us in any other way. We use the information that you provide for such purposes as responding to your requests, customizing future shopping for you, improving our stores, and communicating with you.</li>
                <li>We also store certain types of information whenever you interact with us. For example, like many websites, we use "cookies," and we obtain certain types of information when your web browser accesses Lion-Coders.com or advertisements and other content served by or on behalf of Lion-Coders.com on other websites.</li>
                <li>To help us make e-mails more useful and interesting, we often receive a confirmation when you open e-mail from LionCoders if your computer supports such capabilities.</li>
            </ul>
            <p class="mar-top-30">Information about our customers is an important part of our business, and we are not in the business of selling it to others.</p>
            <p class="mar-top-30">We release accounts and other personal information when we believe release is appropriate to comply with the law; enforce or apply our Terms of Use and other agreements; or protect the rights, property, or safety of Lion-Coders.com, our users, or others. This includes exchanging information with other companies and organizations for fraud protection.</p>
        </div>
    </section>
    <!--Section ends-->
@endsection

@section('script')

@endsection