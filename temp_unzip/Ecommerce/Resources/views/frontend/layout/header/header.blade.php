<header>
        <div id="header-middle" class="header-middle">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-baseline">
                    <div class="category__menu show-on-mobile"><i class="material-symbols-outlined">menu</i></div>
                    <div class="logo">
                        <a href="{{url('/')}}">
                            @if(!config('database.connections.saleprosaas_landlord'))
                                @if(isset($ecommerce_setting->logo))
                                <img src="{{ url('frontend/images/') }}/{{$ecommerce_setting->logo}}" alt="{{$ecommerce_setting->site_title ?? ''}}">
                                @else
                                <img src="{{ asset('logo') }}/{{$general_setting->site_logo}}" alt="{{$ecommerce_setting->site_title ?? ''}}">
                                @endif
                            @else
                                @if(isset($ecommerce_setting->logo))
                                <img src="{{ asset('../../frontend/images/') }}/{{$ecommerce_setting->logo}}" alt="{{$ecommerce_setting->site_title ?? ''}}">
                                @else
                                <img src="{{ asset('../../logo') }}/{{$general_setting->site_logo}}" alt="{{$ecommerce_setting->site_title ?? ''}}">
                                @endif
                            @endif
                        </a>
                    </div>
                    <form action="{{route('products.search')}}" method="post" class="header-search" style="max-width:550px">
                        @csrf
                        <div class="header-search-container">
                            <input id="search" type="text" placeholder="Search products" name="search">
                            <div class="search_result"></div>
                        </div>
                        <button class="btn btn-search" type="submit" style="margin-top:-2px"><span class="d-flex"><i class="material-symbols-outlined">search</i></span></button>
                    </form>
                    <ul class="offset-menu-wrapper">
                        <!-- <li class="language"><a  class="active" href="">En</a> / <a href="">Bn</a></li> -->
                        @if(isset($ecommerce_setting->online_order) && $ecommerce_setting->online_order == 1)
                        @php
                            if (session()->get('currency_code')) {
                                $selectedCurrency = App\Models\Currency::where('code',session()->get('currency_code'))->first();
                            }else {
                                $selectedCurrency = App\Models\Currency::where('is_active',1)->first();
                            }
                             // Static selection
                            $currencies = App\Models\Currency::where('is_active',1)->get();
                        @endphp
                        <li class="nav-item dropdown currency__menu">
                            <a href="#" class="nav-link dropdown-toggle p-0" id="currencyDropdown" role="button" data-toggle="dropdown" title="{{ __('db.Change Currency') }}">
                                <span class="ms-1 current-currency" id="current-currency" data-currency_code="{{ $selectedCurrency->code }}" data-currency_rate="{{ $selectedCurrency->exchange_rate }}">{{ $selectedCurrency->code }}</span>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="currencyDropdown">
                                @foreach ($currencies as $currency)
                                    <a
                                        class="dropdown-item change-currency"
                                        href="#"
                                        data-code="{{ $currency->code }}"
                                    >
                                        {{ $currency->code }}
                                    </a>
                                @endforeach
                            </div>
                        </li>

                        @guest
                        <li>
                            <a href="{{url('customer/login')}}">Login</a>
                        </li>
                        @endguest
                        @if(auth()->user() && auth()->user()->role_id == 5)
                        <li class="user-menu">
                            <i class="material-symbols-outlined">person_add</i>
                            <ul class="user-dropdown-menu">
                                <li><a href="{{url('customer/account-details')}}">My Account</a></li>
                                <li><a href="{{url('customer/orders')}}">Order History</a></li>
                                <li><a href="{{url('customer/address')}}">Addresses</a></li>
                                <li><a href="{{url('customer/logout')}}"> {{__('db.logout')}}</a></li>
                            </ul>
                        </li>
                        @endif

                        <li>
                            <a href="{{url('track-order')}}" title="{{__('db.Track Order')}}"><i class="material-symbols-outlined">pin_drop</i></a>
                        </li>
                        <li class="wishlist__menu">
                            <a href="{{url('customer/wishlist')}}" title="{{__('db.Wishlist')}}"><i class="material-symbols-outlined" title="{{__('db.My Wishlist')}}">favorite</i></a>
                            <span class="badge badge-light cart_qty">
                            {{ $wishlist_count }}
                            </span>
                        </li>
                        @php

                        $total_qty = session()->has('total_qty') ? session()->get('total_qty') : 0;
                        $subTotal = session()->has('subTotal') ? session()->get('subTotal') : 0;

                        if($total_qty == 0){
                            $subTotal = 0;
                        }

                        @endphp
                        <li class="cart__menu">
                            <i class="material-symbols-outlined" title="{{__('db.Cart')}}">shopping_bag</i>
                            <span class="badge badge-light cart_qty">{{ $total_qty ?? 0}}</span>
                            <span class="total">
                                @if($general_setting->currency_position == 'prefix')
                                {{$selectedCurrency->symbol ?? $selectedCurrency->code }} {{ $subTotal * $selectedCurrency->exchange_rate ?? 0.00}}
                                @else
                                {{ $subTotal * $selectedCurrency->exchange_rate ?? 0.00}}{{$selectedCurrency->symbol ?? $selectedCurrency->code}}
                                @endif
                            </span>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
        <div class="header-bottom">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-3 d-none d-lg-flex d-xl-flex">
                        <div class="category-list">
                            <ul>
                                <li class="has-dropdown">
                                    <a class="category-button" href="#"><i class="material-symbols-outlined">menu</i> Categories</a>
                                    <ul class="dropdown sidebar {{ (request()->is('/')) ? 'show' : '' }}">
                                        @php $i = 0 @endphp
                                        @foreach($all_categories as $category)
                                            @if(in_array($category->id, $parents))
                                            @php
                                            $categories = $all_categories->where('parent_id', $category->id)->where('is_active', 1);
                                            @endphp
                                            @if(count($categories) > 0)
                                            <li class="has-dropdown"><a href="{{ url('shop') }}/{{ $category->slug }}">@if(isset($category->icon))<img src="{{ url('images/category/icons/') }}/{{ $category->icon }}" alt="{{ $category->name }}">@endif <span>{{ $category->name }}</span></a>
                                                <ul class="dropdown">
                                                    @foreach($categories as $cat)
                                                    <li><a href="{{ url('shop') }}/{{ $cat->slug }}">@if(isset($cat->icon))<img src="{{ url('images/category/icons/') }}/{{ $cat->icon }}" alt="{{ $cat->name }}">@endif <span>{{ $cat->name }}</span></a></li>
                                                    @endforeach
                                                </ul>
                                            </li>
                                            @else
                                            <li><a href="{{ url('shop') }}/{{ $category->slug }}">@if(isset($category->icon))<img src="{{ url('images/category/icons/') }}/{{ $category->icon }}" alt="{{ $category->name }}">@endif <span>{{ $category->name }}</span></a>
                                            </li>
                                            @endif
                                            @endif
                                            @php $i++ @endphp
                                        @endforeach
                                        <li><a class="button style3 text-center" href="{{ url('shop') }}/"> All Categories</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="main-header-inner">
                            <div id="main-menu" class="main-menu">
                                <nav id="mobile-nav" class="d-flex justify-content-between">
                                    <ul>
                                        @if(!empty($topNavItems))
                                            @foreach($topNavItems as $nav)
                                                @if(!empty($nav->children[0]))
                                                <li class="user-menu">
                                                    <a href="#">@if($nav->name == NULL) {{$nav->title}} @else {{$nav->name}} @endif <i class="caret"></i>
                                                        <ul class="user-dropdown-menu">
                                                            @foreach($nav->children[0] as $childNav)
                                                            @if($childNav->type == 'custom')
                                                            <li><a href="{{$childNav->slug}}" target="_blank">@if($childNav->name == NULL) {{$childNav->title}} @else {{$childNav->name}} @endif</a></li>
                                                            @elseif($childNav->type == 'category')
                                                            <li><a href="{{url('shop')}}/{{$childNav->slug}}">@if($childNav->name == NULL) {{$childNav->title}} @else {{$childNav->name}} @endif</a></li>
                                                            @else
                                                            <li><a href="{{url('')}}/{{$childNav->slug}}">@if($childNav->name == NULL) {{$childNav->title}} @else {{$childNav->name}} @endif</a></li>
                                                            @endif
                                                            @endforeach
                                                        </ul>
                                                    </a>
                                                </li>
                                                @else
                                                    @if($nav->type == 'custom')
                                                    <li><a href="{{$nav->slug}}" target="_blank">@if($nav->name == NULL) {{$nav->title}} @else {{$nav->name}} @endif</a></li>
                                                    @elseif($nav->type == 'category')
                                                    <li><a href="{{url('shop')}}/{{$nav->slug}}">@if($nav->name == NULL) {{$nav->title}} @else {{$nav->name}} @endif</a></li>
                                                    @elseif($nav->type == 'page' && ($nav->slug == 'home'))
                                                    <li><a href="{{url('/')}}">@if($nav->name == NULL) {{$nav->title}} @else {{$nav->name}} @endif</a></li>
                                                    @elseif($nav->type == 'collection')
                                                    <li><a href="{{url('products')}}/{{$nav->slug}}">@if($nav->name == NULL) {{$nav->title}} @else {{$nav->name}} @endif</a></li>
                                                    @elseif($nav->type == 'brand')
                                                    <li><a href="{{url('brand')}}/{{$nav->slug}}">@if($nav->name == NULL) {{$nav->title}} @else {{$nav->name}} @endif</a></li>
                                                    @else
                                                    <li><a href="{{url('')}}/{{$nav->slug}}">@if($nav->name == NULL) {{$nav->title}} @else {{$nav->name}} @endif</a></li>
                                                    @endif
                                                @endif
                                            @endforeach
                                        @endif
                                    </ul>
                                    @if(isset($social_links))
                                    <ul class="social-links">
                                        @foreach ($social_links as $link)
                                        <li><a href="{{$link->link}}">{!!$link->icon!!}</a></li>
                                        @endforeach
                                    </ul>
                                    @endif
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

