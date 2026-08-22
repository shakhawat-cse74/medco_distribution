<li><a class="{{ request()->is('restaurant/floor') ? 'active' : '' }}" href="{{route('restaurant.floor.index')}}">{{__('db.Floors')}}</a></li>
<li id="table-menu"><a class="{{ request()->is('tables') ? 'active' : '' }}" href="{{route('tables.index')}}">{{__('db.Tables')}}</a></li>
<li><a class="{{ request()->is('restaurant/reservation') ? 'active' : '' }}" href="{{route('restaurant.reservation.index')}}">{{__('db.Reservation')}}</a></li>
<li><a class="{{ request()->is('restaurant/menutype') ? 'active' : '' }}" href="{{route('restaurant.menutype.index')}}">{{__('db.Menu Type')}}</a></li>
<li><a class="{{ request()->is('restaurant/modifier-group') ? 'active' : '' }}" href="{{route('restaurant.modifier-group.index')}}">{{__('db.Modifier Group')}}</a></li>
<li><a class="{{ request()->is('restaurant/kitchen') ? 'active' : '' }}" href="{{route('restaurant.kitchen.index')}}">{{__('db.Kitchen')}}</a></li>
<li><a class="{{ request()->is('restaurant/kitchen/dashboard') ? 'active' : '' }}" href="{{route('restaurant.kitchen.dashboard')}}">{{__('db.kitchen Dashboard')}}</a></li>
<li><a href="{{url('pos')}}?restaurant">{{__('db.Restaurant POS')}}</a></li>
