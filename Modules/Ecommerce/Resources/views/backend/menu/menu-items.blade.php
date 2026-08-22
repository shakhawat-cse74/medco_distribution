@extends('backend.layout.main')
@section('content')

@push('css')
<style>
  #menu-items .card{margin-bottom: 15px}
  #menu-items .card .card-header{padding: 1rem}
  .item-list,.info-box{background: #fff;padding: 10px;}
  .item-list-body{max-height: 300px;overflow-y: scroll;}
  .panel-body p{margin-bottom: 5px;}
  .info-box{margin-bottom: 15px;}
  .item-list-footer{padding-top: 10px;}
  .btn-menu-select{padding: 4px 10px}
  .disabled{pointer-events: none; opacity: 0.7;}
  .menu-item-bar{background: #eee;padding: 10px 15px;border:1px solid #d7d7d7;margin-bottom: 10px; width: 75%; cursor: move;display: block;}
  #serialize_output{display: block;}
  .menu.ui-sortable{list-style:none;padding-left:0}
  body.dragging, body.dragging * {cursor: move !important;}
  .dragged {position: absolute;z-index: 1;}
  ol.example li.placeholder {position: relative;}
  ol.example li.placeholder:before {position: absolute;}
  #menuitem{list-style: none;}
  #menuitem ul{list-style: none;}
  .input-box{width:75%;background:#fff;padding: 10px;box-sizing: border-box;margin-bottom: 5px;}
  .input-box .form-control{width: 50%}
</style>	
@endpush

<section>

    <div class="container-fluid">
        @if($errors->any())
        <div class="alert alert-danger alert-dismissible text-center mar-bot-30"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <ul>
                @foreach($errors->all() as $error)
                <li>{{ $error}}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if(session()->has('message'))
        <div class="alert alert-{{session('type')}} alert-dismissible text-center mar-bot-30"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session('message') }}</div>
        @endif
    </div>

    <div class="container-fluid">

        <div class="card mb-0">
            <div class="card-body">
                <h4><span>{{__('db.Add Menu Items')}}</span>- <strong>{{$desiredMenu->title}}</strong></h4>
            </div>
        </div>

        <div class="row" id="main-row">
            <div class="col-sm-4 cat-form mt-3 @if(count($menus) == 0) disabled @endif">

                <div class="accordion" id="menu-items">
                    <div class="card">
                        <div class="card-header">
                            <h4 data-toggle="collapse" data-target="#categories-list" aria-expanded="true" aria-controls="categories-list">{{__('db.Categories')}} <span class="pull-right"><i class="dripicons-chevron-down"></i></span></h4>
                        </div>
                        <div class="collapse show" id="categories-list" data-parent="#accordionExample" data-parent="#menu-items">
                            <div class="card-body">
                                <div class="item-list-body">
                                    @foreach($categories as $cat)
                                    <p><input type="checkbox" name="select-category[]" value="{{$cat->id}}"> {{$cat->name}}</p>
                                    @endforeach
                                </div>
                                <div class="item-list-footer">
                                    <label class="btn btn-sm btn-default"><input type="checkbox" id="select-all-categories"> {{__('db.Select All')}}</label>
                                    <button type="button" class="pull-right btn btn-default btn-sm" id="add-categories">{{__('db.Add to Menu')}}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h4 data-toggle="collapse" data-target="#collections-list" aria-expanded="flase" aria-controls="collections-list">{{__('db.Collections')}} <span class="pull-right"><i class="dripicons-chevron-down"></i></span></h4>
                        </div>
                        <div class="collapse" id="collections-list" data-parent="#accordionExample" data-parent="#menu-items">
                            <div class="card-body">
                                <div class="item-list-body">
                                    @foreach($collections as $collection)
                                    <p><input type="checkbox" name="select-collection[]" value="{{$collection->id}}"> {{$collection->name}}</p>
                                    @endforeach
                                </div>
                                <div class="item-list-footer">
                                    <label class="btn btn-sm btn-default"><input type="checkbox" id="select-all-collections"> {{__('db.Select All')}}</label>
                                    <button type="button" class="pull-right btn btn-default btn-sm" id="add-collections">{{__('db.Add to Menu')}}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h4 data-toggle="collapse" data-target="#brands-list" aria-expanded="flase" aria-controls="brands-list">{{__('db.Brands')}} <span class="pull-right"><i class="dripicons-chevron-down"></i></span></h4>
                        </div>
                        <div class="collapse" id="brands-list" data-parent="#accordionExample" data-parent="#menu-items">
                            <div class="card-body">
                                <div class="item-list-body">
                                    @foreach($brands as $brand)
                                    <p><input type="checkbox" name="select-brand[]" value="{{$brand->id}}"> {{$brand->title}}</p>
                                    @endforeach
                                </div>
                                <div class="item-list-footer">
                                    <label class="btn btn-sm btn-default"><input type="checkbox" id="select-all-brands"> {{__('db.Select All')}}</label>
                                    <button type="button" class="pull-right btn btn-default btn-sm" id="add-brands">{{__('db.Add to Menu')}}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h4 href="#pages-list" data-toggle="collapse" data-target="#pages-list" aria-expanded="false" aria-controls="pages-list">{{__('db.Pages')}} <span class="pull-right"><i class="dripicons-chevron-down"></i></span></h4>
                        </div>
                        <div class="collapse" id="pages-list" data-parent="#menu-items">
                            <div class="card-body">
                                <div class="item-list-body">
                                    @foreach($pages as $page)
                                    <p><input type="checkbox" name="select-page[]" value="{{$page->id}}"> {{$page->page_name}}</p>
                                    @endforeach
                                </div>
                                <div class="item-list-footer">
                                    <label class="btn btn-sm btn-default"><input type="checkbox" id="select-all-pages"> {{__('db.Select All')}}</label>
                                    <button type="button" id="add-pages" class="pull-right btn btn-default btn-sm">{{__('db.Add to Menu')}}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h4 href="#blog-list" data-toggle="collapse" data-target="#blog-list" aria-expanded="false" aria-controls="blog-list">{{__('db.Blog')}} <span class="pull-right"><i class="dripicons-chevron-down"></i></span></h4>
                        </div>
                        <div class="collapse" id="blog-list" data-parent="#menu-items">
                            <div class="card-body">
                                <div class="item-list-body">
                                    <p><input type="checkbox" name="select-blog"> Blog </p>
                                </div>
                                <div class="item-list-footer">
                                    <button type="button" id="add-blog" class="pull-right btn btn-default btn-sm">{{__('db.Add to Menu')}}</button>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h4 href="#custom-links" data-toggle="collapse" data-target="#custom-links" aria-expanded="false" aria-controls="custom-links">{{__('db.Custom Links')}} <span class="pull-right"><i class="dripicons-chevron-down"></i></span></h4>
                        </div>
                        <div class="collapse" id="custom-links" data-parent="#menu-items">
                            <div class="card-body">
                                <div class="item-list-body">
                                    <div class="form-group">
                                        <label>{{__('db.URL')}}</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">https://</span>
                                            </div>
                                            <div class="input-group-append">
                                                <input type="url" id="url" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>{{__('db.Link Text')}}</label>
                                        <input type="text" id="linktext" class="form-control" placeholder="">
                                    </div>
                                </div>
                                <div class="item-list-footer">
                                    <button type="button" class="pull-right btn btn-default btn-sm" id="add-custom-link">{{__('db.Add to Menu')}}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-8 cat-view mt-3">
                <div class="card">
                    <div class="card-header">
                        <h3><span>{{__('db.Menu Structure')}}</span></h3>
                    </div>
                    <div class="card-body">
                        <div id="menu-content">
                            <div id="result"></div>
                            <div style="min-height: 250px;">
                                <p>{{__('db.Select categories, pages or add custom links to menus')}}.</p>
                                @if($desiredMenu != '')
                                <ul class="menu ui-sortable" id="menuitems">
                                    @if(!empty($menuitems))
                                    @foreach($menuitems as $key=>$item)
                                    <li data-id="{{$item->id}}"><span class="menu-item-bar"> @if(empty($item->name)) {{$item->title}} @else {{$item->name}} @endif <a href="#collapse{{$item->id}}" class="pull-right" data-toggle="collapse"><i class="fa fa-angle-down"></i></a></span>
                                        <div class="collapse" id="collapse{{$item->id}}">
                                            <div class="input-box">
                                                <form method="post" action="{{url('menu/menuitem/update')}}/{{$item->id}}">
                                                    {{csrf_field()}}
                                                    <div class="form-group">
                                                        <label>{{__('db.Link Name')}}</label>
                                                        <input type="text" name="name" value="@if(empty($item->name)) {{$item->title}} @else {{$item->name}} @endif" class="form-control">
                                                    </div>
                                                    @if($item->type == 'custom')
                                                    <div class="form-group">
                                                        <label>{{__('db.URL')}}</label>
                                                        <input type="text" name="slug" value="{{$item->slug}}" class="form-control">
                                                    </div>
                                                    <div class="form-group">
                                                        <input type="checkbox" name="target" value="_blank" @if($item->target == '_blank') checked @endif> {{__('db.Open in a new tab')}}
                                                    </div>
                                                    @endif
                                                    <div class="form-group">
                                                        <button class="btn btn-sm btn-primary"><i class="dripicons-checkmark"></i></button>
                                                        <a href="{{url('menu/menuitem/delete')}}/{{$item->id}}/{{$key}}/x" class="btn btn-sm btn-danger"><i class="dripicons-trash"></i></a>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                        <ul>
                                            @if(isset($item->children))
                                            @foreach($item->children as $m)
                                            @foreach($m as $in=>$data)
                                            <li data-id="{{$data->id}}" class="menu-item"> <span class="menu-item-bar"> @if(empty($data->name)) {{$data->title}} @else {{$data->name}} @endif <a href="#collapse{{$data->id}}" class="pull-right" data-toggle="collapse"><i class="fa fa-angle-down"></i></a></span>
                                                <div class="collapse" id="collapse{{$data->id}}">
                                                    <div class="input-box">
                                                        <form method="post" action="{{url('update-menuitem')}}/{{$data->id}}">
                                                            {{csrf_field()}}
                                                            <div class="form-group">
                                                                <label>{{__('db.Link Name')}}</label>
                                                                <input type="text" name="name" value="@if(empty($data->name)) {{$data->title}} @else {{$data->name}} @endif" class="form-control">
                                                            </div>
                                                            @if($data->type == 'custom')
                                                            <div class="form-group">
                                                                <label>{{__('db.URL')}}</label>
                                                                <input type="text" name="slug" value="{{$data->slug}}" class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <input type="checkbox" name="target" value="_blank" @if($data->target == '_blank') checked @endif> {{__('db.Open in a new tab')}}
                                                            </div>
                                                            @endif
                                                            <div class="form-group">
                                                                <button class="btn btn-sm btn-primary"><i class="dripicons-checkmark"></i></button>
                                                                <a href="{{url('menu/menuitem/delete')}}/{{$data->id}}/{{$key}}/{{$in}}" class="btn btn-sm btn-danger"><i class="dripicons-trash"></i></a>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                                <ul></ul>
                                            </li>
                                            @endforeach
                                            @endforeach
                                            @endif
                                        </ul>
                                    </li>
                                    @endforeach
                                    @endif
                                </ul>
                                @endif
                            </div>
                            @if($desiredMenu != '')
                            <div class="text-right">
                                <button class="btn btn-sm btn-primary" id="saveMenu">{{__('db.Save Menu')}}</button>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="serialize_output" class="d-none">@if($desiredMenu){{$desiredMenu->content}}@endif</div>
</section>
@endsection

@push('scripts')
<script>
{!! file_get_contents(Module::find('Ecommerce')->getPath(). "/assets/js/sortable.js") !!}
</script>
<script>
    "use strict";

    $('#select-all-categories').click(function(event) {
        if (this.checked) {
            $('#categories-list :checkbox').each(function() {
                this.checked = true;
            });
        } else {
            $('#categories-list :checkbox').each(function() {
                this.checked = false;
            });
        }
    });

    $('#select-all-collections').click(function(event) {
        if (this.checked) {
            $('#collections-list :checkbox').each(function() {
                this.checked = true;
            });
        } else {
            $('#collections-list :checkbox').each(function() {
                this.checked = false;
            });
        }
    });

    $('#select-all-pages').click(function(event) {
        if (this.checked) {
            $('#pages-list :checkbox').each(function() {
                this.checked = true;
            });
        } else {
            $('#pages-list :checkbox').each(function() {
                this.checked = false;
            });
        }
    });

    $('#select-all-brands').click(function(event) {
        if (this.checked) {
            $('#brands-list :checkbox').each(function() {
                this.checked = true;
            });
        } else {
            $('#brands-list :checkbox').each(function() {
                this.checked = false;
            });
        }
    });

    @if($desiredMenu)
    $('#add-categories').click(function() {
        var menuid = <?= $desiredMenu->id ?>;
        var n = $('input[name="select-category[]"]:checked').length;
        var array = $('input[name="select-category[]"]:checked');
        var ids = [];
        for (let i = 0; i < n; i++) {
            ids[i] = array.eq(i).val();
        }
        if (ids.length == 0) {
            return false;
        }
        $.ajax({
            type: "get",
            url: "{{url('menu/add-category-to-menu')}}/"+menuid+"/"+ids,
            success: function(res) {
                location.reload();
            }
        })
    })

    $('#add-collections').click(function() {
        var menuid = <?= $desiredMenu->id ?>;
        var n = $('input[name="select-collection[]"]:checked').length;
        var array = $('input[name="select-collection[]"]:checked');
        var ids = [];
        for (let i = 0; i < n; i++) {
            ids[i] = array.eq(i).val();
        }
        if (ids.length == 0) {
            return false;
        }
        $.ajax({
            type: "get",
            url: "{{url('menu/add-collection-to-menu')}}/"+menuid+"/"+ids,
            success: function(res) {
                location.reload();
            }
        })
    })

    $('#add-brands').click(function() {
        var menuid = <?= $desiredMenu->id ?>;
        var n = $('input[name="select-brand[]"]:checked').length;
        var array = $('input[name="select-brand[]"]:checked');
        var ids = [];
        for (let i = 0; i < n; i++) {
            ids[i] = array.eq(i).val();
        }
        if (ids.length == 0) {
            return false;
        }
        $.ajax({
            type: "get",
            url: "{{url('menu/add-brand-to-menu')}}/"+menuid+"/"+ids,
            success: function(res) {
                location.reload();
            }
        })
    })

    $('#add-pages').click(function() {
        var menuid = <?= $desiredMenu->id ?>;
        var n = $('input[name="select-page[]"]:checked').length;
        var array = $('input[name="select-page[]"]:checked');
        var ids = [];
        for (let i = 0; i < n; i++) {
            ids[i] = array.eq(i).val();
        }
        if (ids.length == 0) {
            return false;
        }
        $.ajax({
            type: "get",
            url: "{{url('menu/add-page-to-menu')}}/"+menuid+"/"+ids,
            success: function(res) {
                location.reload();
            }
        })
    })

    $('#add-blog').click(function() {
        var menuid = <?= $desiredMenu->id ?>;
        var link = "{{__('db.Blog')}}";
        $.ajax({
            type: "get",
            url: "{{url('menu/add-blog-to-menu')}}/"+menuid+"/"+link,
            success: function(res) {
                location.reload();
            }
        })
    })

    $("#add-custom-link").click(function() {
        var menuid = <?= $desiredMenu->id ?>;
        var url = btoa($('#url').val());
        var link = $('#linktext').val();
        if (url.length > 0 && link.length > 0) {
            $.ajax({
                type: "get",
                url: "{{url('menu/add-custom-link')}}/"+menuid+"/"+link+"/"+url,
                success: function(res) {
                    location.reload();
                }
            })
        }
    })

    var group = $("#menuitems").sortable({
        group: 'serialization',
        onDrop: function($item, container, _super) {
            var data = group.sortable("serialize").get();
            var jsonString = JSON.stringify(data, null, ' ');
            $('#serialize_output').text(jsonString);
            _super($item, container);
        }
    });

    $('#saveMenu').click(function() {
        var menuid = "{{ $desiredMenu->id }}";
        var location = "{{ $desiredMenu->location }}";
        var data = group.sortable("serialize").get();
        var jsonString = JSON.stringify(data, null, ' ');
        $('#serialize_output').text(jsonString);
        var newText = $("#serialize_output").text();
        var data = JSON.parse($("#serialize_output").text());
        $(this).attr('disabled','true').html('<span class="spinner-border text-light" role="status"></span> {{trans("file.Saving")}}...');
        $.ajax({
            type: "post",
            data: {menuid:menuid,data:data},
            url: "{{url('menu/update')}}/",
            success: function(res) {
                window.location.reload();
            }
        })
    })
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    @endif
</script>
@endpush