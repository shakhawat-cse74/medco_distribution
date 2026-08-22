@extends('backend.layout.main')
@section('content')

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
        padding: 10px 15px;
        margin-bottom: 20px;
        width: 100%;
    }

    .widgets li:last-child {
        margin-bottom: 0;
    }

    .toggle-collapse {
        display: block;
    }

    #widget-list .toggle-collapse span {
        display: none;
    }

    body.dragging,
    body.dragging * {
        cursor: move !important;
    }

    body.dragging,
    body.dragging * {
        cursor: move !important;
    }

    .dragged {
        position: absolute;
        opacity: 0.5;
        z-index: 2000;
    }

    /* .widget-section{
        min-height: 200px;
    } */

    .widgets li.placeholder {
        position: relative;
        /** More li styles **/
    }

    .widgets li.placeholder:before {
        position: absolute;
        /** Define arrowhead **/
    }

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

        <div class="row" id="main-row">
            <div class="col-sm-4">
                <h4><span>{{__('db.Available Widgets')}}</span></h4>
                <p>{{__('db.To add a widget to a section, drag it onto that section')}}</p>

                <ul id="widget-list" class="widgets">
                    <li><a class="toggle-collapse"> {{__('db.Text')}} <span class="pull-right"><i class="fa fa-angle-down"></i></span></a>
                        <div class="collapse">
                            <hr>
                            <form action="" class="">
                                {{csrf_field()}}
                                <label>{{__('db.Title')}}</label>
                                <input type="text" name="text_title" class="form-control">
                                <label>{{__('db.Text')}}</label>
                                <textarea name="text_content" class="form-control"></textarea>
                                <div class="form-group mt-2">
                                    <button class="btn btn-sm btn-primary btn-save"><i class="dripicons-checkmark"></i></button>
                                    <a href="" class="btn btn-sm btn-danger btn-delete"><i class="dripicons-trash"></i></a>
                                    <input type="hidden" name="location">
                                    <input type="hidden" name="order">
                                    <input type="hidden" name="id">
                                    <input type="hidden" name="name" value="text-widget">
                                </div>
                            </form>
                        </div>
                    </li>
                    <li><a class="toggle-collapse"> {{__('db.Custom Menu')}} <span class="pull-right"><i class="fa fa-angle-down"></i></span></a>
                        <div class="collapse">
                            <hr>
                            <form action="" class="">
                                {{csrf_field()}}
                                <label>{{__('db.Title')}}</label>
                                <input type="text" name="quick_links_title" class="form-control">
                                <label>{{__('db.Select menu')}}</label>
                                <select name="quick_links_menu" class="selectpicker form-control">
                                    @foreach($menus as $menu)
                                    <option value="{{$menu->id}}">{{$menu->title}}</option>
                                    @endforeach
                                </select>
                                <div class="form-group mt-2">
                                    <button class="btn btn-sm btn-primary btn-save"><i class="dripicons-checkmark"></i></button>
                                    <a href="" class="btn btn-sm btn-danger btn-delete"><i class="dripicons-trash"></i></a>
                                    <input type="hidden" name="location">
                                    <input type="hidden" name="order">
                                    <input type="hidden" name="id">
                                    <input type="hidden" name="name" value="custom-menu-widget">
                                </div>
                            </form>
                        </div>
                    </li>
                    <li><a class="toggle-collapse"> {{__('db.Site Features')}} <span class="pull-right"><i class="fa fa-angle-down"></i></span></a>
                        <div class="collapse">
                            <hr>
                            <form action="" class="" enctype="multipart/form-data">
                                {{csrf_field()}}
                                <label>{{__('db.Title')}}</label>
                                <input type="text" name="feature_title" class="form-control">
                                <label>{{__('db.Text')}}</label>
                                <textarea name="feature_secondary_title" class="form-control"></textarea>
                                <label>{{__('db.Icon')}}</label>
                                <input type="file" name="feature_icon" class="form-control"> 
                                <div class="form-group mt-2">
                                    <button class="btn btn-sm btn-primary btn-save"><i class="dripicons-checkmark"></i></button>
                                    <a href="" class="btn btn-sm btn-danger btn-delete"><i class="dripicons-trash"></i></a>
                                    <input type="hidden" name="location">
                                    <input type="hidden" name="order">
                                    <input type="hidden" name="id">
                                    <input type="hidden" name="name" value="site-features-widget">
                                </div>
                            </form>
                        </div>
                    </li>
                    <li><a class="toggle-collapse"> {{__('db.Site Information')}} <span class="pull-right"><i class="fa fa-angle-down"></i></span></a>
                        <div class="collapse">
                            <hr>
                            <form action="" class="">
                                {{csrf_field()}}
                                <label>{{__('db.Title')}}</label>
                                <input type="text" name="site_info_name" class="form-control">
                                <label>{{__('db.Text')}}</label>
                                <textarea name="site_info_description" class="form-control"></textarea>
                                <label>{{__('db.Address')}}</label>
                                <input type="text" name="site_info_address" class="form-control">
                                <label>{{__('db.Phone')}}</label>
                                <input type="text" name="site_info_phone" class="form-control">
                                <label>{{__('db.Email')}}</label>
                                <input type="text" name="site_info_email" class="form-control">
                                <label>{{__('db.Hours')}}</label>
                                <input type="text" name="site_info_hours" class="form-control">
                                <div class="form-group mt-2">
                                    <button class="btn btn-sm btn-primary btn-save"><i class="dripicons-checkmark"></i></button>
                                    <a href="" class="btn btn-sm btn-danger btn-delete"><i class="dripicons-trash"></i></a>
                                    <input type="hidden" name="location">
                                    <input type="hidden" name="order">
                                    <input type="hidden" name="id">
                                    <input type="hidden" name="name" value="site-info-widget">
                                </div>
                            </form>
                        </div>
                    </li>
                    <li><a class="toggle-collapse"> {{__('db.Newsletter form')}} <span class="pull-right"><i class="fa fa-angle-down"></i></span></a>
                        <div class="collapse">
                            <hr>
                            <form action="" class="">
                                {{csrf_field()}}
                                <label>{{__('db.Title')}}</label>
                                <input type="text" name="newsletter_title" class="form-control">
                                <label>{{__('db.Text')}}</label>
                                <textarea name="newsletter_text" class="form-control"></textarea>
                                <div class="form-group mt-2">
                                    <button class="btn btn-sm btn-primary btn-save"><i class="dripicons-checkmark"></i></button>
                                    <a href="" class="btn btn-sm btn-danger btn-delete"><i class="dripicons-trash"></i></a>
                                    <input type="hidden" name="location">
                                    <input type="hidden" name="order">
                                    <input type="hidden" name="id">
                                    <input type="hidden" name="name" value="newsletter-widget">
                                </div>
                            </form>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="col-sm-8">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="mb-3">{{__('db.Section Above Footer')}}</h4>
                                <div class="widget-section">
                                    <ul id="footer_top" class="widgets" style="min-height: 100px;">
                                        @if(isset($footer_top))
                                        @foreach($footer_top as $widget)
                                        @include('ecommerce::backend.widgets.widget-loop')
                                        @endforeach
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <h4 class="mb-3">{{__('db.Footer Section')}}</h4>
                                <div class="widgets widget-section">
                                    <ul id="footer" class="widgets" style="min-height: 100px;">
                                        @if(isset($footer))
                                        @foreach($footer as $widget)
                                        @include('ecommerce::backend.widgets.widget-loop')
                                        @endforeach
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="mb-3">{{__('db.product Details Sidebar')}}</h4>
                                <div class="widgets widget-section">
                                    <ul id="product_details" class="widgets" style="min-height: 100px;">
                                        @if(isset($product_details_sidebar))
                                        @foreach($product_details_sidebar as $widget)
                                        @include('ecommerce::backend.widgets.widget-loop')
                                        @endforeach
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
{!! file_get_contents(Module::find('Ecommerce')->getPath(). "/assets/js/sortable2.js") !!}
</script>
<script>
    "use strict";

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var widget_list = document.getElementById('widget-list'),
        footer_top = document.getElementById('footer_top'),
        footer = document.getElementById('footer'),
        product_details = document.getElementById('product_details');

    new Sortable(widget_list, {
        group: {
            name: 'shared',
            pull: 'clone',
            put: false // Do not allow items to be put into this list
        },
        animation: 150,
        sort: false // To disable sorting: set sort to false
    });

    function createSortable(container, groupName) {
        return new Sortable(container, {
            group: {
                name: groupName,
                pull: 'clone'
            },
            animation: 150,
            onUpdate: function(e) {
                var itemEl = e.item;
                widgetOrder(e.item.parentNode.id);
            },
            onAdd: function(e) {
                var itemEl = e.item;
                itemEl.classList.add('new');
                widgetAdd(e.item.parentNode.id);
            }
        });
    }

    createSortable(footer_top, 'shared');
    createSortable(footer, 'shared');
    createSortable(product_details, 'shared');

    function widgetAdd(parent) {
        var item = $('#' + parent + ' li.new');
        var order = item.index();
        var name = item.find('input[name="name"]').val();
        item.find('input').val('');
        item.find('input[name="order"]').val(order);
        item.find('input[name="location"]').val(parent);
        item.find('input[name="name"]').val(name);
        var data = item.find('form').serialize();
        
        $.ajax({
            type: "post",
            data: data,
            url: "{{url('widget/store')}}/",
            success: function(data) {
                item.find('input[name="id"]').val(data.id);
                item.removeClass('new');

                widgetOrder(parent);
                
                var message = '<div class="ajax-message"><div class="alert alert-success alert-dismissible text-center mar-bot-30"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{trans("file.Widget saved")}}</div></div>';
                $('body section').append(message);
                $("div.alert").delay(3000).slideUp(800);
            }
        })
    }

    function widgetOrder(parent){
        var itemArray = [];
        $('#' + parent + ' li').each(function(index) {
            var id = $(this).find('input[name="id"]').val();
            itemArray.push({ id: id, index: index });
        })
        $.ajax({
            type    : "POST",
            url     : "{{route('widget.order')}}",
            data    : JSON.stringify(itemArray),
            contentType: 'application/json', 
            success : function(response) {
            }    
        });
    }

    $(document).on('click', '.btn-save', function(e) {
        e.preventDefault();
        var item = $(this).closest('form')[0];

        var formData = new FormData(item);

        $.ajax({
            type: "post",
            data: formData,
            dataType: 'JSON',
            processData: false,
            contentType: false,
            url: "{{url('widget/update')}}/",
            success: function(result) {                
                var message = '<div class="ajax-message"><div class="alert alert-success alert-dismissible text-center mar-bot-30"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{trans("file.Widget saved")}}</div></div>';
                $('body section').append(message);
                $("div.alert").delay(3000).slideUp(800);
            }
        })
    })

    $(document).on('click', '.btn-delete', function(e) {
        e.preventDefault();
        var item = $(this).siblings('input[name="id"]').val();
        var parent = $(this).closest('ul').attr('id');
        $(this).closest('li').remove();
        $.ajax({
            type: "get",
            url: "{{url('widget/delete')}}/"+item,
            success: function(res) {  
                widgetOrder(parent);
            }
        })
    })

    $(document).on('click', '.widget-section .toggle-collapse', function() {
        $(this).parent().find('.collapse').toggleClass('show');
    })

</script>
@endpush