@extends('backend.layout.main') @section('content')

@push('css')
<style>
.search_result {border:1px solid #e4e6fc;border-radius:5px;overflow-y: scroll;}
.search_result > div, .selected_items > div {border-top:1px solid #e4e6fc;cursor:pointer;display:flex;align-items:center;padding: 10px;position: relative;}
.search_result > div > img, .selected_items > div > img {margin-right: 10px;max-width: 40px;}
.search_result > div h4, .selected_items > div h4 {font-size: 0.9rem;}
.search_result > div i {color:#54b948;position:absolute;right:5px;top:30%}
.search_result div:first-child {border-top:none}
.selected_items .remove_item {position: absolute;right: 20px;top:20px};
</style>
@endpush

@if($errors->has('name'))
<div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ $errors->first('name') }}</div>
@endif
@if($errors->has('image'))
<div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ $errors->first('image') }}</div>
@endif
@if(session()->has('message'))
  <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('message') }}</div>
@endif
@if(session()->has('not_permitted'))
  <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
@endif

<section>
    <div class="container-fluid">
        <form id="add-form" method="post" action="{{route('collection.store')}}" class="form-horizontal" enctype='multipart/form-data'>

            @csrf
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            {{__('db.Add Collection')}}
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>{{__('db.Collection Name')}} *</label>
                                <input type="text" name="name" required class="form-control">
                            </div>
                            <div class="form-group">
                                <label>{{__('db.Permalink')}} *</label>
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">{{url('/')}}/</div>
                                    </div>
                                    <input type="text" name="slug" required class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>{{__('db.Meta Title')}}</label>
                                <input type="text" name="page_title" class="form-control" placeholder="{{__('db.Meta Title')}}">
                            </div>
                            <div class="form-group">
                                <label>{{__('db.Meta Description')}}</label>
                                <input type="text" name="short_description" class="form-control" placeholder="{{__('db.Meta Description')}}">
                            </div>

                            <div class="form-group">
                                <label>{{__('db.products')}}</label>
                                <input type="text" id="search_products" class="form-control">
                            </div>
                            <div class="search_result"></div>
                            <h4 class="mt-5 mb-3">Selected Items</h4>
                            <div class="selected_items"></div>
                            <textarea class="selected_ids hidden" name="products"></textarea>
                        </div>
                    </div>

                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <input type="hidden" name="status" value="1" />
                            <button id="draft" type="submit" class="btn btn-warning" disabled><i class="fa fa-save"></i> {{__('db.Save as Draft')}}</button>
                            <button id="publish" type="submit" class="btn btn-primary" disabled><i class="fa fa-check"></i> {{__('db.Publish')}}</button>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>
</section>
@endsection

@push('scripts')
<script type="text/javascript">
    "use strict";

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('#search_products').on('input', function() {
        var item = $(this).val();
        $('.search_result').html('<div class="d-block text-center"><div class="spinner-border text-secondary" role="status"><span class="sr-only">Loading...</span></div></div>');

        if(item.length >= 3){
            $.ajax({
                type: "get",
                url: "{{url('search')}}/" + item,
                success: function(data) {
                    $('.search_result').html('').css('height','200px');
                    $.each(data,function(key, value){ 
                        var image = value.image.split(',');
                        $('.search_result').append('<div data-id="'+value.id+'"><img src="{{asset("images/product/small/")}}/'+image[0]+'"><h4>'+value.name+'</h4><i class="dripicons-checkmark d-none"></i></div>')
                    })
                }
            })  
        } else if (item.length < 3) {
            $('.search_result').html('');
        }
    });

    $(document).on('click','.search_result div',function(){
        $(this).find('i').removeClass('d-none');
        var selected_item = '<div data-id="'+$(this).data('id')+'">'+$(this).html()+'<span class="remove_item"><i class="dripicons-cross"></i></span></div>';
        if ($('.selected_ids').html().indexOf($(this).data('id')) === -1){
            $('.selected_items').prepend(selected_item);
            $('.selected_ids').append($(this).data('id')+','); 
            $('.selected_items .dripicons-checkmark').addClass('d-none');
        }       
    });

    $(document).on('click','.remove_item',function(){
        var item = $(this).parent().remove();
        var remove_id = $(this).parent().data('id');
        var selected_ids = $('.selected_ids').html().replace(remove_id+',','');
        $('.selected_ids').html(selected_ids);
        
    });

    $('input[name="name"]').on('input', function() {
        var slug = $(this).val().toLowerCase().replace(/[^\w ]+/g, '').replace(/ +/g, '-');
        $('input[name="slug"]').val(slug);
    });

    $('input[name="name"], input[name="slug"]').focusout(function(){
        var slug = $('input[name="slug"]').val();

        if(slug.length > 1){
            $.ajax({
                type: "get",
                url: "{{url('collection')}}/" + slug,
                success: function(data) {
                    $('input[name="slug"]').val(data);
                }
            })  

            $('#draft').prop('disabled',false);
            $('#publish').prop('disabled',false);
        }else{
            $('#draft').prop('disabled',true);
            $('#publish').prop('disabled',true);
        }
    })

    $('#publish').on('click', function(e) {
        e.preventDefault();
        $('input[name="status"]').val(1);
        $('#add-form').submit();
    })

    $('#draft').on('click', function(e) {
        e.preventDefault();
        $('input[name="status"]').val(0);
        $('#add-form').submit();
    })

</script>
@endpush
