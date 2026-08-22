<?php

namespace Modules\Ecommerce\Http\Controllers;

use Modules\Ecommerce\Entities\Menus;
use Modules\Ecommerce\Entities\MenuItems;
use App\Models\Category;
use Modules\Ecommerce\Entities\Page;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Str;
use Session;
use DB; 

class MenuController extends Controller
{

    public function index()
    {
        $menus = Menus::all();
        return view('ecommerce::backend.menu.index', compact('menus'));
    }

    public function store(Request $request)
    {
        if(!env('USER_VERIFIED')){
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');
        }
        $data = $request->all();

        if (Menus::create($data)) {
            $newdata = Menus::orderby('id', 'DESC')->first();
            Session::flash('message', 'Menu saved successfully.');
            Session::flash('type', 'success');
            return redirect()->back();
        } else {
            Session::flash('message', 'Failed to save menu.');
            Session::flash('type', 'danger');
            return redirect()->back();
        }
    }

    public function edit($id)
    {
        $menu = Menus::find($id);
        return $menu;
    }

    public function updateMenu(Request $request)
    {
        if(!env('USER_VERIFIED')){
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');
        }
        $newdata = $request->all();
        $menu = Menus::findOrFail($request->menuid);
        $newdata = [];
        if(isset($request->data)){
            $newdata['content'] = json_encode($request->data);
        }
        if(isset($request->title)){
            $newdata['title'] = $request->title;
            $newdata['location'] = $request->location;
        }

        $menu->update($newdata);

        Session::flash('message', 'Menu saved successfully.');
        Session::flash('type', 'success');
        return redirect()->back();
    }

    public function destroy(Request $request)
    {
        if(!env('USER_VERIFIED')){
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');
        }

        MenuItems::where('menu_id', $request->id)->delete();
        Menus::findOrFail($request->id)->delete();

        Session::flash('message', 'Menu deleted successfully.');
        Session::flash('type', 'success');
        return redirect()->back();
    }
}
