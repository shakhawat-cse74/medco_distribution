<?php

namespace Modules\Ecommerce\Http\Controllers;

use Modules\Ecommerce\Entities\Page;
use Modules\Ecommerce\Entities\PageWidgets;
use Illuminate\Http\Request;
use Str;
use Session;
use DB;

class PageController extends Controller
{
    use \App\Traits\TenantInfo;

    public function index()
    {
        $pages = Page::all();
        return view('ecommerce::backend.pages.index', compact('pages'));
    }

    public function create()
    {
        $categories = DB::table('categories')->where('is_active', 1)->get();
        $parent_categories = $categories->whereNull('parent_id');
        $brands = DB::table('brands')->where('is_active', 1)->get();
        $collections = DB::table('collections')->where('status', 1)->get();
        return view('ecommerce::backend.pages.create', compact('categories', 'parent_categories', 'brands', 'collections'));
    }


    public function store(Request $request)
    {
        if(!env('USER_VERIFIED')){
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');
        }
        if (isset($request->image)) {
            $this->validate($request, [
                'image' => 'image|mimes:jpg,jpeg,png,gif|max:100000',
            ]);
        }

        $input = $request->except('image');
        if (strlen($request->slug) < 1){
            $input['slug'] = $this->generateUniqueSlug(Str::slug($request->page_name, '-'), $count = 0);
        }

        $input['status'] = $request->status;
        $image = $request->og_image;
        if ($image) {
            $ext = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
            $imageName = date("Ymdhis");
            if(!config('database.connections.saleprosaas_landlord')) {
                $imageName = $imageName . '.' . $ext;
                $image->move('frontend/images/page', $imageName);
            }
            else {
                $imageName = $this->getTenantId() . '_' . $imageName . '.' . $ext;
                $image->move('frontend/images/page', $imageName);

            }  
            $input['og_image'] = $imageName;
        }

        $page = Page::create($input);

        return $page->id;
    }

    public function generateUniqueSlug($originalSlug, $count = 0)
    {
        $newSlug = ($count === 0) ? $originalSlug : $originalSlug . '-' . $count;

        $existingSlug = Page::where('slug', $newSlug)->first();

        if (!$existingSlug) {
            return $newSlug;
        } else {
            return $this->generateUniqueSlug($originalSlug, $count + 1);
        }
    }

    public function generateUniqueSlugEdit($id,$slug)
    {
        $page = Page::where('id', $id)->where('slug',$slug)->first();

        if($page){
            return $slug;
        } else {
            return $this->generateUniqueSlug($slug);
        }
    }


    public function edit($id)
    {
        $categories = DB::table('categories')->where('is_active', 1)->get();
        $parent_categories = $categories->whereNull('parent_id');
        $brands = DB::table('brands')->where('is_active', 1)->get();
        $collections = DB::table('collections')->where('status', 1)->get();
        $page_widgets = PageWidgets::where('page_id',$id)->orderBy('order','ASC')->get();

        if (request()->ajax())
        {
            $data = Page::findOrFail($id);

            return $data;
        }

        return view('ecommerce::backend.pages.edit', compact('id','categories', 'parent_categories', 'brands', 'collections', 'page_widgets'));
    }


    public function update(Request $request)
    {
        if(!env('USER_VERIFIED')){
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');
        }
        $id = $request->page_id;
        $data = [];
        $data['page_name'] = htmlspecialchars($request->page_name);
        $data['slug'] = $request->slug;
        $data['description'] = $request->description;
        $data['meta_title'] = htmlspecialchars($request->meta_title);
        $data['meta_description'] = htmlspecialchars($request->meta_description);
        $data['og_title'] = htmlspecialchars($request->og_title);
        $data['og_description'] = htmlspecialchars($request->og_description);
        $data['status'] = $request->status;
        $data['template'] = $request->template;

        $page = Page::find($id);

        $image = $request->og_image;
        if(isset($image)) {
            $ext = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
            $imageName = date("Ymdhis");
            $imageName = $imageName . '.' . $ext;
            if(!config('database.connections.saleprosaas_landlord')) {
                $imageName = $imageName . '.' . $ext;
                $image->move('frontend/images/page', $imageName);
            }
            else {
                $imageName = $this->getTenantId() . '_' . $imageName . '.' . $ext;
                $image->move('frontend/images/page', $imageName);

            }
            $data['og_image'] = $imageName;

            if($page->og_image){
                $this->fileDelete('frontend/images/page/', $page->og_image);
            }
        }

        $page->update($data);

        return $page->id;
    }

    public function destroy($id)
    {
        if(!env('USER_VERIFIED')){
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');
        }
        Page::whereId($id)->delete();

        return redirect()->back()->with('message', 'Page deleted successfully!');
    }
    function delete_by_selection(Request $request)
    {
        if(!env('USER_VERIFIED')){
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');
        }

        $page_id = $request['PageListIdArray'];
        $pages = Page::whereIn('id', $page_id);
        if ($pages->delete()) {
            return response()->json(['success' => __('Multi Delete', ['key' => __('db.Account')])]);
        } else {
            return response()->json(['error' => 'Error,selected Accounts can not be deleted']);
        }
    }

    public function status($id, $status)
    {
        if(!env('USER_VERIFIED')){
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');
        }

        Page::where('id', $id)->update(['status' => $status]);
        return response()->json(['success' => _('updates')]);
    }
}
