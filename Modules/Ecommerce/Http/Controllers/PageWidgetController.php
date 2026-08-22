<?php

namespace Modules\Ecommerce\Http\Controllers;

use Modules\Ecommerce\Entities\Page;
use Modules\Ecommerce\Entities\PageWidgets;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Http\Request;
use Session;
use DB; 

class PageWidgetController extends Controller
{
    use \App\Traits\TenantInfo;
    
    public function store(Request $request)
    {
        // if(!env('USER_VERIFIED')){
        //     return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');
        // }

        $input = $request->except('image');
        
        $input['name'] = $input['widget_name'];
        
        if($input['name'] == 'category-slider-widget'){
            if(isset($request->category_slider_ids)) {
                $input['category_slider_ids'] = implode(',',$request->category_slider_ids);
            }else{
                $input['category_slider_ids'] = NULL;
            }
        }

        if($input['name'] == 'brand-slider-widget'){
            if(isset($request->brand_slider_ids)) {
                $input['brand_slider_ids'] = implode(',',$request->brand_slider_ids);
            }else{
                $input['brand_slider_ids'] = NULL;
            }
        }

        if($input['name'] == 'tab-product-collection-widget'){
            if(isset($request->tab_product_collection_ids)) {
                $input['tab_product_collection_id'] = implode(',',$request->tab_product_collection_ids);
            }else{
                $input['tab_product_collection_id'] = NULL;
            }
        } 

        $widget = PageWidgets::create($input);

        return $widget->id;

    }

