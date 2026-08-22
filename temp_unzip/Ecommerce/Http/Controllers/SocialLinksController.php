<?php

namespace Modules\Ecommerce\Http\Controllers;

use Modules\Ecommerce\Entities\SocialLinks;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Traits\CacheForget;
use Session;
use Cache;
use DB;

class SocialLinksController extends Controller
{
    use CacheForget;

    public function index()
    {
        $links = DB::table('social_links')->get();
        return view('ecommerce::backend.social.index', compact('links'));
    }
    
    public function store(Request $request)
    {  
        if(!env('USER_VERIFIED')){
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');
        }

        $titles = $request->input('title');
        $links = $request->input('link');
        $orders = $request->input('order');
        $icons = $request->input('icon');
        
        foreach($orders as $key=>$order) {  
            
            $data = [
                'order'  => $orders[$key],
                'title'  => $titles[$key],
                'link'   => $links[$key],
                'icon'   => $icons[$key],
            ];

            $social_links = SocialLinks::create($data);

        }

        $this->cacheForget('social_links');

        Session::flash('message', 'Links inserted successfully.');
        Session::flash('type', 'success');

        return redirect()->back();
    }

    public function edit($id)
    {
        $data = SocialLinks::findOrFail($id);

        return $data;
    }

   
    public function update(Request $request)
    {
        if(!env('USER_VERIFIED')){
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');
        }
        
        $id = $request->hidden_id;
        $data = [];
        $data['title'] = htmlspecialchars($request->title);
        $data['link'] = $request->link;
        $data['order'] = htmlspecialchars($request->order);
        $data['icon'] = $request->icon;

        $link = SocialLinks::where('id', $id)->update($data);

        $this->cacheForget('social_links');

        Session::flash('message', 'Link updated successfully.');
        Session::flash('type', 'success');

        return redirect()->back();
        
    }

    public function destroy($id)
    {
        SocialLinks::whereId($id)->delete();

        $this->cacheForget('social_links');

        Session::flash('message', 'Link deleted successfully.');
        Session::flash('type', 'success');

        return redirect()->back();

    }
   
}
