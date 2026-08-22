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
                            {{__('db.Edit Page')}}
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>{{__('db.Page Name')}} *</label>
                                <input type="text" name="page_name" required class="form-control" placeholder="{{__('db.Page Name')}}">
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
                            <div id="text-block" class="form-group">
                                <label>{{__('db.Description')}} *</label>
                                <textarea id="description" name="description" class="form-control"></textarea>
                            </div>
                            <div id="page-template" class="form-group">
                                <label>{{__('db.Page Layout')}} *</label>
                                <span class="d-block mt-2 mb-2">({{__('db.click on the widgets below to add it to your layout')}})</span>
                                <ul id="widget-list" class="widgets">
                                    <li><a data-class="text-widget"> {{__('db.Text')}}</a></li>
                                    <li><a data-class="three-c-banner-widget"> {{__('db.Three Column Banner')}}</a></li>
                                    <li><a data-class="two-c-banner-widget"> {{__('db.Two Column Banner')}}</a></li>
                                    <li><a data-class="one-c-banner-widget"> {{__('db.One Column Banner')}}</a></li>
                                    <li><a data-class="product-category-widget"> {{__('db.product Category')}}</a></li>
                                    <li><a data-class="category-slider-widget"> {{__('db.Category Slider')}}</a></li>
                                    <li><a data-class="product-collection-widget"> {{__('db.product Collection')}}</a></li>
                                    <li><a data-class="tab-product-collection-widget"> {{__('db.Tabbed Product Collections')}}</a></li>
                                    <li><a data-class="brand-slider-widget"> {{__('db.Brand Slider')}}</a></li>
                                    <li><a data-class="image-slider-widget"> {{__('db.Image Slider Widget')}}</a></li>
                                </ul>

                                <div>
                                    <ul id="layout" class="widgets">
                                        @if(isset($page_widgets))
                                            @foreach($page_widgets as $widget)
                                                @if($widget->name == 'text-widget')
                                                <li>
                                                    <a class="toggle-collapse"> {{__('db.Text')}} <span class="pull-right"><i class="fa fa-angle-down"></i></span></a>
                                                    <div class="collapse">
                                                        <hr>
                                                        <form>
                                                            <label>{{__('db.Title')}}</label>
                                                            <input type="text" name="text_title" class="form-control" value="{{$widget->text_title}}">
                                                            <label>{{__('db.Text')}}</label>
                                                            <textarea name="text_content" class="form-control">{{$widget->text_content}}</textarea>
                                                            <div class="form-group mt-2">
                                                                <button class="btn btn-sm btn-primary btn-save"><i class="dripicons-checkmark"></i></button>
                                                                <button class="btn btn-sm btn-danger btn-delete"><i class="dripicons-trash"></i></button>
                                                                <input type="hidden" name="widget_name" value="text-widget">
                                                                <input type="hidden" name="order" value="{{$widget->order}}">
                                                                <input type="hidden" name="id" value="{{$widget->id}}">
                                                            </div>
                                                        </form>
                                                    </div>
                                                </li>
                                                @endif

                                                @if($widget->name == 'three-c-banner-widget')
                                                <li>
                                                    <a class="toggle-collapse"> {{__('db.Three Column Banner')}} <span class="pull-right"><i class="fa fa-angle-down"></i></span></a>
                                                    <div class="collapse">
                                                        <hr>
                                                        <form>
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <label>{{__('db.Banner Image')}} 1</label>
                                                                    <input type="file" name="three_c_banner_image1" class="form-control">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label>{{__('db.Link')}} 1</label>
                                                                    <input type="text" name="three_c_banner_link1" class="form-control" value="{{$widget->three_c_banner_link1}}">
                                                                </div>
                                                            </div>
                                                            <hr>
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <label>{{__('db.Banner Image')}} 2</label>
                                                                    <input type="file" name="three_c_banner_image2" class="form-control">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label>{{__('db.Link')}} 2</label>
                                                                    <input type="text" name="three_c_banner_link2" class="form-control" value="{{$widget->three_c_banner_link2}}">
                                                                </div>
                                                            </div>
                                                            <hr>
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <label>{{__('db.Banner Image')}} 3</label>
                                                                    <input type="file" name="three_c_banner_image3" class="form-control">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label>{{__('db.Link')}} 3</label>
                                                                    <input type="text" name="three_c_banner_link3" class="form-control" value="{{$widget->three_c_banner_link3}}">
                                                                </div>
                                                            </div>
                                                            <div class="form-group mt-2">
                                                                <button class="btn btn-sm btn-primary btn-save"><i class="dripicons-checkmark"></i></button>
                                                                <a href="" class="btn btn-sm btn-danger btn-delete"><i class="dripicons-trash"></i></a>
                                                                <input type="hidden" name="widget_name" value="three-c-banner-widget">
                                                                <input type="hidden" name="order" value="{{$widget->order}}">
                                                                <input type="hidden" name="id" value="{{$widget->id}}">
                                                            </div>
                                                        </form>
                                                    </div>
                                                </li>
                                                @endif

                                                @if($widget->name == 'two-c-banner-widget')
                                                <li>
                                                    <a class="toggle-collapse"> {{__('db.Two Column Banner')}} <span class="pull-right"><i class="fa fa-angle-down"></i></span></a>
                                                    <div class="collapse">
                                                        <hr>
                                                        <form>
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <label>{{__('db.Banner Image')}} 1</label>
                                                                    <input type="file" name="two_c_banner_image1" class="form-control">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label>{{__('db.Link')}} 1</label>
                                                                    <input type="text" name="two_c_banner_link1" class="form-control" value="{{$widget->two_c_banner_link1}}">
                                                                </div>
                                                            </div>
                                                            <hr>
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <label>{{__('db.Banner Image')}} 2</label>
                                                                    <input type="file" name="two_c_banner_image2" class="form-control">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label>{{__('db.Link')}} 2</label>
                                                                    <input type="text" name="two_c_banner_link2" class="form-control" value="{{$widget->two_c_banner_link2}}">
                                                                </div>
                                                            </div>
                                                            <div class="form-group mt-2">
                                                                <button class="btn btn-sm btn-primary btn-save"><i class="dripicons-checkmark"></i></button>
                                                                <a href="" class="btn btn-sm btn-danger btn-delete"><i class="dripicons-trash"></i></a>
                                                                <input type="hidden" name="widget_name" value="two-c-banner-widget">
                                                                <input type="hidden" name="order" value="{{$widget->order}}">
                                                                <input type="hidden" name="id" value="{{$widget->id}}">
                                                            </div>
                                                        </form>
                                                    </div>
                                                </li>
                                                @endif

                                                @if($widget->name == 'one-c-banner-widget')
                                                <li>
                                                    <a class="toggle-collapse"> {{__('db.One Column Banner')}} <span class="pull-right"><i class="fa fa-angle-down"></i></span></a>
                                                    <div class="collapse">
                                                        <hr>
                                                        <form>
                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    <label>{{__('db.Banner Image')}}</label>
                                                                    <input type="file" name="one_c_banner_image1" class="form-control">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label>{{__('db.Mobile Version')}}</label>
                                                                    <input type="file" name="one_c_banner_image1_mobile" class="form-control">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label>{{__('db.Link')}}</label>
                                                                    <input type="text" name="one_c_banner_link1" class="form-control" value="{{$widget->one_c_banner_link1}}">
                                                                </div>
                                                            </div>
                                                            <div class="form-group mt-2">
                                                                <button class="btn btn-sm btn-primary btn-save"><i class="dripicons-checkmark"></i></button>
                                                                <a href="" class="btn btn-sm btn-danger btn-delete"><i class="dripicons-trash"></i></a>
                                                                <input type="hidden" name="widget_name" value="one-c-banner-widget">
                                                                <input type="hidden" name="order" value="{{$widget->order}}">
                                                                <input type="hidden" name="id" value="{{$widget->id}}">
                                                            </div>
                                                        </form>
                                                    </div>
                                                </li>
                                                @endif

                                                @if($widget->name == 'product-category-widget')
                                                <li>
                                                    <a class="toggle-collapse"> {{__('db.product Category')}} <span class="pull-right"><i class="fa fa-angle-down"></i></span></a>
                                                    <div class="collapse">
                                                        <hr>
                                                        <form>
                                                            <label>{{__('db.Title')}}</label>
                                                            <input type="text" name="product_category_title" class="form-control" value="{{$widget->product_category_title}}">

                                                            <label>{{__('db.Choose Category')}}</label>
                                                            <select name="product_category_id" class="selectpicker form-control">
                                                                @foreach($categories as $cat)
                                                                <option @if($widget->product_category_id == $cat->id) selected @endif value="{{$cat->id}}">{{$cat->name}}</option>
                                                                @endforeach
                                                            </select>

                                                            <label>{{__('db.Layout Type')}}</label>
                                                            <select name="product_category_type" class="selectpicker form-control">
                                                                <option @if($widget->product_category_type == 'gallery') selected @endif value="gallery">Gallery</option>
                                                                <option @if($widget->product_category_type == 'slider') selected @endif value="slider">Slider</option>
                                                            </select>

                                                            <label>{{__('db.Slider Loop')}}</label>
                                                            <select name="product_category_slider_loop" class="selectpicker form-control">
                                                                <option @if($widget->product_category_slider_loop == 'true') selected @endif value="true">Yes</option>
                                                                <option @if($widget->product_category_slider_loop == 'false') selected @endif value="false">No</option>
                                                            </select>

                                                            <label>{{__('db.Number of products to show')}}</label>
                                                            <input type="text" name="product_category_limit" class="form-control" value="{{$widget->product_category_limit}}">

                                                            <div class="form-group mt-2">
                                                                <button class="btn btn-sm btn-primary btn-save"><i class="dripicons-checkmark"></i></button>
                                                                <button class="btn btn-sm btn-danger btn-delete"><i class="dripicons-trash"></i></button>
                                                                <input type="hidden" name="widget_name" value="product-category-widget">
                                                                <input type="hidden" name="order" value="{{$widget->order}}">
                                                                <input type="hidden" name="id" value="{{$widget->id}}">
                                                            </div>
                                                        </form>
                                                    </div>
                                                </li>
                                                @endif

                                                @if($widget->name == 'category-slider-widget')
                                                <li>
                                                    <a class="toggle-collapse"> {{__('db.Category Slider')}} <span class="pull-right"><i class="fa fa-angle-down"></i></span></a>
                                                    <div class="collapse">
                                                        <hr>
                                                        <form>
                                                            <label>{{__('db.Title')}}</label>
                                                            <input type="text" name="category_slider_title" class="form-control" value="{{$widget->category_slider_title}}">

                                                            <label>{{__('db.Choose Categories')}}</label>
                                                            <select name="category_slider_ids[]" class="selectpicker form-control" multiple>
                                                                @foreach($categories as $cat)
                                                                <option @if(in_array($cat->id,explode(',',$widget->category_slider_ids))) selected @endif value="{{$cat->id}}">{{$cat->name}}</option>
                                                                @endforeach
                                                            </select>

                                                            <label>{{__('db.Slider Loop')}}</label>
                                                            <select name="category_slider_loop" class="selectpicker form-control">
                                                                <option @if($widget->category_slider_loop == 'yes') selected @endif value="yes">Yes</option>
                                                                <option @if($widget->category_slider_loop == 'no') selected @endif value="no">No</option>
                                                            </select>

                                                            <div class="form-group mt-2">
                                                                <button class="btn btn-sm btn-primary btn-save"><i class="dripicons-checkmark"></i></button>
                                                                <button class="btn btn-sm btn-danger btn-delete"><i class="dripicons-trash"></i></button>
                                                                <input type="hidden" name="widget_name" value="category-slider-widget">
                                                                <input type="hidden" name="order" value="{{$widget->order}}">
                                                                <input type="hidden" name="id" value="{{$widget->id}}">
                                                            </div>
                                                        </form>
                                                    </div>
                                                </li>
                                                @endif

                                                @if($widget->name == 'product-collection-widget')
                                                <li>
                                                    <a class="toggle-collapse"> {{__('db.product Collection')}} <span class="pull-right"><i class="fa fa-angle-down"></i></span></a>
                                                    <div class="collapse">
                                                        <hr>
                                                        <form>
                                                            <label>{{__('db.Title')}}</label>
                                                            <input type="text" name="product_collection_title" class="form-control" value="{{$widget->product_collection_title}}">

                                                            <label>{{__('db.Choose Collection')}}</label>
                                                            <select name="product_collection_id" class="selectpicker form-control">
                                                                @foreach($collections as $collection)
                                                                <option @if($widget->product_collection_id == $collection->id) selected @endif value="{{$collection->id}}">{{$collection->name}}</option>
                                                                @endforeach
                                                            </select>

                                                            <label>{{__('db.Layout Type')}}</label>
                                                            <select name="product_collection_type" class="selectpicker form-control">
                                                                <option @if($widget->product_collection_type == 'gallery') selected @endif value="gallery">Gallery</option>
                                                                <option @if($widget->product_collection_type == 'slider') selected @endif value="slider">Slider</option>
                                                            </select>

                                                            <label>{{__('db.Slider Loop')}}</label>
                                                            <select name="product_collection_slider_loop" class="selectpicker form-control">
                                                                <option @if($widget->product_collection_slider_loop == 'true') selected @endif value="true">Yes</option>
                                                                <option @if($widget->product_collection_slider_loop == 'false') selected @endif value="false">No</option>
                                                            </select>

                                                            <label>{{__('db.Number of products to show')}}</label>
                                                            <input type="text" name="product_collection_limit" class="form-control" value="{{$widget->product_collection_limit}}">

                                                            <div class="form-group mt-2">
                                                                <button class="btn btn-sm btn-primary btn-save"><i class="dripicons-checkmark"></i></button>
                                                                <button class="btn btn-sm btn-danger btn-delete"><i class="dripicons-trash"></i></button>
                                                                <input type="hidden" name="widget_name" value="product-collection-widget">
                                                                <input type="hidden" name="order" value="{{$widget->order}}">
                                                                <input type="hidden" name="id" value="{{$widget->id}}">
                                                            </div>
                                                        </form>
                                                    </div>
                                                </li>
                                                @endif

                                                @if($widget->name == 'tab-product-collection-widget')
                                                <li>
                                                    <a class="toggle-collapse"> {{__('db.Tabbed Product Collections')}} <span class="pull-right"><i class="fa fa-angle-down"></i></span></a>
                                                    <div class="collapse">
                                                        <hr>
                                                        <form>
                                                            <label>{{__('db.Choose Collections')}}</label>
                                                            <select name="tab_product_collection_ids[]" class="selectpicker form-control" multiple>
                                                                @foreach($collections as $collection)
                                                                <option @if(in_array($collection->id,explode(',',$widget->tab_product_collection_id))) selected @endif value="{{$collection->id}}">{{$collection->name}}</option>
                                                                @endforeach
                                                            </select>

                                                            <label>{{__('db.Layout Type')}}</label>
                                                            <select name="tab_product_collection_type" class="selectpicker form-control">
                                                                <option @if($widget->tab_product_collection_type == 'gallery') selected @endif value="gallery">Gallery</option>
                                                                <!-- <option @if($widget->tab_product_collection_type == 'slider') selected @endif value="slider">Slider</option> -->
                                                            </select>

                                                            <!-- <label>{{__('db.Slider Loop')}}</label>
                                                            <select name="tab_product_collection_slider_loop" class="selectpicker form-control">
                                                                <option @if($widget->tab_product_collection_slider_loop == 'yes') selected @endif value="yes">Yes</option>
                                                                <option @if($widget->tab_product_collection_slider_loop == 'no') selected @endif value="no">No</option>
                                                            </select> -->

                                                            <label>{{__('db.Number of products to show')}}</label>
                                                            <input type="text" name="tab_product_collection_limit" class="form-control" value="{{$widget->tab_product_collection_limit}}">

                                                            <div class="form-group mt-2">
                                                                <button class="btn btn-sm btn-primary btn-save"><i class="dripicons-checkmark"></i></button>
                                                                <button class="btn btn-sm btn-danger btn-delete"><i class="dripicons-trash"></i></button>
                                                                <input type="hidden" name="widget_name" value="tab-product-collection-widget">
                                                                <input type="hidden" name="order" value="{{$widget->order}}">
                                                                <input type="hidden" name="id" value="{{$widget->id}}">
                                                            </div>
                                                        </form>
                                                    </div>
                                                </li>
                                                @endif

                                                @if($widget->name == 'brand-slider-widget')
                                                <li>
                                                    <a class="toggle-collapse"> {{__('db.Brand Slider')}} <span class="pull-right"><i class="fa fa-angle-down"></i></span></a>
                                                    <div class="collapse">
                                                        <hr>
                                                        <form>
                                                            <label>{{__('db.Title')}}</label>
                                                            <input type="text" name="brand_slider_title" class="form-control" value="{{$widget->brand_slider_title}}">

                                                            <label>{{__('db.Choose Brands')}}</label>
                                                            <select name="brand_slider_ids[]" class="selectpicker form-control" multiple>
                                                                @foreach($brands as $brand)
                                                                <option @if(in_array($brand->id,explode(',',$widget->brand_slider_ids))) selected @endif value="{{$brand->id}}">{{$brand->title}}</option>
                                                                @endforeach
                                                            </select>

                                                            <label>{{__('db.Slider Loop')}}</label>
                                                            <select name="brand_slider_loop" class="selectpicker form-control">
                                                                <option @if($widget->brand_slider_loop == 'yes') selected @endif value="yes">Yes</option>
                                                                <option @if($widget->brand_slider_loop == 'no') selected @endif value="no">No</option>
                                                            </select>

                                                            <div class="form-group mt-2">
                                                                <button class="btn btn-sm btn-primary btn-save"><i class="dripicons-checkmark"></i></button>
                                                                <button class="btn btn-sm btn-danger btn-delete"><i class="dripicons-trash"></i></button>
                                                                <input type="hidden" name="widget_name" value="brand-slider-widget">
                                                                <input type="hidden" name="order" value="{{$widget->order}}">
                                                                <input type="hidden" name="id" value="{{$widget->id}}">
                                                            </div>
                                                        </form>
                                                    </div>
                                                </li>
                                                @endif

                                                @if($widget->name == 'image-slider-widget')
                                                <li>
                                                    <a class="toggle-collapse"> {{__('db.Image Slider')}} <span class="pull-right"><i class="fa fa-angle-down"></i></span></a>
                                                    <div class="collapse">
                                                        <hr>
                                                        <form enctype="multipart/form-data">
                                                            @php
                                                                $links = explode(',',$widget->slider_links);
                                                                $images = explode(',',$widget->slider_images);
                                                            @endphp

                                                            @foreach($images as $key=>$image)
                                                            @if(strlen($image)>0)
                                                                <div class="row ">
                                                                    <div class="col-sm-4 mt-2">
                                                                        <input class="form-control" type="hidden" name="slide_lg_prev[]" required value="{{$image}}">
                                                                        <img src="{{url('frontend/images/slider_widget/desktop')}}/{{$image}}" style="width:50px" /> 
                                                                    </div>
                                                                    <div class="col-sm-4 mt-2">
                                                                        @php
                                                                            $path = url('frontend/images/slider_widget/mobile')."/".$image;
                                                                        @endphp
                                                                        @if(file_exists($path))
                                                                        <input class="form-control" type="hidden" name="slide_m_prev[]" value="{{$image}}">
                                                                        <img src="{{url('frontend/images/slider_widget/mobile')}}/{{$image}}" style="width:50px" />
                                                                        @else
                                                                        <input class="form-control" type="hidden" name="slide_m_prev[]" value="">
                                                                        @endif
                                                                    </div>
                                                                    <div class="col-sm-3 mt-2">
                                                                        <input class="form-control" type="text" name="link_prev[]" value="@if(isset($links[$key])){{$links[$key]}}@endif">
                                                                    </div>
                                                                    <div class="col-sm-1 mt-2">
                                                                        <a class="btn btn-danger btn-sm remove"><i class="dripicons-cross"></i></a>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                            @endforeach

                                                            <div class="add-img-slider">
                                                                <div class="row ">
                                                                    <div class="col-sm-4 mt-2">
                                                                        <label>{{ __('db.Image') }} ({{ __('db.Desktop') }})<span class="required">*</span></label><br>
                                                                        <input class="form-control" type="file" name="slide_lg[]" required>
                                                                    </div>
                                                                    <div class="col-sm-4 mt-2">
                                                                        <label>{{ __('db.Image') }} ({{ __('db.Mobile') }})</label><br>
                                                                        <input class="form-control" type="file" name="slide_m[]">
                                                                    </div>
                                                                    <div class="col-sm-3 mt-2">
                                                                        <label>{{ __('db.Link') }}</label><br>
                                                                        <input class="form-control" type="text" name="link[]">
                                                                    </div>
                                                                    <div class="col-sm-1 mt-2">
                                                                        <label>{{ __('Add') }}</label>
                                                                        <a class="btn btn-success btn-sm add-more"><i class="dripicons-plus"></i></a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group mt-2">
                                                                <button class="btn btn-sm btn-primary btn-save"><i class="dripicons-checkmark"></i></button>
                                                                <button class="btn btn-sm btn-danger btn-delete"><i class="dripicons-trash"></i></button>
                                                                <input type="hidden" name="widget_name" value="image-slider-widget">
                                                                <input type="hidden" name="order" value="{{$widget->order}}">
                                                                <input type="hidden" name="id" value="{{$widget->id}}">
                                                            </div>
                                                        </form>
                                                    </div>
                                                </li>
                                                @endif
                                            @endforeach
                                        @endif
                                    </ul>
                                </div>
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
                            <input id="page_id" name="page_id" type="hidden" value="{{$id}}">
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            {{__('db.Templates')}}
                        </div>
                        <div class="card-body">
                            <select id="template" class="selectpicker form-control" name="template">
                                <option value="default">Default</option>
                                <option value="home">Home</option>
                                <option value="contact">Contact</option>
                                <option value="faq">FAQ</option>
                            </select>
                            <div class="badge badge-light mt-3">More templates are coming soon...</div>
                        </div>
                    </div>
                </div>
            </div>

        </form>

        <ul class="widgets d-none">
            <div class="text-widget">
                <li>
                    <a class="toggle-collapse"> {{__('db.Text')}} <span class="pull-right"><i class="fa fa-angle-down"></i></span></a>
                    <div class="collapse">
                        <hr>
                        <form>
                            <label>{{__('db.Title')}}</label>
                            <input type="text" name="text_title" class="form-control">
                            <label>{{__('db.Text')}}</label>
                            <textarea name="text_content" class="form-control"></textarea>
                            <div class="form-group mt-2">
                                <button class="btn btn-sm btn-primary btn-save"><i class="dripicons-checkmark"></i></button>
                                <button class="btn btn-sm btn-danger btn-delete"><i class="dripicons-trash"></i></button>
                                <input type="hidden" name="widget_name" value="text-widget">
                                <input type="hidden" name="order">
                                <input type="hidden" name="id">
                            </div>
                        </form>
                    </div>
                </li>
            </div>

            <div class="three-c-banner-widget">
                <li>
                    <a class="toggle-collapse"> {{__('db.Three Column Banner')}} <span class="pull-right"><i class="fa fa-angle-down"></i></span></a>
                    <div class="collapse">
                        <hr>
                        <form>
                            <div class="row">
                                <div class="col-md-6">
                                    <label>{{__('db.Banner Image')}} 1</label>
                                    <input type="file" name="three_c_banner_image1" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label>{{__('db.Link')}} 1</label>
                                    <input type="text" name="three_c_banner_link1" class="form-control">
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <label>{{__('db.Banner Image')}} 2</label>
                                    <input type="file" name="three_c_banner_image2" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label>{{__('db.Link')}} 2</label>
                                    <input type="text" name="three_c_banner_link2" class="form-control">
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <label>{{__('db.Banner Image')}} 3</label>
                                    <input type="file" name="three_c_banner_image3" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label>{{__('db.Link')}} 3</label>
                                    <input type="text" name="three_c_banner_link3" class="form-control">
                                </div>
                            </div>
                            <div class="form-group mt-2">
                                <button class="btn btn-sm btn-primary btn-save"><i class="dripicons-checkmark"></i></button>
                                <a href="" class="btn btn-sm btn-danger btn-delete"><i class="dripicons-trash"></i></a>
                                <input type="hidden" name="widget_name" value="three-c-banner-widget">
                                <input type="hidden" name="order">
                                <input type="hidden" name="id">
                            </div>
                        </form>
                    </div>
                </li>
            </div>

            <div class="two-c-banner-widget">
                <li>
                    <a class="toggle-collapse"> {{__('db.Two Column Banner')}} <span class="pull-right"><i class="fa fa-angle-down"></i></span></a>
                    <div class="collapse">
                        <hr>
                        <form>
                            <div class="row">
                                <div class="col-md-6">
                                    <label>{{__('db.Banner Image')}} 1</label>
                                    <input type="file" name="two_c_banner_image1" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label>{{__('db.Link')}} 1</label>
                                    <input type="text" name="two_c_banner_link1" class="form-control">
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <label>{{__('db.Banner Image')}} 2</label>
                                    <input type="file" name="two_c_banner_image2" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label>{{__('db.Link')}} 2</label>
                                    <input type="text" name="two_c_banner_link2" class="form-control">
                                </div>
                            </div>
                            <div class="form-group mt-2">
                                <button class="btn btn-sm btn-primary btn-save"><i class="dripicons-checkmark"></i></button>
                                <a href="" class="btn btn-sm btn-danger btn-delete"><i class="dripicons-trash"></i></a>
                                <input type="hidden" name="widget_name" value="two-c-banner-widget">
                                <input type="hidden" name="order">
                                <input type="hidden" name="id">
                            </div>
                        </form>
                    </div>
                </li>
            </div>

            <div class="one-c-banner-widget">
                <li>
                    <a class="toggle-collapse"> {{__('db.One Column Banner')}} <span class="pull-right"><i class="fa fa-angle-down"></i></span></a>
                    <div class="collapse">
                        <hr>
                        <form>
                            <div class="row">
                                <div class="col-md-6">
                                    <label>{{__('db.Banner Image')}}</label>
                                    <input type="file" name="one_c_banner_image1" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label>{{__('db.Link')}}</label>
                                    <input type="text" name="one_c_banner_link1" class="form-control">
                                </div>
                            </div>
                            <div class="form-group mt-2">
                                <button class="btn btn-sm btn-primary btn-save"><i class="dripicons-checkmark"></i></button>
                                <a href="" class="btn btn-sm btn-danger btn-delete"><i class="dripicons-trash"></i></a>
                                <input type="hidden" name="widget_name" value="one-c-banner-widget">
                                <input type="hidden" name="order">
                                <input type="hidden" name="id">
                            </div>
                        </form>
                    </div>
                </li>
            </div>

            <div class="product-category-widget">
                <li>
                    <a class="toggle-collapse"> {{__('db.product Category')}} <span class="pull-right"><i class="fa fa-angle-down"></i></span></a>
                    <div class="collapse">
                        <hr>
                        <form>
                            <label>{{__('db.Title')}}</label>
                            <input type="text" name="product_category_title" class="form-control">

                            <label>{{__('db.Choose Category')}}</label>
                            <select name="product_category_id" class="selectpicker form-control">
                                @foreach($categories as $cat)
                                <option value="{{$cat->id}}">{{$cat->name}}</option>
                                @endforeach
                            </select>

                            <label>{{__('db.Layout Type')}}</label>
                            <select name="product_category_type" class="selectpicker form-control">
                                <option value="gallery">Gallery</option>
                                <option value="slider">Slider</option>
                            </select>

                            <label>{{__('db.Slider Loop')}}</label>
                            <select name="product_category_slider_loop" class="selectpicker form-control">
                                <option value="yes">Yes</option>
                                <option value="false">No</option>
                            </select>

                            <label>{{__('db.Number of products to show')}}</label>
                            <input type="text" name="product_category_limit" class="form-control" value="10">

                            <div class="form-group mt-2">
                                <button class="btn btn-sm btn-primary btn-save"><i class="dripicons-checkmark"></i></button>
                                <button class="btn btn-sm btn-danger btn-delete"><i class="dripicons-trash"></i></button>
                                <input type="hidden" name="widget_name" value="product-category-widget">
                                <input type="hidden" name="order">
                                <input type="hidden" name="id">
                            </div>
                        </form>
                    </div>
                </li>
            </div>

            <div class="category-slider-widget">
                <li>
                    <a class="toggle-collapse"> {{__('db.Category Slider')}} <span class="pull-right"><i class="fa fa-angle-down"></i></span></a>
                    <div class="collapse">
                        <hr>
                        <form>
                            <label>{{__('db.Title')}}</label>
                            <input type="text" name="category_slider_title" class="form-control">

                            <label>{{__('db.Choose Categories')}}</label>
                            <select name="category_slider_ids[]" class="selectpicker form-control" multiple>
                                @foreach($categories as $cat)
                                <option value="{{$cat->id}}">{{$cat->name}}</option>
                                @endforeach
                            </select>

                            <label>{{__('db.Slider Loop')}}</label>
                            <select name="category_slider_loop" class="selectpicker form-control">
                                <option value="yes">Yes</option>
                                <option value="false">No</option>
                            </select>

                            <div class="form-group mt-2">
                                <button class="btn btn-sm btn-primary btn-save"><i class="dripicons-checkmark"></i></button>
                                <button class="btn btn-sm btn-danger btn-delete"><i class="dripicons-trash"></i></button>
                                <input type="hidden" name="widget_name" value="category-slider-widget">
                                <input type="hidden" name="order">
                                <input type="hidden" name="id">
                            </div>
                        </form>
                    </div>
                </li>
            </div>

            <div class="product-collection-widget">
                <li>
                    <a class="toggle-collapse"> {{__('db.product Collection')}} <span class="pull-right"><i class="fa fa-angle-down"></i></span></a>
                    <div class="collapse">
                        <hr>
                        <form>
                            <label>{{__('db.Title')}}</label>
                            <input type="text" name="product_collection_title" class="form-control">

                            <label>{{__('db.Choose Collection')}}</label>
                            <select name="product_collection_id" class="selectpicker form-control">
                                @foreach($collections as $collection)
                                <option value="{{$collection->id}}">{{$collection->name}}</option>
                                @endforeach
                            </select>

                            <label>{{__('db.Layout Type')}}</label>
                            <select name="product_collection_type" class="selectpicker form-control">
                                <option value="gallery">Gallery</option>
                                <option value="slider">Slider</option>
                            </select>

                            <label>{{__('db.Slider Loop')}}</label>
                            <select name="product_collection_slider_loop" class="selectpicker form-control">
                                <option value="true">Yes</option>
                                <option value="false">No</option>
                            </select>

                            <label>{{__('db.Number of products to show')}}</label>
                            <input type="text" name="product_collection_limit" class="form-control" value="10">

                            <div class="form-group mt-2">
                                <button class="btn btn-sm btn-primary btn-save"><i class="dripicons-checkmark"></i></button>
                                <a href="" class="btn btn-sm btn-danger btn-delete"><i class="dripicons-trash"></i></a>
                                <input type="hidden" name="widget_name" value="product-collection-widget">
                                <input type="hidden" name="order">
                                <input type="hidden" name="id">
                            </div>
                        </form>
                    </div>
                </li>
            </div>

            <div class="tab-product-collection-widget">
                <li>
                    <a class="toggle-collapse"> {{__('db.Tabbed Product collections')}} <span class="pull-right"><i class="fa fa-angle-down"></i></span></a>
                    <div class="collapse">
                        <hr>
                        <form>
                            <label>{{__('db.Choose collections')}}</label>
                            <select name="tab_product_collection_ids[]" class="selectpicker form-control" multiple>
                                @foreach($collections as $collection)
                                <option value="{{$collection->id}}">{{$collection->name}}</option>
                                @endforeach
                            </select>

                            <label>{{__('db.Layout Type')}}</label>
                            <select name="tab_product_collection_type" class="selectpicker form-control">
                                <option value="gallery">Gallery</option>
                                <option value="slider">Slider</option>
                            </select> 

                            <label>{{__('db.Slider Loop')}}</label>
                            <select name="tab_product_collection_slider_loop" class="selectpicker form-control">
                                <option value="true">Yes</option>
                                <option value="false">No</option>
                            </select>

                            <label>{{__('db.Number of products to show')}}</label>
                            <input type="text" name="tab_product_collection_limit" class="form-control" value="10">

                            <div class="form-group mt-2">
                                <button class="btn btn-sm btn-primary btn-save"><i class="dripicons-checkmark"></i></button>
                                <a href="" class="btn btn-sm btn-danger btn-delete"><i class="dripicons-trash"></i></a>
                                <input type="hidden" name="widget_name" value="tab-product-collection-widget">
                                <input type="hidden" name="order">
                                <input type="hidden" name="id">
                            </div>
                        </form>
                    </div>
                </li>
            </div>

            <div class="brand-slider-widget">
                <li>
                    <a class="toggle-collapse"> {{__('db.Brand Slider')}} <span class="pull-right"><i class="fa fa-angle-down"></i></span></a>
                    <div class="collapse">
                        <hr>
                        <form>
                            <label>{{__('db.Title')}}</label>
                            <input type="text" name="brand_slider_title" class="form-control">

                            <label>{{__('db.Choose Brands')}}</label>
                            <select name="brand_slider_ids[]" class="selectpicker form-control" multiple>
                                @foreach($brands as $brand)
                                <option value="{{$brand->id}}">{{$brand->title}}</option>
                                @endforeach
                            </select>

                            <label>{{__('db.Slider Loop')}}</label>
                            <select name="brand_slider_loop" class="selectpicker form-control">
                                <option value="yes">Yes</option>
                                <option value="false">No</option>
                            </select>

                            <div class="form-group mt-2">
                                <button class="btn btn-sm btn-primary btn-save"><i class="dripicons-checkmark"></i></button>
                                <button class="btn btn-sm btn-danger btn-delete"><i class="dripicons-trash"></i></button>
                                <input type="hidden" name="widget_name" value="brand-slider-widget">
                                <input type="hidden" name="order">
                                <input type="hidden" name="id">
                            </div>
                        </form>
                    </div>
                </li>
            </div>

            <div class="image-slider-widget">
                <li>
                    <a class="toggle-collapse"> {{__('db.Image Slider')}} <span class="pull-right"><i class="fa fa-angle-down"></i></span></a>
                    <div class="collapse">
                        <hr>
                        <form>
                            <div class="add-img-slider">
                                <div class="row ">
                                    <div class="col-sm-4 mt-2">
                                        <label>{{ __('db.Image') }} ({{ __('db.Desktop') }})<span class="required">*</span></label><br>
                                        <input class="form-control" type="file" name="slide_lg[]" required>
                                    </div>
                                    <div class="col-sm-4 mt-2">
                                        <label>{{ __('db.Image') }} ({{ __('db.Mobile') }})</label><br>
                                        <input class="form-control" type="file" name="slide_m[]">
                                    </div>
                                    <div class="col-sm-3 mt-2">
                                        <label>{{ __('db.Link') }}</label><br>
                                        <input class="form-control" type="text" name="link[]">
                                    </div>
                                    <div class="col-sm-1 mt-2">
                                        <label>{{ __('Add') }}</label>
                                        <a class="btn btn-success btn-sm add-more"><i class="dripicons-plus"></i></a>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mt-2">
                                <button class="btn btn-sm btn-primary btn-save"><i class="dripicons-checkmark"></i></button>
                                <button class="btn btn-sm btn-danger btn-delete"><i class="dripicons-trash"></i></button>
                                <input type="hidden" name="widget_name" value="image-slider-widget">
                                <input type="hidden" name="order">
                                <input type="hidden" name="id">
                            </div>
                        </form>
                    </div>
                </li>
            </div>
        </ul>
    </div>
