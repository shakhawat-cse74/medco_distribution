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

class MenuItemsController extends Controller
{

    public function index($id)
    {
        $pages = DB::table('pages')->where('status',1)->get();
        $categories = DB::table('categories')->where('is_active',1)->get();
        $collections = DB::table('collections')->where('status',1)->get();
        $brands = DB::table('brands')->where('is_active',1)->get();
        $menus = DB::table('menus')->get();

        $desiredMenu = DB::table('menus')->where('id', $id)->first();
        if ($desiredMenu->content != '') {
            $menuitems = json_decode($desiredMenu->content);
            $menuitems = $menuitems[0];
            foreach ($menuitems as $menu) {
                $item = DB::table('menu_items')->where('id', $menu->id)->first();
                $menu->title = $item->title;
                $menu->name = $item->name;
                $menu->slug = $item->slug;
                $menu->target = $item->target;
                $menu->type = $item->type;
                if (!empty($menu->children[0])) {
                    foreach ($menu->children[0] as $child) {
                        $child_item = DB::table('menu_items')->where('id', $child->id)->first();
                        $child->title = $child_item->title;
                        $child->name = $child_item->name;
                        $child->slug = $child_item->slug;
                        $child->target = $child_item->target;
                        $child->type = $child_item->type;
                    }
                }
            }
        } else {
            $menuitems = DB::table('menu_items')->where('menu_id', $desiredMenu->id)->get();
        }

        return view('ecommerce::backend.menu.menu-items', compact('pages', 'brands', 'categories', 'collections', 'menus', 'desiredMenu', 'menuitems', 'id'));
    }
 
    public function addCatToMenu(Request $request)
    {
        if (!env('USER_VERIFIED')) {
            return redirect()->back()->with('not_permitted', 'This feature is disabled for demo!');
        }

        $menuId = $request->menuid;
        $ids = explode(',', $request->ids);
        $menu = Menus::findOrFail($menuId);

        // Decode existing content or initialize as an empty array
        $oldData = strlen($menu->content) > 0 ? json_decode($menu->content, true) : [[]];

        foreach ($ids as $id) {
            $cat = Category::find($id);
            
            // Create a new MenuItem for this category
            $menuItemData = [
                'title' => $cat->name,
                'slug' => $cat->slug,
                'type' => 'category',
                'menu_id' => $menuId,
                'updated_at' => null
            ];
            
            // Save the MenuItem and get its ID
            $menuItem = MenuItems::create($menuItemData);

            // Prepare the data to be added to the menu's content
            $newContentItem = [
                "id" => $menuItem->id,
            ];

            // Add the new content item to the existing content data
            $oldData[0][] = $newContentItem;
        }

        // Update the menu's content field with the new content structure
        $menu->update(['content' => json_encode($oldData)]);
    }

    public function addCollectionToMenu(Request $request)
    {
        if (!env('USER_VERIFIED')) {
            return redirect()->back()->with('not_permitted', 'This feature is disabled for demo!');
        }

        $menuId = $request->menuid;
        $ids = explode(',', $request->ids);
        $menu = Menus::findOrFail($menuId);

        // Decode existing content or initialize as an empty array
        $oldData = strlen($menu->content) > 0 ? json_decode($menu->content, true) : [[]];

        foreach ($ids as $id) {
            // Retrieve collection details
            $col = DB::table('collections')->where('id', $id)->first();

            if ($col) {
                // Create a new MenuItem for this collection
                $menuItemData = [
                    'title' => $col->name,
                    'slug' => $col->slug,
                    'type' => 'collection',
                    'menu_id' => $menuId,
                    'updated_at' => null
                ];

                // Save the MenuItem and get its ID
                $menuItem = MenuItems::create($menuItemData);

                // Prepare the data to be added to the menu's content
                $newContentItem = [
                    "id" => $menuItem->id,
                ];

                // Add the new content item to the existing content data
                $oldData[0][] = $newContentItem;
            }
        }

        // Update the menu's content field with the new content structure
        $menu->update(['content' => json_encode($oldData)]);
    }

    public function addBrandToMenu(Request $request)
    {
        if (!env('USER_VERIFIED')) {
            return redirect()->back()->with('not_permitted', 'This feature is disabled for demo!');
        }

        $menuId = $request->menuid;
        $ids = explode(',', $request->ids);
        $menu = Menus::findOrFail($menuId);

        // Check if the menu has existing content
        $oldData = strlen($menu->content) > 0 ? json_decode($menu->content, true) : [[]];

        foreach ($ids as $id) {
            $brand = DB::table('brands')->where('id', $id)->first();

            // Create a new MenuItem for each brand
            $menuItemData = [
                'title' => $brand->title,
                'slug' => $brand->slug,
                'type' => 'brand',
                'menu_id' => $menuId,
                'updated_at' => null
            ];
            $menuItem = MenuItems::create($menuItemData);

            // Prepare the brand entry for menu content
            $brandArray = [
                'title' => $brand->title,
                'slug' => $brand->slug,
                'name' => null,
                'type' => 'brand',
                'target' => null,
                'id' => $menuItem->id,
                'children' => [[]]
            ];

            // Add the brand entry to the existing content array
            array_push($oldData[0], $brandArray);
        }

        // Update the menu content with the new entries
        $menu->update(['content' => json_encode($oldData)]);
    }

