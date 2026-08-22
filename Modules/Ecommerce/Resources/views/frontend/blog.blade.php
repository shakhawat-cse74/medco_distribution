@extends('ecommerce::frontend.layout.main')

@section('title')  @endsection

@section('description')  @endsection

@section('content')
	<!--Breadcrumb Area start-->
    <div class="breadcrumb-section">
        <div class="container">
            <div class="row">
                <div class="col">
                    <h1 class="page-title">{{ __('db.Blog') }}</h1>
                    <ul>
                        <li><a href="{{ url('/') }}">{{__('db.Home')}}</a></li>
                        <li class="active">{{ __('db.Blog') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!--Breadcrumb Area ends-->
    <!--Section starts-->
    <section class="pt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6 offset-md-3">
                    @foreach($blogs as $blog)
                    <a href="{{url('blog')}}/{{$blog->slug}}" class="mt-5">
                        @if(isset($blog->thumbnail))
                        <img class="post-thumb" src="" data-src="{{url('frontend/images/blog')}}/{{$blog->thumbnail}}" alt="{{$blog->title}}" />
                        @endif
                        <div style="background:#f0f0f5;padding:30px 20px;margin-bottom: 50px">
                            <h2 class="h1 text-center" style="line-height:1.2">{{$blog->title}}</h2>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    <!--Section ends-->
@endsection

@section('script')
<script type="text/javascript">
    "use strict";

    $(document).ready(function(){
        $('.post-thumb').each(function(){
            var img = $(this).data('src');
            $(this).attr('src', img);
        })
    })
</script>
@endsection