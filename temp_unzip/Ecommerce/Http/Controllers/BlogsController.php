<?php

namespace Modules\Ecommerce\Http\Controllers;

use Modules\Ecommerce\Entities\Blog;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Session;
use DB;

class BlogsController extends Controller
{
    use \App\Traits\TenantInfo;

    public function index()
    {
        $blogs = DB::table('blogs')->get();
        return view('ecommerce::backend.blogs.index', compact('blogs'));
    }

    public function create()
    {
        return view('ecommerce::backend.blogs.create');
    }

    public function store(Request $request)
    {
        if(!env('USER_VERIFIED')){
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');
        }
        if (isset($request->image)) {
            $this->validate($request, [
                'thumbnail' => 'image|mimes:jpg,jpeg,png,gif|max:100000',
            ]);
        }

        $input = $request->except('thumbnail,og_image');
        if (strlen($request->slug) < 1){
            $input['slug'] = $this->generateUniqueSlug(Str::slug($request->page_name, '-'), $count = 0);
        }

        $input['status'] = $request->status;

        if (!file_exists(public_path('frontend/images/blog'))) {
            mkdir(public_path('frontend/images/blog'), 0777, true);
        }

        $thumbnail = $request->thumbnail;
        if ($thumbnail) {
            $ext = pathinfo($thumbnail->getClientOriginalName(), PATHINFO_EXTENSION);
            $imageName = 'thumb_'.date("Ymdhis");
            if(!config('database.connections.saleprosaas_landlord')) {
                $imageName = $imageName . '.' . $ext;
            }
            else {
                $imageName = $this->getTenantId() . '_' . $imageName . '.' . $ext;

            }
            $thumbnail->move(public_path('frontend/images/blog'), $imageName);

            list($width, $height) = getimagesize(public_path('frontend/images/blog/'). $imageName);
            if ($width > $height) {
                $manager = new ImageManager(Driver::class);
                $image = $manager->read(public_path('frontend/images/blog/'). $imageName);
                $image->cover(1000, 600)->save(public_path('frontend/images/blog/'). $imageName, 100);
            } elseif($width > $height){
                $manager = new ImageManager(Driver::class);
                $image = $manager->read(public_path('frontend/images/blog/'). $imageName);
                $image->cover(600, 600)->save(public_path('frontend/images/blog/'). $imageName, 100);
            } else {
                $manager = new ImageManager(Driver::class);
                $image = $manager->read(public_path('frontend/images/blog/'). $imageName);
                $image->cover(600, 1000)->save(public_path('frontend/images/blog/'). $imageName, 100);
            }

            $input['thumbnail'] = $imageName;
        }

        $og_image = $request->og_image;
        if ($og_image) {
            $ext = pathinfo($og_image->getClientOriginalName(), PATHINFO_EXTENSION);
            $imageName = 'og_'.date("Ymdhis");
            if(!config('database.connections.saleprosaas_landlord')) {
                $imageName = $imageName . '.' . $ext;
            }
            else {
                $imageName = $this->getTenantId() . '_' . $imageName . '.' . $ext;

            }
            $og_image->move(public_path('frontend/images/blog'), $imageName);

            list($width, $height) = getimagesize(public_path('frontend/images/blog/'). $imageName);
            if ($width > $height) {
                $manager = new ImageManager(Driver::class);
                $image = $manager->read(public_path('frontend/images/blog/'). $imageName);
                $image->cover(1000, 600)->save(public_path('frontend/images/blog/'). $imageName, 100);
            } elseif($width > $height){
                $manager = new ImageManager(Driver::class);
                $image = $manager->read(public_path('frontend/images/blog/'). $imageName);
                $image->cover(600, 600)->save(public_path('frontend/images/blog/'). $imageName, 100);
            } else {
                $manager = new ImageManager(Driver::class);
                $image = $manager->read(public_path('frontend/images/blog/'). $imageName);
                $image->cover(600, 1000)->save(public_path('frontend/images/blog/'). $imageName, 100);
            }

            $input['og_image'] = $imageName;
        }

        $blog = Blog::create($input);

        return $blog->id;
    }

    public function edit($id)
    {
        //$data = Blog::findOrFail($id);

        if (request()->ajax())
        {
            $data = Blog::findOrFail($id);

            return $data;
        }

        return view('ecommerce::backend.blogs.edit', compact('id'));
    }


