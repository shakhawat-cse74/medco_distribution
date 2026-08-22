<?php

namespace Modules\Ecommerce\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use DB;
use Cache;
use Auth;

class Ecommerce
{
    public function handle(Request $request, Closure $next)
    {
        $general_setting =  Cache::remember('general_setting', 60*60*24*365, function () {
            return DB::table('general_settings')->select('site_logo','expiry_date','developed_by', 'modules', 'currency_position', 'decimal')->latest()->first();
        });

        if(in_array('ecommerce',explode(',',$general_setting->modules))) {
            if(auth()->user() && auth()->user()->role_id == 5){
                $customer = DB::table('customers')->select('id','user_id','wishlist')->where('user_id', Auth::id())->first();
                if(isset($customer->wishlist)){
                    $wishlist = $customer->wishlist;
                    $wishlist_count = (count(explode(',',$customer->wishlist)) - 1);
                }else {
                    $wishlist = 0;
                    $wishlist_count = 0;
                }

            }else{
                $wishlist = 0;
                $wishlist_count = 0;
            }

            View::share('wishlist', $wishlist);
            View::share('wishlist_count', $wishlist_count);

            View::share('general_setting', $general_setting);

            $ecommerce_setting =  Cache::remember('ecommerce_setting', 60*60*24*365, function () {
                return DB::table('ecommerce_settings')->latest()->first();
            });

            View::share('ecommerce_setting', $ecommerce_setting);

            $social_links =  Cache::remember('social_links', 60*60*24*365, function () {
                return DB::table('social_links')->orderBy('order','ASC')->get();
            });

            View::share('social_links', $social_links);


            //setting language
            if(isset($_COOKIE['language'])) {
                \App::setLocale($_COOKIE['language']);
            }
            else {
                \App::setLocale('en');
            }

            $currency = Cache::remember('currency', 60*60*24*365, function () {
                if (session()->get('currency_code')) {
                    return \App\Models\Currency::where('code',session()->get('currency_code'))->first();
                }else {
                    $settingData = DB::table('general_settings')->select('currency')->latest()->first();
                    return \App\Models\Currency::find($settingData->currency);
                }
            });


            View::share('currency', $currency);


            $categories_list = Cache::remember('category_list', 60*60*24*365, function () {
                return DB::table('categories')->where('is_active', true)->get();
            });
            View::share('categories_list', $categories_list);

            $topNav = DB::table('menus')->where('location', 1)->first();
            $menu_items = DB::table('menu_items')->get();
            if($topNav && isset($topNav->content)){
                $topNavItems = json_decode($topNav->content);
                $topNavItems = $topNavItems[0];
                foreach ($topNavItems as $menu) {
                    $menu_item = $menu_items->where('id',$menu->id)->first();
                    $menu->title = $menu_item->title;
                    $menu->name = $menu_item->name;
                    $menu->slug = $menu_item->slug;
                    $menu->target = $menu_item->target;
                    $menu->type = $menu_item->type;
                    if (!empty($menu->children[0])) {
                        foreach ($menu->children[0] as $child) {
                            $child_item = $menu_items->where('id',$child->id)->first();
                            $child->title = $child_item->title;
                            $child->name = $child_item->name;
                            $child->slug = $child_item->slug;
                            $child->target = $child_item->target;
                            $child->type = $child_item->type;
                        }
                    }
                }
            }else{
                $topNavItems = '';
            }

            $widgets = DB::table('widgets')->where('location', 'footer_top')->orWhere('location', 'footer')->orderBy('order','ASC')->get();

            $footer_top_widgets = $widgets->where('location', 'footer_top');

            $footer_widgets = $widgets->where('location', 'footer');

            view()->share([
                'topNavItems' => $topNavItems,
                'menu_items' => $menu_items,
                'footer_widgets' => $footer_widgets,
                'footer_top_widgets' => $footer_top_widgets
            ]);

            return $next($request);
        }
        else {
            return redirect('dashboard');
        }
    }
}
