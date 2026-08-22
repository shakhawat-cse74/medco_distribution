<?php

namespace Modules\Ecommerce\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class NewsletterController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
          // existing table থেকে data আনা
        $data['newsletters'] = DB::table('newsletter')
            ->orderBy('id', 'desc')
            ->get();
        return view('ecommerce::backend.newsletter.index')->with($data);
    }

    
   public function destroy($id)
{
    DB::table('newsletter')->where('id', $id)->delete();

    return redirect()->back()->with([
        'message' => 'Newsletter deleted successfully!',
        'type' => 'success'
    ]);
}
}
