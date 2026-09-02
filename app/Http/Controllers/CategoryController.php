<?php

namespace App\Http\Controllers;

use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Product;
use DB;
use Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\Rule;
use App\Traits\TenantInfo;
use App\Traits\CacheForget;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;

class CategoryController extends Controller
{
    use CacheForget;
    use TenantInfo;

    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('category')) {
            return view('backend.category.create');
        }
        else
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
    }

    public function categoryData(Request $request)
    {
        $columns = array(
            0 => 'id',
            2 => 'name',
            3 => 'parent_id',
            4 => 'is_active',
        );

        $typeFilter = $request->input('category_type', $request->input('type', 'all'));

        $query = Category::where('is_active', true);
        if ($typeFilter === 'parent') {
            $query->whereNull('parent_id');
        } elseif ($typeFilter === 'subcategory') {
            $query->whereNotNull('parent_id');
        }

        $totalData = $query->count();
        $totalFiltered = $totalData;

        if ($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;

        $start = $request->input('start', 0);
        $orderColIndex = $request->input('order.0.column', 2);
        $order = $columns[$orderColIndex] ?? 'name';
        $dir = $request->input('order.0.dir', 'asc');

        if (empty($request->input('search.value'))) {
            $categories = $query->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $categories = $query->where('name', 'LIKE', "%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)->get();

            $searchQuery = Category::where('name', 'LIKE', "%{$search}%")->where('is_active', true);
            if ($typeFilter === 'parent') {
                $searchQuery->whereNull('parent_id');
            } elseif ($typeFilter === 'subcategory') {
                $searchQuery->whereNotNull('parent_id');
            }
            $totalFiltered = $searchQuery->count();
        }

        // Preload parent category names
        $parentIds = $categories->pluck('parent_id')->filter()->unique();
        $parentMap = !empty($parentIds) ? Category::whereIn('id', $parentIds)->pluck('name', 'id')->toArray() : [];

        $data = array();
        if (!empty($categories)) {
            foreach ($categories as $key => $category) {
                $nestedData['id'] = $category->id;
                $nestedData['key'] = $key;

                if ($category->image)
                    $image = '<img src="' . url('images/category', $category->image) . '" height="80" width="80" style="object-fit: cover; border-radius: 4px;">';
                else
                    $image = '<img src="' . url('images/zummXD2dvAtI.png') . '" height="80" width="80" style="object-fit: cover; border-radius: 4px;">';

                $nestedData['name'] = '<div class="d-flex align-items-center">' . $image . '<span style="margin:0 15px;font-weight: 500;">' . e($category->name) . '</span></div>';

                if ($category->parent_id) {
                    $pName = $parentMap[$category->parent_id] ?? 'N/A';
                    $nestedData['parent_id'] = '<span class="badge badge-info" style="font-size:12px;padding:5px 9px;"><i class="ti ti-corner-down-right"></i> ' . e($pName) . '</span>';
                } else {
                    $nestedData['parent_id'] = '<span class="badge badge-secondary" style="font-size:12px;padding:5px 9px;">' . __('Main Category') . '</span>';
                }

                $nestedData['number_of_product'] = $category->product()->where('is_active', true)->count();
                $nestedData['stock_qty'] = $category->product()->where('is_active', true)->sum('qty');
                $total_price = $category->product()->where('is_active', true)->sum(DB::raw('price * qty'));
                $total_cost = $category->product()->where('is_active', true)->sum(DB::raw('cost * qty'));

                $nestedData['stock_worth'] = format_currency($total_price) . ' / ' . format_currency($total_cost);

                $addSubAction = '';
                if (!$category->parent_id) {
                    $addSubAction = '<li>
                        <button type="button" data-parent-id="' . $category->id . '" data-parent-name="' . htmlspecialchars($category->name, ENT_QUOTES) . '" class="open-AddSubCategoryDialog btn btn-link text-info"><i class="ti ti-plus"></i> ' . __("Add Sub Category") . '</button>
                    </li><li class="divider"></li>';
                }

                $nestedData['options'] = '<div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' . __("db.action") . '
                              <span class="caret"></span>
                              <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                                ' . $addSubAction . '
                                <li>
                                    <button type="button" data-id="' . $category->id . '" class="open-EditCategoryDialog btn btn-link" data-toggle="modal" data-target="#editModal" ><i class="ti ti-edit"></i> ' . __("db.edit") . '</button>
                                </li>
                                <li class="divider"></li>
                                <form action="' . route("category.destroy", $category->id) . '" method="POST">' . csrf_field() . '' . method_field("DELETE") . '
                                <li>
                                  <button type="submit" class="btn btn-link" onclick="return confirmDelete()"><i class="ti ti-trash"></i> ' . __("db.delete") . '</button>
                                </li></form>
                            </ul>
                        </div>';
                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );

        return response()->json($json_data);
    }

    public function getSubCategories($parentId)
    {
        $subcategories = Category::where('is_active', true)
            ->where('parent_id', $parentId)
            ->select('id', 'name')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json($subcategories);
    }

    public function getParentCategories()
    {
        $parents = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->select('id', 'name')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json($parents);
    }

    public function store(StoreCategoryRequest $request)
    {
        $image = $request->image;
        
        if ($image) {
            $ext = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
            $imageName = date("Ymdhis");
            if(!config('database.connections.saleprosaas_landlord')) {
                $imageName = $imageName . '.' . $ext;
                $image->move(public_path('images/category'), $imageName);
            }
            else {
                $imageName = $this->getTenantId() . '_' . $imageName . '.' . $ext;
                $image->move(public_path('images/category'), $imageName);
            }
            if (!file_exists(public_path('images/category/large/'))) {
                mkdir(public_path('images/category/large/'), 0755, true);
            }
            $manager = new ImageManager(new GdDriver());
            $image = $manager->read(public_path('images/category/' . $imageName));

            $image->resize(600, 750)->save(public_path('images/category/large/' . $imageName));
            
            $lims_category_data['image'] = $imageName;
        }
        $icon = $request->icon;
        if ($icon) {
            if (!file_exists(public_path('images/category/icons/'))) {
                mkdir(public_path('images/category/icons/'), 0755, true);
            }
            $ext = pathinfo($icon->getClientOriginalName(), PATHINFO_EXTENSION);
            $iconName = date("Ymdhis");
            if(!config('database.connections.saleprosaas_landlord')) {
                $iconName = $iconName . '.' . $ext;
                $icon->move(public_path('images/category/icons/'), $iconName);
            }
            else {
                $iconName = $this->getTenantId() . '_' . $iconName . '.' . $ext;
                $icon->move(public_path('images/category/icons/'), $iconName);
            }

            $manager = new ImageManager(new GdDriver());
            $image = $manager->read(public_path('images/category/icons/' . $iconName));
            
            $lims_category_data['icon'] = $iconName;
        }
        $lims_category_data['name'] = preg_replace('/\s+/', ' ', $request->name);
        $lims_category_data['parent_id'] = $request->parent_id ?: null;
        $lims_category_data['is_active'] = true;
        if(isset($request->ajax))
            $lims_category_data['ajax'] = $request->ajax;
        else
            $lims_category_data['ajax'] = 0;
       
        if(isset($request->is_sync_disable))
            $lims_category_data['is_sync_disable'] = $request->is_sync_disable;

        if(in_array('ecommerce', explode(',', gen_setting()->modules ?? config('addons') ?? ''))) {
            $lims_category_data['slug'] = Str::slug($request->name, '-');
            if($request->featured == 1){
                $lims_category_data['featured'] = 1;
            } else {
                $lims_category_data['featured'] = 0;
            }
            $lims_category_data['page_title'] = $request->page_title;
            $lims_category_data['short_description'] = $request->short_description;
        }
        $category = Category::create($lims_category_data);

        $this->cacheForget('category_list');
        $this->cacheForget('categories_list');
        if($lims_category_data['ajax'])
            return $category;
        else
            return redirect('category')->with('message', __('db.Category inserted successfully'));
    }

    public function edit($id)
    {
        $lims_category_data = DB::table('categories')->where('id', $id)->first();
        $lims_parent_data = DB::table('categories')->where('id', $lims_category_data->parent_id)->first();
        if($lims_parent_data){
            $lims_category_data->parent = $lims_parent_data->name;
        }
        return $lims_category_data;
    }

    public function update(UpdateCategoryRequest $request)
    {
        if(!config('app.user_verified'))
            return redirect()->back()->with('not_permitted', __('db.This feature is disable for demo!'));

        $lims_category_data = DB::table('categories')->where('id', $request->category_id)->first();

        $input = $request->except('image','icon','_method','_token','category_id');

        $image = $request->image;
        if ($image) {
            $this->fileDelete(public_path('images/category/'),$lims_category_data->image);

            $ext = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
            $imageName = date("Ymdhis");
            if(!config('database.connections.saleprosaas_landlord')) {
                $imageName = $imageName . '.' . $ext;
                $image->move(public_path('images/category'), $imageName);
            }
            else {
                $imageName = $this->getTenantId() . '_' . $imageName . '.' . $ext;
                $image->move(public_path('images/category'), $imageName);
            }
            if (!file_exists(public_path('images/category/large/'))) {
                mkdir(public_path('images/category/large/'), 0755, true);
            }
            
            $manager = new ImageManager(new GdDriver());
            $image = $manager->read(public_path('images/category/' . $imageName));

            $image->resize(600, 750)->save(public_path('images/category/large/' . $imageName));
             
            $input['image'] = $imageName;
        }

        $icon = $request->icon;
        if ($icon) {
            if (!file_exists(public_path('images/category/icons/'))) {
                mkdir(public_path('images/category/icons/'), 0755, true);
            }
            $this->fileDelete(public_path('images/category/icons/'), $lims_category_data->icon);

            $ext = pathinfo($icon->getClientOriginalName(), PATHINFO_EXTENSION);
            $iconName = date("Ymdhis");
            if(!config('database.connections.saleprosaas_landlord')) {
                $iconName = $iconName . '.' . $ext;
                $icon->move(public_path('images/category/icons/'), $iconName);
            }
            else {
                $iconName = $this->getTenantId() . '_' . $iconName . '.' . $ext;
                $icon->move(public_path('images/category/icons/'), $iconName);
            }

            $manager = new ImageManager(new GdDriver());
            $image = $manager->read(public_path('images/category/icons/' . $iconName));

            $input['icon'] = $iconName;
        }
        if(!isset($request->featured) && \Schema::hasColumn('categories', 'featured') ){
            $input['featured'] = 0;
        }
        if(!isset($input['is_sync_disable']) && \Schema::hasColumn('categories', 'is_sync_disable'))
            $input['is_sync_disable'] = null;

        if(in_array('ecommerce', explode(',', gen_setting()->modules ?? config('addons') ?? ''))) {
            $input['slug'] = Str::slug($request->name, '-');
            if($request->featured == 1){
                $input['featured'] = 1;
            } else {
                $input['featured'] = 0;
            }
            $input['page_title'] = $request->page_title;
            $input['short_description'] = $request->short_description;
        }

        if (array_key_exists('parent_id', $input)) {
            $input['parent_id'] = $input['parent_id'] ?: null;
        }

        DB::table('categories')->where('id', $request->category_id)->update($input);
        
        $this->cacheForget('category_list');
        $this->cacheForget('categories_list');
        return redirect('category')->with('message', __('db.Category updated successfully'));
    }

    public function import(Request $request)
    {
        //get file
        $upload=$request->file('file');
        $ext = pathinfo($upload->getClientOriginalName(), PATHINFO_EXTENSION);
        if($ext != 'csv')
            return redirect()->back()->with('not_permitted', __('db.Please upload a CSV file'));
        $filename =  $upload->getClientOriginalName();
        $filePath=$upload->getRealPath();
        //open and read
        $file=fopen($filePath, 'r');
        $header= fgetcsv($file);
        $escapedHeader=[];
        //validate
        foreach ($header as $key => $value) {
            $lheader=strtolower($value);
            $escapedItem=preg_replace('/[^a-z]/', '', $lheader);
            array_push($escapedHeader, $escapedItem);
        }
        //looping through other rows
        while($columns=fgetcsv($file))
        {
            if($columns[0]=="")
                continue;
            foreach ($columns as $key => $value) {
                $value=preg_replace('/\D/','',$value);
            }
            $data= array_combine($escapedHeader, $columns);
            $category = Category::firstOrNew(['name' => $data['name'], 'is_active' => true ]);
            if($data['parentcategory']){
                $parent_category = Category::firstOrNew(['name' => $data['parentcategory'], 'is_active' => true ]);
                $parent_id = $parent_category->id;
            }
            else
                $parent_id = null;

            if(in_array('ecommerce', explode(',', gen_setting()->modules ?? config('addons') ?? ''))) {
                $category->slug = Str::slug($data['name'], '-');
            }

            $category->parent_id = $parent_id;
            $category->is_active = true;
            $category->save();
        }
        $this->cacheForget('category_list');
        $this->cacheForget('categories_list');
        return redirect('category')->with('message', __('db.Category imported successfully'));
    }

    public function deleteBySelection(Request $request)
    {
        $category_id = $request['categoryIdArray'];
        foreach ($category_id as $id) {
            $lims_product_data = Product::where('category_id', $id)->get();
            foreach ($lims_product_data as $product_data) {
                $product_data->is_active = false;
                $product_data->save();
            }
            $lims_category_data = Category::findOrFail($id);
            $lims_category_data->is_active = false;
            $lims_category_data->save();

            $this->fileDelete(public_path('images/category/'),$lims_category_data->image);
            $this->fileDelete(public_path('images/category/icons/'),$lims_category_data->icon);
        }
        $this->cacheForget('category_list');
        $this->cacheForget('categories_list');
        return 'Category deleted successfully!';
    }

    public function destroy($id)
    {
        $lims_category_data = Category::findOrFail($id);
        $lims_category_data->is_active = false;
        $lims_product_data = Product::where('category_id', $id)->get();
        foreach ($lims_product_data as $product_data) {
            $product_data->is_active = false;
            $product_data->save();
        }

        $this->fileDelete(public_path('images/category/'),$lims_category_data->image);
        $this->fileDelete(public_path('images/category/icons/'),$lims_category_data->icon);

        $lims_category_data->save();
        $this->cacheForget('category_list');
        $this->cacheForget('categories_list');
        return redirect('category')->with('not_permitted', __('db.Category deleted successfully'));
    }
}
