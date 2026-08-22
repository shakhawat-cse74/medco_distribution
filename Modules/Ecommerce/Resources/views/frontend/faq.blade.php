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
        <div class="container-fluid">
            @if(isset($page->description))
            <div class="row">
                <div class="col-md-12">
                    <div class="mar-bot-30">
                        {!! $page->description !!}
                    </div>
                </div>
            </div>
            @endif

            @if(isset($categories))
            <div class="row">
                <div class="col-md-3 offset-md-1">
                    <div class="nav nav-pills faq-categories">
                    @foreach($categories as $index=>$cat)
                        <h3 class=" @if($index == 0) active @endif" id="v-pills-{{$cat->id}}-tab" data-toggle="pill" data-target="#v-pills-{{$cat->id}}" type="button" role="tab" aria-controls="v-pills-{{$cat->id}}" @if($index == 0) aria-selected="true" @else aria-selected="false" @endif>{{$cat->name}}</h3>
                    @endforeach
                    </div>
                </div>
                <div class="col-md-7 offset-md-1">
                    <div class="tab-content" id="v-pills-tabContent">
                    @foreach($categories as $index=>$cat)
                        <div class="tab-pane fade @if($index == 0) show active @endif" id="v-pills-{{$cat->id}}" role="tabpanel" aria-labelledby="v-pills-{{$cat->id}}-tab">
                            @php
                                $items = $faqs->where('category_id',$cat->id);
                            @endphp
                            
                            @foreach($items as $faq)
                            <div class="single-faq">
                                <h4>{{$faq->question}}</h4>
                                <p>{{$faq->answer}}</p>
                            </div>
                            @endforeach
                        </div>
                    @endforeach
                    </div>
                </div>
            </div>
            @endif
            
        </div>
    </section>
    <!--Section ends-->
@endsection

@section('script')

@endsection