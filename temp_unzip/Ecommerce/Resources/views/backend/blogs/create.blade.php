@extends('backend.layout.main') @section('content')
@if(session()->has('not_permitted'))
<div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
@endif

@push('css')
<style> 

    .ajax-message {
        bottom: 10px;
        position: fixed;
        right: 10px;
        z-index: 999;
    }

</style>
@endpush
<section>
    <div class="container-fluid">
        <form id="add-page-form" method="post" action="{{route('blog.post.store')}}" class="form-horizontal" enctype='multipart/form-data'>

            @csrf
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            {{__('db.Add Post')}}
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>{{__('db.Post Title')}} *</label>
                                <input type="text" name="title" required class="form-control" placeholder="{{__('db.Post title')}}">
                            </div>
                            <div class="form-group">
                                <label>{{__('db.Permalink')}} *</label>
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">{{url('/')}}/blog/</div>
                                    </div>
                                    <input type="text" name="slug" required class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="image">{{__('db.Post Thumbnail')}}</label>
                                <input type="file" class="form-control" name="thumbnail">
                            </div>
                            <div id="text-block" class="form-group">
                                <label>{{__('db.Description')}} *</label>
                                <textarea id="description" name="description" class="form-control"></textarea>
                            </div>
                            <div class="form-group">
                                <label>{{__('db.Meta Title')}}</label>
                                <input type="text" name="meta_title" class="form-control" placeholder="{{__('db.Meta Title')}}">
                            </div>
                            <div class="form-group">
                                <label>{{__('db.Meta Description')}}</label>
                                <input type="text" name="meta_description" class="form-control" placeholder="{{__('db.Meta Description')}}">
                            </div>
                            <div class="form-group">
                                <label>{{__('db.Og_Title')}}</label>
                                <input type="text" name="og_title" class="form-control" placeholder="{{__('db.Og Title')}}">
                            </div>
                            <div class="form-group">
                                <label>{{__('db.Og_description')}}</label>
                                <input type="text" name="og_description" class="form-control" placeholder="{{__('db.Og Description')}}">
                            </div>
                            <div class="form-group">
                                <label for="image">{{__('db.Og_Image')}}</label>
                                <input type="file" class="form-control" name="og_image">
                            </div>
                        </div>
                    </div>

                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <input type="hidden" name="status" value="1" />
                            <input type="hidden" name="user_id" value="{{auth()->user()->id}}" />
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


    function text_editor(id) {
        tinymce.init({
            selector: id,
            height: 130,
            plugins: [
                'advlist autolink lists link image charmap print preview anchor textcolor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table contextmenu paste code wordcount'
            ],
            toolbar: 'insert | undo redo |  formatselect | bold italic backcolor  | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat',
            branding: false,
        });
    }

    $(document).on('click', '.btn-save', function(e) {
        e.preventDefault();
        $(this).closest('li').find('.collapse').toggleClass('show');
    })

    $(document).on('click', '.btn-delete', function(e) {
        e.preventDefault();
        $(this).closest('li').remove();
    })


    $('input[name="title"]').on('input', function() {
        var slug = $(this).val().toLowerCase().replace(/[^\w ]+/g, '').replace(/ +/g, '-');
        $('input[name="slug"]').val(slug);
    });

    $('input[name="title"], input[name="slug"]').focusout(function(){
        var slug = $('input[name="slug"]').val();
        $.ajax({
            type: "get",
            url: "{{url('blog')}}/post/" + slug,
            success: function(data) {
                $('input[name="slug"]').val(data);
            }
        })    
        
        if($('input[name="title"]').val().length > 1){
            $('#draft').prop('disabled',false);
            $('#publish').prop('disabled',false);
        }else{
            $('#draft').prop('disabled',true);
            $('#publish').prop('disabled',true);
        }
    })

    $('#publish').on('click', function(e) {
        @if(!env('USER_VERIFIED'))
        var message = '<div class="ajax-message"><div class="alert alert-danger alert-dismissible text-center mar-bot-30"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{trans("file.This feature is disabled for demo")}}</div></div>';
        $('body section').append(message);
        $("div.alert").delay(3000).slideUp(800);
        @else
        e.preventDefault();
        $('input[name="status"]').val(1);
        $(this).attr('disabled','true').html('<span class="spinner-border text-light" role="status"></span> {{trans("file.Saving")}}...');
        pageCreate();
        @endif
    })

    $('#draft').on('click', function(e) {
        @if(!env('USER_VERIFIED'))
        var message = '<div class="ajax-message"><div class="alert alert-danger alert-dismissible text-center mar-bot-30"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{trans("file.This feature is disabled for demo")}}</div></div>';
        $('body section').append(message);
        $("div.alert").delay(3000).slideUp(800);
        @else
        e.preventDefault();
        $('input[name="status"]').val(0);
        $(this).attr('disabled','true').html('<span class="spinner-border text-light" role="status"></span> {{trans("file.Saving")}}...');
        pageCreate();
        @endif
    })

    function pageCreate() {
        tinyMCE.triggerSave();

        var form = $('#add-page-form')[0];

        var formData = new FormData(form);

        $.ajax({
            type: "post",
            data: formData,
            dataType: 'JSON',
            processData: false,
            contentType: false,
            url: "{{route('blog.post.store')}}",
            success: function(data) {
                var message = '<div class="ajax-message"><div class="alert alert-success alert-dismissible text-center mar-bot-30"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{trans("file.Page saved")}}</div></div>';
                $('body section').append(message);
                $("div.alert").delay(3000).slideUp(800);
                setTimeout(function () {
                    window.location.href = "{{route('blog.post')}}"; 
                }, 3000);
            }
        });
    }

    text_editor('#description');
    
</script>
@endpush