</section>

@endsection

@push('scripts')
<script>
{!! file_get_contents(Module::find('Ecommerce')->getPath(). "/assets/js/sortable2.js") !!}
</script>
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
            url: "{{url('pages/edit')}}/{{$id}}",
            success: function(data) {  
                $('input[name="page_name"]').val(data.page_name);
                $('input[name="slug"]').val(data.slug);
                if(data.description){
                    tinymce.get('description').setContent(data.description);
                }
                $('input[name="meta_title"]').val(data.meta_title);
                $('input[name="meta_description"]').val(data.meta_description);
                $('input[name="og_title"]').val(data.og_title);
                $('input[name="og_description"]').val(data.og_description);
                $('select[name=template]').val(data.template);
                $('select[name=template]').selectpicker('refresh');
                updateDisplay(data.template);
            }
        })

        text_editor('textarea');
    })

    var container = document.getElementById('layout');

    new Sortable(container, {
        group: {
            name: 'shared',
            pull: 'clone'
        },
        animation: 100,
        onUpdate: function(e) {
            var itemEl = e.item;
            widgetOrder(e.item.parentNode.id);
        },
        onAdd: function(e) {

        }
    });

    function widgetOrder(parent){
        var itemArray = [];
        $('#' + parent + ' li').each(function(index) {
            var order = $(this).index();
            $(this).find('input[name="order"]').val(order);
        })
    }

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
    
    function widgetUpdate() {
        
        $('#layout li').each(function(){
            var item = $(this);

            var widget = item.find('input[name=widget_name]').val();

            if(widget == 'three-c-banner-widget') {
                var page_id = $('#page_id').val();
                var data = item.find('form');

                var form = data[0];
                var formData = new FormData(form);
                formData.append('page_id', page_id);

                $.ajax({
                    type: "post",
                    data: formData,
                    url: "{{url('pages/widget/update')}}/",
                    contentType: false,
                    processData: false,
                    success: function(data) {
                        var message = '<div class="ajax-message"><div class="alert alert-success alert-dismissible text-center mar-bot-30"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{trans("file.Widget saved")}}</div></div>';
                        $('body section').append(message);
                        $("div.alert").delay(3000).slideUp(800);
                    }
                })
            } else if(widget == 'two-c-banner-widget') {
                var page_id = $('#page_id').val();
                var data = item.find('form');

                var form = data[0];
                var formData = new FormData(form);
                formData.append('page_id', page_id);

                $.ajax({
                    type: "post",
                    data: formData,
                    url: "{{url('pages/widget/update')}}/",
                    contentType: false,
                    processData: false,
                    success: function(data) {
                        var message = '<div class="ajax-message"><div class="alert alert-success alert-dismissible text-center mar-bot-30"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{trans("file.Widget saved")}}</div></div>';
                        $('body section').append(message);
                        $("div.alert").delay(3000).slideUp(800);
                    }
                })
            } else if(widget == 'one-c-banner-widget') {
                var page_id = $('#page_id').val();
                var data = item.find('form');

                var form = data[0];
                var formData = new FormData(form);
                formData.append('page_id', page_id);

                $.ajax({
                    type: "post",
                    data: formData,
                    url: "{{url('pages/widget/update')}}/",
                    contentType: false,
                    processData: false,
                    success: function(data) {
                        var message = '<div class="ajax-message"><div class="alert alert-success alert-dismissible text-center mar-bot-30"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{trans("file.Widget saved")}}</div></div>';
                        $('body section').append(message);
                        $("div.alert").delay(3000).slideUp(800);
                    }
                })
            } else if(widget == 'image-slider-widget') {
                var page_id = $('#page_id').val();
                var data = item.find('form');

                var form = data[0];
                var formData = new FormData(form);
                formData.append('page_id', page_id);

                $.ajax({
                    type: "post",
                    data: formData,
                    url: "{{url('pages/widget/update')}}/",
                    contentType: false,
                    processData: false,
                    success: function(data) {
                        var message = '<div class="ajax-message"><div class="alert alert-success alert-dismissible text-center mar-bot-30"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{trans("file.Widget saved")}}</div></div>';
                        $('body section').append(message);
                        $("div.alert").delay(3000).slideUp(800);
                    }
                })
            } else {

                var data = item.find('input,select,textarea').serializeArray();
                var page_id = $('#page_id').val();

                data.push({name: "page_id", value: page_id});

                $.ajax({
                    type: "post",
                    data: $.param(data),
                    url: "{{url('pages/widget/update')}}/",
                    success: function(data) {
                        var message = '<div class="ajax-message"><div class="alert alert-success alert-dismissible text-center mar-bot-30"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{trans("file.Widget saved")}}</div></div>';
                        $('body section').append(message);
                        $("div.alert").delay(3000).slideUp(800);
                    }
                })
            }
        })
    }

    function widgetAdd() {
        console.log('here');
        $('#layout li.new').each(function(){
            var item = $(this);
            var order = item.index();
            item.find('input[name="order"]').val(order);
            var data = item.find('input,select,textarea').serializeArray();
            var page_id = $('#page_id').val();

            data.push({name: "page_id", value: page_id});

            $.ajax({
                type: "post",
                data: $.param(data),
                url: "{{url('pages/widget/store')}}/",
                success: function(data) {
                    item.removeClass('new');
                    item.find('input[name="id"]').val(data);
                    var message = '<div class="ajax-message"><div class="alert alert-success alert-dismissible text-center mar-bot-30"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{trans("file.Widget saved")}}</div></div>';
                    $('body section').append(message);
                    $("div.alert").delay(3000).slideUp(800);
                }
            })
        })
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
            url: "{{route('page.update')}}",
            success: function(data) {
                
                var message = '<div class="ajax-message"><div class="alert alert-success alert-dismissible text-center mar-bot-30"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{trans("file.Page saved")}}</div></div>';
                $('body section').append(message);
                $("div.alert").delay(3000).slideUp(800);
                setTimeout(function () {
                    window.location.href = "{{route('page.index')}}"; 
                }, 3000);
            }
        });
    }

    function updateDisplay(temp) {
        if (temp === 'home') {
            $('#text-block').css('display', 'none');
            $('#text-block textarea').attr('name', 'description').attr('id', 'description');
            $('#page-template').css('display', 'block');
        } else if (temp === 'contact') {
            $('#text-block').css('display', 'block');
            $('#page-template').css('display', 'none');
        } else if (temp === 'faq') {
            $('#text-block').css('display', 'block');
            $('#page-template').css('display', 'none');
        } else {
            $('#text-block').css('display', 'block');
            $('#text-block textarea').attr('name', 'description').attr('id', 'description');
            $('#page-template').css('display', 'none');
            text_editor('#description');
        }
    }

    $(document).on('click', '.btn-save', function(e) {
        e.preventDefault();
        $(this).closest('li').find('.collapse').toggleClass('show');
    })

    $(document).on('click', '.btn-delete', function(e) {
        e.preventDefault();
        $(this).closest('li').remove();
        var id = $(this).siblings('input[name="id"]').val();
        $.ajax({
            type: "get",
            url: "{{url('pages/widget/delete')}}/" + id,
            success: function(data) {
            }
        }) 
    })

    $(document).on('click', '#page-template .toggle-collapse', function() {
        $(this).parent().find('.collapse').toggleClass('show');
    })

    $('input[name="page_name"]').on('input', function() {
        var slug = $(this).val().toLowerCase().replace(/[^\w ]+/g, '').replace(/ +/g, '-');
        $('input[name="slug"]').val(slug);
    });

    $('input[name="page_name"], input[name="slug"]').focusout(function(){
        var slug = $('input[name="slug"]').val();
        $.ajax({
            type: "get",
            url: "{{url('pages/edit')}}/{{$id}}/" + slug,
            success: function(data) {
                console.log(data);
                $('input[name="slug"]').val(data);
            }
        })    
        
        if($('input[name="page_name"]').val().length > 1){
            $('#draft').prop('disabled',false);
            $('#publish').prop('disabled',false);
        }else{
            $('#draft').prop('disabled',true);
            $('#publish').prop('disabled',true);
        }
    })

    $(document).on('click', '#widget-list li a', function() {
        var widget = $(this).attr('data-class');

        $('#layout').append($('div.' + widget).html());
        $('#layout li:last-child').addClass('new')
        widgetAdd();

        $('.selectpicker').selectpicker('refresh');
        if($('#layout li .bootstrap-select .dropdown-toggle').length > 1){
            $('#layout li form > .bootstrap-select > .dropdown-toggle').css('display','none');
        }

        if (widget === 'text-widget') {
            var index = Math.floor(1000 + Math.random() * 9000);
            $('#layout li textarea').last().attr('id', 'text_content' + index);
            text_editor('#text_content' + index);
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
        var temp = $('#template').val();
        if (temp === 'home'){
            widgetUpdate();
        }
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
        var temp = $('#template').val();
        if (temp === 'home'){
            widgetUpdate();
        }
        pageUpdate();
        @endif
    })

    $(document).ready(function() {
        var temp = $('#template').val();
        updateDisplay(temp);
    });

    $('#template').change(function() {
        var temp = $(this).val();
        updateDisplay(temp);
    });

    text_editor('#description');

    $(document).on('click', '.add-more', function(){
        $('.add-img-slider').append('<div class="row"><div class="col-sm-4 mt-2"><label>{{ __('db.Image') }} ({{ __('db.Desktop') }})<span class="required">*</span></label><br><input class="form-control" type="file" name="slide_lg[]" required></div><div class="col-sm-4 mt-2"><label>{{ __('db.Image') }} ({{ __('db.Mobile') }})</label><br><input class="form-control" type="file" name="slide_m[]"></div><div class="col-sm-3 mt-2"><label>{{ __('db.Link') }}</label><br><input class="form-control" type="text" name="link[]"></div><div class="col-sm-1 mt-2"><label>{{ __('Remove') }}</label><a class="btn btn-danger btn-sm remove"><i class="dripicons-cross"></i></a></div></div>');
    })

    $(document).on('click', '.remove', function(){
        $(this).closest('.row').remove();
    })
    
</script>
@endpush