<?php

namespace Modules\Ecommerce\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Session;
use DB;

class FaqCategoriesController extends Controller
{

    public function index()
    {

        $categories = DB::table('faq_categories')->get();

        return view('ecommerce::backend.faq-category.index', compact('categories'));
    }

    public function store(Request $request)
    {
        if(!env('USER_VERIFIED')){
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');
        }

        $data = $request->except('_token');

        if (DB::table('faq_categories')->insert($data)) {
            Session::flash('message', 'FAQ category saved successfully.');
            Session::flash('type', 'success');
            return redirect()->back();
        } else {
            Session::flash('message', 'Failed to save FAQ category.');
            Session::flash('type', 'danger');
            return redirect()->back();
        }
    }

    public function edit($id)
    {
        $category = DB::table('faq_categories')->find($id);
        return $category;
    }

    public function update(Request $request)
    {
        if(!env('USER_VERIFIED')){
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');
        }

        $category = DB::table('faq_categories')->where('id',$request->hidden_id)->update(['name' => $request->name, 'order' => $request->order]);
        Session::flash('message', 'FAQ category saved successfully.');
        Session::flash('type', 'success');
        return redirect()->back();
    }

    public function destroy(Request $request)
    {
        if(!env('USER_VERIFIED')){
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');
        }
        
        DB::table('faqs')->where('category_id', $request->id)->delete();
        DB::table('faq_categories')->where('id',$request->id)->delete();
        Session::flash('message', 'FAQ category deleted successfully.');
        Session::flash('type', 'success');
        return redirect()->back();
    }
}
