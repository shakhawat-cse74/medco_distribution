@extends('ecommerce::frontend.layout.main')

@section('title') {{$page->meta_title}} @endsection

@section('description') {{$page->meta_description}} @endsection

@section('content')
	<!--Breadcrumb Area start-->
    <div class="breadcrumb-section">
        <div class="container-fluid">
            <div class="row">
                <div class="col">
                    <h1 class="page-title">{{ $page->page_name }}</h1>
                    <ul>
                        <li><a href="{{ url('/') }}">{{__('db.Home')}}</a></li>
                        <li class="active">{{ $page->page_name }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!--Breadcrumb Area ends-->
    <!--Section starts-->
    <section class="">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h3>{{__('db.Get in touch')}}</h3>
                    <form class="row" id="contact-form" method="post" action="{{url('/send-email')}}">
                        @csrf
                        <div class="col-md-4 mb-4">
                            <input type="text" name="name" placeholder="{{__('db.name')}} *" class="form-control" required min="3">
                        </div>
                        <div class="col-md-4 mb-4">    
                            <input type="email" name="email" placeholder="{{__('db.Email')}} *" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-4">   
                            <input type="text" name="phone" placeholder="{{__('db.Phone')}}" class="form-control">
                        </div>
                        <div class="col-md-12 mb-3">
                            <textarea rows="10" class="form-control" name="message" placeholder="{{__('db.Message')}}"></textarea>
                        </div>
                        <div class="col-md-12">
                            <button type="submit" class="button style1 mt-3">{{__('db.Send')}}</button>
                        </div>
                    </form>
                </div>
                <div class="col-md-5 offset-md-1 contact-info">
                    <h3>{{__('db.Contact Info')}}</h3>
                    @if(isset($page->description))
                    <div class="mb-3"><p class="lead">{!!$page->description!!}</p></div>
                    @endif
                    @if(isset($ecommerce_setting->store_address))
                    <p class="lead mb-3">
                        <i class="material-symbols-outlined">
                        location_on
                        </i>
                        {{$ecommerce_setting->store_address}}
                    </p>
                    @endif
                    @if(isset($ecommerce_setting->store_phone))
                    <p class="lead mb-3">
                        <i class="material-symbols-outlined">
                        call
                        </i>
                        <a href="tel:{{$ecommerce_setting->store_phone}}">{{$ecommerce_setting->store_phone}}</a>
                    </p>
                    @endif
                    @if(isset($ecommerce_setting->store_email))
                    <p class="lead mb-3">
                        <i class="material-symbols-outlined">
                        mail
                        </i>
                        <a href="mailto:{{$ecommerce_setting->store_email}}">{{$ecommerce_setting->store_email}}</a>
                    </p>
                    @endif
                </div>

                @if(isset($ecommerce_setting->store_address))
                <div class="col-12 mt-5">
                    <iframe width='100%' height='350' frameborder='0' scrolling='no' marginheight='0' marginwidth='0' src='https://maps.google.com/maps?&q={{$ecommerce_setting->store_address}}&t=&z=13&ie=UTF8&iwloc=&output=embed'></iframe>
                </div>
                @endif
            </div>
        </div>
    </section>
    <!--Section ends-->
@endsection

@section('script')
	<script type="text/javascript">
		"use strict";

		$('#contact-form').on('submit', function(e){
			e.preventDefault();
            var data = $(this).serialize();
			var route = "{{ url('/send-email') }}";
            $('#contact-form button').html('sending...');
			$.ajax({
		        url: route,
		        type:"POST",
		        data: data,
		        success:function(response){
			        console.log(response);
		            if(response) {
		            	$('.alert').addClass('alert-custom show');
			            $('.alert-custom .message').html('{{trans("file.Thanks for your email. We shall get back to you shortly")}}.');
			            $('#contact-form button').html('Send');
                        $('#contact-form').reset();
                        setTimeout(function() {
                            $('.alert').removeClass('show');
                        }, 4000);
		            }
		        },
		    });
		})
	</script>
@endsection