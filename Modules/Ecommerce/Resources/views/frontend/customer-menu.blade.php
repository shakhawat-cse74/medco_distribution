<ul class="">
    <li>
        <a class="nav-link {{ (request()->is('profile')) ? 'active' : '' }}" href="{{ url('/customer/profile') }}">{{__('db.My Profile')}}</a>
    </li>
    <li>
        <a class="nav-link {{ (request()->is('orders')) ? 'active' : '' }}" href="{{ url('/customer/orders') }}">{{__('db.My Orders')}}</a>
    </li>
    <li>
        <a class="nav-link {{ (request()->is('wishlist')) ? 'active' : '' }}" href="{{ url('/customer/wishlist') }}">{{__('db.My Wishlist')}}</a>
    </li>
    <li>
        <a class="nav-link {{ (request()->is('address')) ? 'active' : '' }}" href="{{ url('/customer/address') }}">{{__('db.My Addresses')}}</a>
    </li>
    <li>
        <a class="nav-link {{ (request()->is('account-details')) ? 'active' : '' }}" href="{{ url('/customer/account-details') }}">{{__('db.Account Details')}}</a>
    </li>
    <li>
        <a class="nav-link" href="{{ url('/customer/logout') }}">{{__('db.logout')}}</a>
    </li>
</ul>