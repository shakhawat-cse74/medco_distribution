@php
    $parent_categories = isset($categories_list) ? $categories_list->whereNull('parent_id')->where('is_active', 1) : DB::table('categories')->whereNull('parent_id')->where('is_active', 1)->get();
@endphp
<style>
/* Tab System Styles */
.home-tab-system {
    background: #fff;
    border: 1px solid #eee;
    border-radius: 8px;
    height: 100%;
    display: flex;
    flex-direction: column;
}
.home-mega-tabs {
    display: flex;
    border-bottom: 1px solid #eee;
    margin: 0;
    padding: 0;
    list-style: none;
    flex-wrap: wrap;
}
.home-mega-tabs li {
    flex: 1;
    min-width: 120px;
}
.home-mega-tab-btn {
    width: 100%;
    text-align: center;
    padding: 15px 10px;
    border: none;
    background: #fbfbfb;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    color: #444;
    transition: all 0.2s;
    outline: none;
}
.home-mega-tab-btn.active, .home-mega-tab-btn:hover {
    background: #fff;
    color: #578B45;
    border-bottom: 3px solid #578B45;
}
.home-mega-tab-content-container {
    padding: 20px;
    flex-grow: 1;
}
.home-mega-tab-content { display: none; }
.home-mega-tab-content.active { display: block; }
.home-sub-cat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 20px;
}
.home-sub-cat-item {
    text-align: center;
    color: #333;
    text-decoration: none;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #eee;
    transition: all 0.2s;
    background: #fff;
}
.home-sub-cat-item:hover {
    color: #578B45;
    background: #f4faf4;
    border: 1px solid #d3e8d3;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.05);
}
.home-sub-cat-item img {
    width: 80px;
    height: 80px;
    object-fit: contain;
    margin-bottom: 15px;
}
.home-sub-cat-item span {
    font-size: 14px;
    font-weight: 600;
    line-height: 1.3;
}
/* Mobile: 2 subcategories per row */
@media (max-width: 767px) {
    .home-sub-cat-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }
    .home-sub-cat-item {
        padding: 12px 8px;
    }
    .home-sub-cat-item img {
        width: 60px;
        height: 60px;
        margin-bottom: 10px;
    }
    .home-sub-cat-item span {
        font-size: 13px;
    }
    .home-mega-tab-content-container {
        padding: 12px;
    }
}
</style>

<section class="category-tab-section mb-3">
    <div class="container-fluid" style="overflow-x:hidden">
        <div class="section-title mb-3">
            <div class="d-flex align-items-center">
                <h3>{{$widget->category_slider_title}}</h3>
            </div>
        </div>

        <div class="home-tab-system">
            <ul class="home-mega-tabs">
                @php $first_parent = true; @endphp
                @foreach($parent_categories as $parent)
                    <li>
                        <button class="home-mega-tab-btn {{ $first_parent ? 'active' : '' }}" onclick="openCategoryTab(event, 'category-tab-{{ $parent->id }}')">{{ $parent->name }}</button>
                    </li>
                    @php $first_parent = false; @endphp
                @endforeach
            </ul>
            <div class="home-mega-tab-content-container">
                @php $first_parent_content = true; @endphp
                @foreach($parent_categories as $parent)
                    <div id="category-tab-{{ $parent->id }}" class="home-mega-tab-content {{ $first_parent_content ? 'active' : '' }}">
                        <div class="home-sub-cat-grid">
                            @php
                                $sub_categories = isset($categories_list) ? $categories_list->where('parent_id', $parent->id)->where('is_active', 1) : DB::table('categories')->where('parent_id', $parent->id)->where('is_active', 1)->get();
                            @endphp
                            @if(count($sub_categories) > 0)
                                @foreach($sub_categories as $sub)
                                    <a href="{{ url('shop') }}/{{ $sub->slug }}" class="home-sub-cat-item">
                                        @if(isset($ecommerce_setting->theme) && $ecommerce_setting->theme == 'fashion')
                                            @if($sub->image!==null)
                                                <img loading="lazy" class="category-img" data-src="{{ url('images/category/large') }}/{{ $sub->image }}" alt="{{ $sub->name }}">
                                            @else
                                                <img loading="lazy" src="https://dummyimage.com/100x100/e5e8ec/e5e8ec&text={{ $sub->name }}" alt="{{ $sub->name }}">
                                            @endif
                                        @else
                                            @if(isset($sub->icon) && $sub->icon!==null)
                                                <img loading="lazy" class="category-img" data-src="{{ url('images/category/icons/') }}/{{ $sub->icon }}" alt="{{ $sub->name }}">
                                            @elseif(isset($sub->image) && $sub->image!==null)
                                                <img loading="lazy" class="category-img" data-src="{{ url('images/category/large') }}/{{ $sub->image }}" alt="{{ $sub->name }}">
                                            @else
                                                <img loading="lazy" src="https://dummyimage.com/100x100/e5e8ec/e5e8ec&text={{ $sub->name }}" alt="{{ $sub->name }}">
                                            @endif
                                        @endif
                                        <span>{{ ucwords($sub->name) }}</span>
                                    </a>
                                @endforeach
                            @else
                                <p style="text-align: center; width: 100%; color: #888; grid-column: 1 / -1;">No sub-categories available</p>
                            @endif
                        </div>
                    </div>
                    @php $first_parent_content = false; @endphp
                @endforeach
            </div>
        </div>
    </div>
</section>

<script>
    function openCategoryTab(evt, tabName) {
        evt.preventDefault();
        var tabcontent = document.getElementsByClassName("home-mega-tab-content");
        for (var i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
            tabcontent[i].classList.remove("active");
        }
        var tablinks = document.getElementsByClassName("home-mega-tab-btn");
        for (var i = 0; i < tablinks.length; i++) {
            tablinks[i].classList.remove("active");
        }
        document.getElementById(tabName).style.display = "block";
        document.getElementById(tabName).classList.add("active");
        evt.currentTarget.classList.add("active");
    }
</script>