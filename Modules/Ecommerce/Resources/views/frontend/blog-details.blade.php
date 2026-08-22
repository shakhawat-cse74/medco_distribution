@extends('ecommerce::frontend.layout.main')

@section('title') {{$post->meta_title}}  @endsection

@section('description') {{$post->meta_description}}  @endsection

@section('content')

    <!--Section starts-->
    <section class="pt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6 offset-md-3">
                    @if(isset($post->thumbnail))
                    <img class="post-thumb mb-5" src="" data-src="{{url('frontend/images/blog')}}/{{$post->thumbnail}}" alt="{{$post->title}}" />
                    @endif
                    <h1 class="text-center mb-5">{{ $post->title }}</h1>
                    <p class="mb-5">{!! $post->description !!}</p>
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