    public function addPageToMenu(Request $request)
    {
        if (!env('USER_VERIFIED')) {
            return redirect()->back()->with('not_permitted', 'This feature is disabled for demo!');
        }

        $menuId = $request->menuid;
        $ids = explode(',', $request->ids);
        $menu = Menus::findOrFail($menuId);
        $oldData = strlen($menu->content) > 0 ? json_decode($menu->content, true) : [[]];

        foreach ($ids as $id) {
            $page = Page::find($id);

            // Create a new MenuItem for each page
            $menuItemData = [
                'title' => $page->page_name,
                'slug' => $page->slug,
                'type' => 'page',
                'menu_id' => $menuId,
                'updated_at' => null
            ];
            $menuItem = MenuItems::create($menuItemData);

            // Prepare the page entry for menu content
            $pageArray = [
                'title' => $page->page_name,
                'slug' => $page->slug,
                'name' => null,
                'type' => 'page',
                'target' => null,
                'id' => $menuItem->id,
                'children' => [[]]
            ];

            array_push($oldData[0], $pageArray);
        }

        $menu->update(['content' => json_encode($oldData)]);
    }

    public function addBlogToMenu(Request $request)
    {
        if (!env('USER_VERIFIED')) {
            return redirect()->back()->with('not_permitted', 'This feature is disabled for demo!');
        }

        $menuId = $request->menuid;
        $menu = Menus::findOrFail($menuId);
        $oldData = strlen($menu->content) > 0 ? json_decode($menu->content, true) : [[]];

        // Create a new MenuItem for the blog link
        $menuItemData = [
            'title' => $request->link,
            'slug' => url('blog'),
            'type' => 'custom',
            'menu_id' => $menuId,
            'updated_at' => null
        ];
        $menuItem = MenuItems::create($menuItemData);

        // Prepare the blog entry for menu content
        $blogArray = [
            'title' => $request->link,
            'slug' => url('blog'),
            'name' => null,
            'type' => 'custom',
            'target' => null,
            'id' => $menuItem->id,
            'children' => [[]]
        ];

        array_push($oldData[0], $blogArray);
        $menu->update(['content' => json_encode($oldData)]);
    }

    public function addCustomLink(Request $request)
    {
        if (!env('USER_VERIFIED')) {
            return redirect()->back()->with('not_permitted', 'This feature is disabled for demo!');
        }

        $menuId = $request->menuid;
        $menu = Menus::findOrFail($menuId);
        $oldData = strlen($menu->content) > 0 ? json_decode($menu->content, true) : [[]];

        // Create a new MenuItem for the custom link
        $menuItemData = [
            'title' => $request->link,
            'slug' => base64_decode($request->url),
            'type' => 'custom',
            'menu_id' => $menuId,
            'updated_at' => null
        ];
        $menuItem = MenuItems::create($menuItemData);

        // Prepare the custom link entry for menu content
        $customLinkArray = [
            'title' => $request->link,
            'slug' => base64_decode($request->url),
            'name' => null,
            'type' => 'custom',
            'target' => null,
            'id' => $menuItem->id,
            'children' => [[]]
        ];

        array_push($oldData[0], $customLinkArray);
        $menu->update(['content' => json_encode($oldData)]);
    }

    public function updateMenuItem(Request $request)
    {
        if(!env('USER_VERIFIED')){
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');
        }
        $data = $request->all();
        $item = MenuItems::findOrFail($request->id);
        $item->update($data);
        return redirect()->back();
    }

    public function deleteMenuItem($id, $key, $in)
    {
        if(!env('USER_VERIFIED')){
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');
        }
        $menuitem = MenuItems::findOrFail($id);
        $menu = Menus::where('id', $menuitem->menu_id)->first();
        if ($menu->content != '') {
            $data = json_decode($menu->content, true);
            $maindata = $data[0];
            if ($in == 'x') {
                unset($data[0][$key]);
                $newdata = json_encode($data);
                $menu->update(['content' => $newdata]);
            } else {
                unset($data[0][$key]['children'][0][$in]);
                $newdata = json_encode($data);
                $menu->update(['content' => $newdata]);
            }
        }
        $menuitem->delete();
        return redirect()->back();
    }

    public function destroy(Request $request)
    {
        if(!env('USER_VERIFIED')){
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');
        }
        MenuItems::where('menu_id', $request->id)->delete();
        Menus::findOrFail($request->id)->delete();
        return redirect('manage-menus')->with('success', 'Menu deleted successfully');
    }
}