    public function update(Request $request)
    {
        // if(!env('USER_VERIFIED')){
        //     return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');
        // }

        $data = $request->except('three_c_banner_image1','three_c_banner_image2','three_c_banner_image3','two_c_banner_image1','two_c_banner_image2','one_c_banner_image1');

        if($data['widget_name'] == 'category-slider-widget'){
            $data['category_slider_ids'] = implode(',',$request->category_slider_ids);
        }

        if($data['widget_name'] == 'brand-slider-widget'){
            $data['brand_slider_ids'] = implode(',',$request->brand_slider_ids);
        }

        if($data['widget_name'] == 'tab-product-collection-widget' && isset($data['tab_product_collection_id'])){
            $data['tab_product_collection_id'] = implode(',',$request->tab_product_collection_ids);
        }

        $widget = PageWidgets::find($request->id);
        $widget->update($data);

        if($request->widget_name == 'image-slider-widget'){
            if (!file_exists(public_path('frontend/images/slider_widget'))) {
                mkdir(public_path('frontend/images/slider_widget'), 0777, true);
            }

            if (!file_exists(public_path('frontend/images/slider_widget/desktop'))) {
                mkdir(public_path('frontend/images/slider_widget/desktop'), 0777, true);
            }

            if (!file_exists(public_path('frontend/images/slider_widget/mobile'))) {
                mkdir(public_path('frontend/images/slider_widget/mobile'), 0777, true);
            }

            if(isset($request->slide_lg_prev)){
                $data['slider_images'] = $request->slide_lg_prev;
                $data['slider_links'] = $request->link_prev;
            }else{
                $data['slider_links'] = [];
                $data['slider_images'] = [];
            }

            if(isset($request->slide_lg)){
                foreach($request->slide_lg as $key=>$link) {  

                    $image_name = date('Ymdhis');
        
                    if(isset($request->slide_lg[$key])) { 
                        $slide_lg = $request->slide_lg[$key];
                        if(!is_string($slide_lg)){
                            $ext = pathinfo($slide_lg->getClientOriginalName(), PATHINFO_EXTENSION);
                            $imageName = $image_name . $key;
        
                            if(!config('database.connections.saleprosaas_landlord')) {
                                $imageName = $imageName . '.' . $ext;
                            }
                            else {
                                $imageName = $this->getTenantId() . '_' . $imageName . '.' . $ext;        
                            }     
        
                            $slide_lg->move(public_path('frontend/images/slider_widget/desktop'), $imageName);

        
                            $data['slider_images'][]= $imageName;
                        }
        
                    }
        
                    if(isset($request->slide_m[$key])) { 
                        $slide_m = $request->slide_m[$key];
                        
                        if(!is_string($slide_m)){
                            $ext = pathinfo($slide_m->getClientOriginalName(), PATHINFO_EXTENSION);
                            $imageName = $image_name . $key;

                            if(!config('database.connections.saleprosaas_landlord')) {
                                $imageName = $imageName . '.' . $ext;
                            }
                            else {
                                $imageName = $this->getTenantId() . '_' . $imageName . '.' . $ext;        
                            }     

                            $slide_m->move(public_path('frontend/images/slider_widget/mobile'), $imageName);
                        }    
                    }

                    $data['slider_links'][] = $request->link[$key];
        
                }
            }
            $data['slider_links'] = implode(',',$data['slider_links']);
            $data['slider_images'] = implode(',',$data['slider_images']);

            $widget->slider_images = $data['slider_images'];
            $widget->slider_links = $data['slider_links'];
            $widget->update($data);
            return $widget;
        }
        
        if(isset($request->three_c_banner_image1))
        {
            if (!file_exists(public_path('frontend/images/banners/'))) {
                mkdir(public_path('frontend/images/banners/'), 0777, true);
            }
            $three_c_banner_image1 = $request->three_c_banner_image1;
            $ext = pathinfo($three_c_banner_image1->getClientOriginalName(), PATHINFO_EXTENSION);
            $imageName = date("Ymdhis") . 1;
            if(!config('database.connections.saleprosaas_landlord')) {
                $imageName = $imageName . '.' . $ext;                
            }
            else {
                $imageName = $this->getTenantId() . '_' . $imageName . '.' . $ext;
            }

            $three_c_banner_image1->move(public_path('frontend/images/banners'), $imageName);

            $manager = new ImageManager(Driver::class);
            $image = $manager->read(public_path('frontend/images/banners/'). $imageName);
            $image->cover(500, 200)->save(public_path('frontend/images/banners/'). $imageName, 100);
            
            $widget->three_c_banner_image1 = $imageName;
            $widget->save();
        }

        if(isset($request->three_c_banner_image2))
        {
            if (!file_exists(public_path('frontend/images/banners/'))) {
                mkdir(public_path('frontend/images/banners/'), 0777, true);
            }
            $three_c_banner_image2 = $request->three_c_banner_image2;
            $ext = pathinfo($three_c_banner_image2->getClientOriginalName(), PATHINFO_EXTENSION);
            $imageName = date("Ymdhis") . 2;
            if(!config('database.connections.saleprosaas_landlord')) {
                $imageName = $imageName . '.' . $ext;
            }
            else {
                $imageName = $this->getTenantId() . '_' . $imageName . '.' . $ext;
            }

            $three_c_banner_image2->move(public_path('frontend/images/banners'), $imageName);

            $manager = new ImageManager(Driver::class);
            $image = $manager->read(public_path('frontend/images/banners/'). $imageName);
            $image->cover(500, 200)->save(public_path('frontend/images/banners/'). $imageName, 100);

            $widget->three_c_banner_image2 = $imageName;
            $widget->save();
        }

        if(isset($request->three_c_banner_image3))
        {
            if (!file_exists(public_path('frontend/images/banners/'))) {
                mkdir(public_path('frontend/images/banners/'), 0777, true);
            }
            $three_c_banner_image3 = $request->three_c_banner_image3;
            $ext = pathinfo($three_c_banner_image3->getClientOriginalName(), PATHINFO_EXTENSION);
            $imageName = date("Ymdhis") . 3;
            if(!config('database.connections.saleprosaas_landlord')) {
                $imageName = $imageName . '.' . $ext;
            }
            else {
                $imageName = $this->getTenantId() . '_' . $imageName . '.' . $ext;
            }

            $three_c_banner_image3->move(public_path('frontend/images/banners'), $imageName);

            $manager = new ImageManager(Driver::class);
            $image = $manager->read(public_path('frontend/images/banners/'). $imageName);
            $image->cover(500, 200)->save(public_path('frontend/images/banners/'). $imageName, 100);

            $widget->three_c_banner_image3 = $imageName;
            $widget->save();
        }

        if(isset($request->two_c_banner_image1))
        {
            if (!file_exists(public_path('frontend/images/banners/'))) {
                mkdir(public_path('frontend/images/banners/'), 0777, true);
            }
            $two_c_banner_image1 = $request->two_c_banner_image1;
            $ext = pathinfo($two_c_banner_image1->getClientOriginalName(), PATHINFO_EXTENSION);
            $imageName = date("Ymdhis") . 1;
            if(!config('database.connections.saleprosaas_landlord')) {
                $imageName = $imageName . '.' . $ext;
            }
            else {
                $imageName = $this->getTenantId() . '_' . $imageName . '.' . $ext;
            }

            $two_c_banner_image1->move(public_path('frontend/images/banners'), $imageName);

            $manager = new ImageManager(Driver::class);
            $image = $manager->read(public_path('frontend/images/banners/'). $imageName);
            $image->cover(700, 150)->save(public_path('frontend/images/banners/'). $imageName, 100);

            $widget->two_c_banner_image1 = $imageName;
            $widget->save();
        }

        if(isset($request->two_c_banner_image2))
        {
            if (!file_exists(public_path('frontend/images/banners/'))) {
                mkdir(public_path('frontend/images/banners/'), 0777, true);
            }
            $two_c_banner_image2 = $request->two_c_banner_image2;
            $ext = pathinfo($two_c_banner_image2->getClientOriginalName(), PATHINFO_EXTENSION);
            $imageName = date("Ymdhis") . 2;
            if(!config('database.connections.saleprosaas_landlord')) {
                $imageName = $imageName . '.' . $ext;
            }
            else {
                $imageName = $this->getTenantId() . '_' . $imageName . '.' . $ext;
            }

            $two_c_banner_image2->move(public_path('frontend/images/banners'), $imageName);

            $manager = new ImageManager(Driver::class);
            $image = $manager->read(public_path('frontend/images/banners/'). $imageName);
            $image->cover(700, 150)->save(public_path('frontend/images/banners/'). $imageName, 100);

            $widget->two_c_banner_image2 = $imageName;
            $widget->save();
        }

        if(isset($request->one_c_banner_image1))
        {
            if (!file_exists(public_path('frontend/images/banners/'))) {
                mkdir(public_path('frontend/images/banners/'), 0777, true);
            }
            if (!file_exists(public_path('frontend/images/banners/desktop/'))) {
                mkdir(public_path('frontend/images/banners/desktop/'), 0777, true);
            }
            if (!file_exists(public_path('frontend/images/banners/mobile/'))) {
                mkdir(public_path('frontend/images/banners/mobile/'), 0777, true);
            }
            $one_c_banner_image1 = $request->one_c_banner_image1;
            $ext = pathinfo($one_c_banner_image1->getClientOriginalName(), PATHINFO_EXTENSION);
            $imageName = date("Ymdhis") . rand(0,3);
            if(!config('database.connections.saleprosaas_landlord')) {
                $imageName = $imageName . '.' . $ext;
            }
            else {
                $imageName = $this->getTenantId() . '_' . $imageName . '.' . $ext;
            }

            $one_c_banner_image1->move(public_path('frontend/images/banners/desktop'), $imageName);

            $manager = new ImageManager(Driver::class);
            $image = $manager->read(public_path('frontend/images/banners/desktop/'). $imageName);
            $image->cover(1900, 900)->save(public_path('frontend/images/banners/desktop/'). $imageName, 100);

            if(isset($request->one_c_banner_image1_mobile))
            {
                $one_c_banner_image1_mobile = $request->one_c_banner_image1_mobile;
                $ext = pathinfo($one_c_banner_image1_mobile->getClientOriginalName(), PATHINFO_EXTENSION);

                $one_c_banner_image1_mobile->move(public_path('frontend/images/banners/mobile'), $imageName);

                $manager = new ImageManager(Driver::class);
                $image = $manager->read(public_path('frontend/images/banners/mobile/'). $imageName);
                $image->cover(600, 750)->save(public_path('frontend/images/banners/mobile/'). $imageName, 100);

            }

            $widget->one_c_banner_image1 = $imageName;
            $widget->save();
        }

        return response(['id' => $widget->id]);
    }

    public function delete($id)
    {
        if(!env('USER_VERIFIED')){
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');
        }
        
        $widget = PageWidgets::where('id',$id)->delete();

        // if(isset($widget->icon))
        // {
        //     $this->fileDelete('frontend/images/features/', $widget->icon);
        // }

        return redirect()->back();
    }
}
