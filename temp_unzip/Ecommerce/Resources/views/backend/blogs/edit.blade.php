@extends('backend.layout.main') @section('content')
@if(session()->has('not_permitted'))
<div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
@endif

@push('css')
<style> 
    .widgets {
        list-style: none;
        padding-left: 0;
    }

    .widgets li {
        background-color: #fff;
        border: 1px solid #ddd;
        cursor: pointer;
        display: inline-block;
        padding: 10px 15px;
        margin-bottom: 20px;
    }

    .widgets li:last-child {
        margin-bottom: 0;
    }

    .toggle-collapse {
        display: block;
    }

    .widgets li.placeholder {
        position: relative;
    }

    .ajax-message {
        bottom: 10px;
        position: fixed;
        right: 10px;
        z-index: 999;
    }

    #page-template>div {
        border: 1px solid #ddd;
        padding: 20px
    }

    #page-template div>ul {
        min-height: 300px;
    }

    #layout.widgets li {
        display: block;
        width: 100%;
    }
</style>
@endpush
<section>
    <div class="container-fluid">
        <form id="add-page-form" method="post" action="{{route('page.update')}}" class="form-horizontal" enctype='multipart/form-data'>

            @csrf
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            {{__('db.Edit Post')}}
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
                            <button id="draft" type="submit" class="btn btn-warning"><i class="fa fa-save"></i> {{__('db.Save as Draft')}}</button>
                            <button id="publish" type="submit" class="btn btn-primary"><i class="fa fa-check"></i> {{__('db.Publish')}}</button>
                            <input id="post_id" name="post_id" type="hidden" value="{{$id}}">
                            <input type="hidden" name="user_id" value="{{auth()->user()->id}}" />
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

    $(document).ready(function(){
        $.ajax({
            type: "get",
            url: "{{url('blog/edit')}}/{{$id}}",
            success: function(data) {  
                $('input[name="title"]').val(data.title);
                $('input[name="slug"]').val(data.slug);
                if(data.description){
                    tinymce.get('description').setContent(data.description);
                }
                $('input[name="meta_title"]').val(data.meta_title);
                $('input[name="meta_description"]').val(data.meta_description);
                $('input[name="og_title"]').val(data.og_title);
                $('input[name="og_description"]').val(data.og_description);
            }
        })

        text_editor('textarea');
    })

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

    function pageUpdate() {
        tinyMCE.triggerSave();

        var form = $('#add-page-form')[0];

        var formData = new FormData(form);

        $.ajax({
            type: "post",
            data: formData,
            dataType: 'JSON',
            processData: false,
            contentType: false,
            url: "{{route('blog.post.update')}}",
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


    $('input[name="title"]').on('input', function() {
        var slug = $(this).val().toLowerCase().replace(/[^\w ]+/g, '').replace(/ +/g, '-');
        $('input[name="slug"]').val(slug);
    });

    $('input[name="title"], input[name="slug"]').focusout(function(){
        var slug = $('input[name="slug"]').val();
        $.ajax({
            type: "get",
            url: "{{url('blog/edit')}}/{{$id}}/" + slug,
            success: function(data) {
                console.log(data);
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
        pageUpdate();
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
        pageUpdate();
        @endif
    })

    text_editor('#description');
    
</script>
@endpush