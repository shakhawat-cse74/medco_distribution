<?php

namespace Modules\Ecommerce\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Product;
use DB;
use Illuminate\Validation\Rule;


class CollectionController extends Controller
{
    public function index()
    {
        $collections = DB::table('collections')->get();
        return view('ecommerce::backend.collection.index',compact('collections'));
    }

    public function create()
    {
        return view('ecommerce::backend.collection.create');
    }

    public function store(Request $request)
    {
        if(!env('USER_VERIFIED')){
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');
        }
        
        $input = $request->except('_token');
        if (strlen($request->slug) < 1){
            $input['slug'] = $this->generateUniqueSlug(Str::slug($request->page_name, '-'), $count = 0);
        }

        $input['products'] = rtrim($request->products, ",");

        DB::table('collections')->insert($input);
        return redirect('collection')->with('message', 'Collection inserted successfully');
    }

    public function edit($id)
    {

        $collection = DB::table('collections')->where('id',$id)->first();

        $product_arr = explode(',',$collection->products);

        $products = DB::table('products')->whereIn('id',$product_arr)->get();

        return view('ecommerce::backend.collection.edit', compact('collection','products'));
    }

   
    public function update(Request $request)
    {
        if(!env('USER_VERIFIED')){
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');
        }

        $id = $request->id;
        $data = [];
        $data['name'] = htmlspecialchars($request->name);
        $data['slug'] = $request->slug;
        $data['page_title'] = htmlspecialchars($request->page_title);
        $data['short_description'] = htmlspecialchars($request->short_description);
        $data['products'] = rtrim($request->products, ",");
        $data['status'] = $request->status;

        $collection = DB::table('collections')->where('id',$id)->update($data);

        return redirect()->route('collection.index')->with('message', 'Collection updated successfully!');
        
    }

    public function destroy($id)
    {
        if(!env('USER_VERIFIED')){
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');
        }

        DB::table('collections')->whereId($id)->delete();

        return redirect()->back()->with('message', 'Page deleted successfully!');

    }

    public function generateUniqueSlug($originalSlug, $count = 0)
    {
        $newSlug = ($count === 0) ? $originalSlug : $originalSlug . '-' . $count;

        $existingSlug = DB::table('collections')->where('slug', $newSlug)->first();

        if (!$existingSlug) {
            return $newSlug;
        } else {
            return $this->generateUniqueSlug($originalSlug, $count + 1);
        }
    }

    public function generateUniqueSlugEdit($id,$slug)
    {
        $page = DB::table('collections')->where('id', $id)->where('slug',$slug)->first();

        if($page){
            return $slug;
        } else {
            return $this->generateUniqueSlug($slug);
        }
    }
}
