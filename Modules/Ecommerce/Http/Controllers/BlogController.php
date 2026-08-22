<?php

namespace Modules\Ecommerce\Http\Controllers;

use Modules\Ecommerce\Entities\Blog;
use Illuminate\Http\Request;
use Str;
use Session;
use DB;

class BlogController extends Controller
{

    public function index()
    {
        $blogs = Blog::all();
        return view('ecommerce::backend.blogs.index', compact('blogs'));
    }

    public function create()
    {
        $categories = DB::table('categories')->where('is_active', 1)->get();
        $parent_categories = $categories->whereNull('parent_id');
        $brands = DB::table('brands')->where('is_active', 1)->get();
        $collections = DB::table('collections')->where('status', 1)->get();
        return view('ecommerce::backend.blogs.create', compact('categories', 'parent_categories', 'brands', 'collections'));
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
            $input['slug'] = $this->generateUniqueSlug(Str::slug($request->blog_name, '-'), $count = 0);
        }

        $input['status'] = $request->status;
        $image = $request->og_image;
        if ($image) {
            $ext = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
            $imageName = date("Ymdhis");
            $imageName = $imageName . '.' . $ext;
            $image->move(public_path('frontend/images/blog'), $imageName);
            $input['og_image'] = $imageName;
        }

        $blog = Blog::create($input);

        return $blog->id;
    }

    public function generateUniqueSlug($originalSlug, $count = 0)
    {
        $newSlug = ($count === 0) ? $originalSlug : $originalSlug . '-' . $count;

        $existingSlug = Blog::where('slug', $newSlug)->first();

        if (!$existingSlug) {
            return $newSlug;
        } else {
            return $this->generateUniqueSlug($originalSlug, $count + 1);
        }
    }

    public function generateUniqueSlugEdit($id,$slug)
    {
        $blog = Blog::where('id', $id)->where('slug',$slug)->first();

        if($blog){
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
        $blog_widgets = blogWidgets::where('blog_id',$id)->orderBy('order','ASC')->get();

        if (request()->ajax())
        {
            $data = Blog::findOrFail($id);

            return $data;
        }

        return view('ecommerce::backend.blogs.edit', compact('id','categories', 'parent_categories', 'brands', 'collections', 'blog_widgets'));
    }


    public function update(Request $request)
    {
        if(!env('USER_VERIFIED')){
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');
        }
        $id = $request->blog_id;
        $data = [];
        $data['blog_name'] = htmlspecialchars($request->blog_name);
        $data['slug'] = $request->slug;
        $data['description'] = $request->description;
        $data['meta_title'] = htmlspecialchars($request->meta_title);
        $data['meta_description'] = htmlspecialchars($request->meta_description);
        $data['og_title'] = htmlspecialchars($request->og_title);
        $data['og_description'] = htmlspecialchars($request->og_description);
        $data['status'] = $request->status;
        $data['template'] = $request->template;

        $blog = Blog::find($id);

        $image = $request->og_image;
        if(isset($image)) {
            $ext = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
            $imageName = date("Ymdhis");
            $imageName = $imageName . '.' . $ext;
            $image->move(public_path('frontend/images/blog'), $imageName);
            $data['og_image'] = $imageName;

            if($blog->og_image){
                $this->fileDelete(public_path('frontend/images/blog/'), $blog->og_image);
            }
        }

        $blog->update($data);

        return $blog->id;
    }

    public function destroy($id)
    {
        if(!env('USER_VERIFIED')){
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');
        }
        Blog::whereId($id)->delete();

        return redirect()->back()->with('message', 'blog deleted successfully!');
    }
    function delete_by_selection(Request $request)
    {
        if(!env('USER_VERIFIED')){
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');
        }

        $blog_id = $request['blogListIdArray'];
        $blogs = Blog::whereIn('id', $blog_id);
        if ($blogs->delete()) {
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

        Blog::where('id', $id)->update(['status' => $status]);
        return response()->json(['success' => _('updates')]);
    }
}
