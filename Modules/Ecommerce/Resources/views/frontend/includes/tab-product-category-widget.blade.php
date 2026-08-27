    @php
        $sub_cat_ids = DB::table('categories')->where('parent_id', $widget->product_category_id)->where('is_active', true)->pluck('id')->toArray();
        $cat_ids = array_merge([$widget->product_category_id], $sub_cat_ids);
        $products = DB::table('products')->where('is_active', true)->where('is_online', true)->whereIn('category_id', $cat_ids)->get();
    @endphp
    <!--Product area starts-->
    <section class="product-tab-section">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 text-center">
                    <div class="section-title">
                        <h3>{{$widget->tab_product_category_title}}</h3>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="product-grid">
                	@foreach($products as $product)
                    @include('ecommerce::frontend.includes.product-template')
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    <!--product area ends-->
