<?php

namespace Modules\Restaurant\Http\Controllers;

use Modules\Restaurant\Entities\Floors;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Session;
use DB; 

class FloorController extends Controller
{

    public function index()
    {
        $floors = Floors::all();
        $locations = DB::table('warehouses')->where('is_active', true)->get();
        return view('restaurant::backend.floor.index', compact('floors', 'locations'));
    }

    public function store(Request $request)
    {
        if(!config('app.user_verified')){
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');
        }
        $data = $request->all();

        if (Floors::create($data)) {
            $newdata = Floors::orderby('id', 'DESC')->first();
            Session::flash('message', 'Floor saved successfully.');
            Session::flash('type', 'success');
            return redirect()->back();
        } else {
            Session::flash('message', 'Failed to save floor.');
            Session::flash('type', 'danger');
            return redirect()->back();
        }
    }

    public function edit($id)
    {
        $floor = Floors::find($id);
        return $floor;
    }

    public function update(Request $request)
    {
        if(!config('app.user_verified')){
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');
        }
        $floor = Floors::findOrFail($request->floorid);

        $floor->name = $request->name;
        $floor->warehouse_id = $request->warehouse_id;
        $floor->save();

        Session::flash('message', 'Floor saved successfully.');
        Session::flash('type', 'success');
        return redirect()->back();
    }

    public function destroy(Request $request)
    {
        if(!config('app.user_verified')){
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');
        }

        Table::where('floor_id', $request->id)->update(['is_active'=>0]);
        Floors::findOrFail($request->id)->delete();

        Session::flash('message', 'Floor deleted successfully.');
        Session::flash('type', 'success');
        return redirect()->back();
    }

    public function floorplan($id)
    {
        $floor = Floors::find($id);
        $tables = Table::where('floor_id',$id)->get();
        return view('restaurant::backend.floor.plan', compact('floor','tables'));
    }

    public function updateFloorplan(Request $request)
    {
        if(!config('app.user_verified')){
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');
        }

        $floor = Floors::find($request->input('floor_id'));

        $floor->floorplan = $request->input('plan'); // Save Base64 data
        $floor->save();

        return response()->json(['success' => true, 'message' => 'Floor plan saved successfully']);
    }
}