    public function update(Request $request)
    {
        if(!env('USER_VERIFIED')){
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');
        }

        $id = $request->post_id;
        $data = [];
        $data['title'] = htmlspecialchars($request->title);
        $data['slug'] = $request->slug;
        $data['description'] = $request->description;
        $data['meta_title'] = htmlspecialchars($request->meta_title);
        $data['meta_description'] = htmlspecialchars($request->meta_description);
        $data['og_title'] = htmlspecialchars($request->og_title);
        $data['og_description'] = htmlspecialchars($request->og_description);
        $data['status'] = $request->status;

        $post = Blog::find($id);

        $thumbnail = $request->thumbnail;
        if ($thumbnail) {
            $ext = pathinfo($thumbnail->getClientOriginalName(), PATHINFO_EXTENSION);
            $imageName = 'thumb_'.date("Ymdhis");
            if(!config('database.connections.saleprosaas_landlord')) {
                $imageName = $imageName . '.' . $ext;
            }
            else {
                $imageName = $this->getTenantId() . '_' . $imageName . '.' . $ext;

            }
            $thumbnail->move(public_path('frontend/images/blog'), $imageName);

            list($width, $height) = getimagesize(public_path('frontend/images/blog/'). $imageName);
            if ($width > $height) {
                $manager = new ImageManager(Driver::class);
                $image = $manager->read(public_path('frontend/images/blog/'). $imageName);
                $image->cover(1000, 600)->save(public_path('frontend/images/blog/'). $imageName, 100);
            } elseif($width > $height){
                $manager = new ImageManager(Driver::class);
                $image = $manager->read(public_path('frontend/images/blog/'). $imageName);
                $image->cover(600, 600)->save(public_path('frontend/images/blog/'). $imageName, 100);
            } else {
                $manager = new ImageManager(Driver::class);
                $image = $manager->read(public_path('frontend/images/blog/'). $imageName);
                $image->cover(600, 1000)->save(public_path('frontend/images/blog/'). $imageName, 100);
            }

            $data['thumbnail'] = $imageName;

            if($post->thumbnail){
                $this->fileDelete('frontend/images/blog/', $post->thumbnail);
            }
        }

        $og_image = $request->og_image;
        if ($og_image) {
            $ext = pathinfo($og_image->getClientOriginalName(), PATHINFO_EXTENSION);
            $imageName = 'og_'.date("Ymdhis");
            if(!config('database.connections.saleprosaas_landlord')) {
                $imageName = $imageName . '.' . $ext;
            }
            else {
                $imageName = $this->getTenantId() . '_' . $imageName . '.' . $ext;
            }
            $og_image->move(public_path('frontend/images/blog'), $imageName);

            list($width, $height) = getimagesize(public_path('frontend/images/blog/'). $imageName);
            if ($width > $height) {
                $manager = new ImageManager(Driver::class);
                $image = $manager->read(public_path('frontend/images/blog/'). $imageName);
                $image->cover(1000, 600)->save(public_path('frontend/images/blog/'). $imageName, 100);
            } elseif($width > $height){
                $manager = new ImageManager(Driver::class);
                $image = $manager->read(public_path('frontend/images/blog/'). $imageName);
                $image->cover(600, 600)->save(public_path('frontend/images/blog/'). $imageName, 100);
            } else {
                $manager = new ImageManager(Driver::class);
                $image = $manager->read(public_path('frontend/images/blog/'). $imageName);
                $image->cover(600, 1000)->save(public_path('frontend/images/blog/'). $imageName, 100);
            }

            $data['og_image'] = $imageName;

            if($post->og_image){
                $this->fileDelete('frontend/images/blog/', $post->og_image);
            }
        }

        $post->update($data);

        return $post->id;

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
        $page = Blog::where('id', $id)->where('slug',$slug)->first();

        if($page){
            return $slug;
        } else {
            return $this->generateUniqueSlug($slug);
        }
    }

    public function destroy($id)
    {
        $post = Blog::find($id);

        if($post->thumbnail){
            $this->fileDelete(public_path('frontend/images/blog/'), $post->thumbnail);
        }
        if($post->og_image){
            $this->fileDelete(public_path('frontend/images/blog/'), $post->og_image);
        }

        $post->delete();

        Session::flash('message', 'Link deleted successfully.');
        Session::flash('type', 'success');

        return redirect()->back();

    }

}
