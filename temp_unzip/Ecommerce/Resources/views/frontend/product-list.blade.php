
@if(count($products) == 0)

                        <h3 class="text-center mt-5 mb-5 d-block w-100">Sorry, no products found</h3>
@else
    @foreach($products as $product)

        @include('ecommerce::frontend.includes.product-template')

    @endforeach
@endif