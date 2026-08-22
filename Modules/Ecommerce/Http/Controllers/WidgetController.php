<?php

namespace Modules\Ecommerce\Http\Controllers;

use Modules\Ecommerce\Entities\Widgets;
use Modules\Ecommerce\Entities\Menus;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Http\Request;
use Session;
use DB;

class WidgetController extends Controller
{
    use \App\Traits\TenantInfo;
    
    public function index()
    {
        $widgets = DB::table('widgets')->orderBy('order','ASC')->get();

        $footer_top = $widgets->where('location','footer_top');
        $footer = $widgets->where('location','footer');
        $product_details_sidebar = $widgets->where('location','product_details');

        $menus = DB::table('menus')->get();

        return view('ecommerce::backend.widgets.index', compact('footer_top','footer','product_details_sidebar','menus'));
    }

    public function store(Request $request)
    {
        if(!env('USER_VERIFIED')){
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');
        }

        $data = $request->except('feature_icon');
        $widget = Widgets::create($data);

        return response()->json(['id' => $widget->id]);
    }

    public function update(Request $request)
    {
        if(!env('USER_VERIFIED')){
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');
        }

        $data = $request->except('feature_icon');
        $widget = Widgets::find($request->id);
        $widget->update($data);
        
        if(isset($request->feature_icon))
        {
            $image = $request->feature_icon;
            $ext = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
            $imageName = date("Ymdhis") . ($request->order);
            if(!config('database.connections.saleprosaas_landlord')) {
                $imageName = $imageName . '.' . $ext;
            }
            else {
                $imageName = $this->getTenantId() . '_' . $imageName . '.' . $ext;
            }
            
            $image->move(public_path('frontend/images/features'), $imageName);
            $manager = new ImageManager(Driver::class);
            $image = $manager->read(public_path('frontend/images/features/'). $imageName);
            $image->cover(100, 100)->save(public_path('frontend/images/features/'). $imageName, 100);
            
            $widget->feature_icon = $imageName;
            $widget->save();
        }

        return response()->json(['id' => $widget->id]);
    }

    public function order(Request $request)
    {
        $items = json_decode($request->getContent(), true);

        foreach ($items as $item) {
            $id = $item['id'];
            $position = $item['index'];
        
            DB::table('widgets')->where('id', $id)->update(['order' => $position]);
        }
    }

    public function delete($id)
    {
        if(!env('USER_VERIFIED')){
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');
        }
        
        $widget = Widgets::where('id',$id)->delete();

        if(isset($widget->icon))
        {
            $this->fileDelete('frontend/images/features/', $widget->icon);
        }

        return redirect()->back();
    }
}
