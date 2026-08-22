<?php

namespace Modules\Ecommerce\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Session;
use DB;

class FaqController extends Controller
{

    public function index()
    {

        $faqs = DB::table('faqs')->get();
        $categories = DB::table('faq_categories')->get();

        return view('ecommerce::backend.faq.index', compact('faqs','categories'));
    }

    public function store(Request $request)
    {
        if(!env('USER_VERIFIED')){
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');
        }

        $data = $request->except('_token');

        if (DB::table('faqs')->insert($data)) {
            Session::flash('message', 'FAQ saved successfully.');
            Session::flash('type', 'success');
            return redirect()->back();
        } else {
            Session::flash('message', 'Failed to save FAQ.');
            Session::flash('type', 'danger');
            return redirect()->back();
        }
    }

    public function edit($id)
    {
        $faq = DB::table('faqs')->find($id);
        return $faq;
    }

    public function update(Request $request)
    {
        if(!env('USER_VERIFIED')){
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');
        }

        $faq = DB::table('faqs')->where('id',$request->hidden_id)->update(['question' => $request->question, 'answer' => $request->answer, 'order' => $request->order]);
        Session::flash('message', 'FAQ saved successfully.');
        Session::flash('type', 'success');
        return redirect()->back();
    }

    public function destroy(Request $request)
    {
        if(!env('USER_VERIFIED')){
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');
        }
        
        DB::table('faqs')->where('id',$request->id)->delete();
        Session::flash('message', 'FAQ deleted successfully.');
        Session::flash('type', 'success');
        return redirect()->back();
    }
}
