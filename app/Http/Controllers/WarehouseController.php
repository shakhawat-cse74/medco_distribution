<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\Product;
use App\Models\Warehouse;
use App\Traits\CacheForget;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Product_Warehouse;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class WarehouseController extends Controller
{
    use CacheForget;

    private function checkPermission($permission)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo($permission)) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
    }

    public function index()
    {
        if ($response = $this->checkPermission('warehouse-index')) {
            return $response;
        }

        $lims_warehouse_list = Warehouse::all();
        $numberOfWarehouse = Warehouse::count();
        return view('backend.warehouse.create', compact('lims_warehouse_list', 'numberOfWarehouse'));
    }

    public function warehouseData(Request $request)
    {
        if ($response = $this->checkPermission('warehouse-index')) {
            return $response;
        }
        $columns = [
            1 => 'warehouses.name',
            2 => 'warehouses.phone',
            3 => 'warehouses.email',
            4 => 'warehouses.address',
            5 => 'warehouses.is_active',
        ];
    
        $totalData = Warehouse::count();
        $totalFiltered = $totalData;
    
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')] ?? 'warehouses.id';
        $dir = $request->input('order.0.dir', 'asc');
    
        $query = Warehouse::select('warehouses.*');
    
        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('warehouses.name', 'LIKE', "%{$search}%")
                  ->orWhere('warehouses.phone', 'LIKE', "%{$search}%")
                  ->orWhere('warehouses.email', 'LIKE', "%{$search}%")
                  ->orWhere('warehouses.address', 'LIKE', "%{$search}%");
            });
    
            $totalFiltered = (clone $query)->count();
        }
    
        $warehouses = $query
            ->orderBy($order, $dir)
            ->offset($start)
            ->limit($limit)
            ->get();
    
        $data = [];
    
        foreach ($warehouses as $warehouse) {
    
            $action = view('backend.warehouse.partials.action-buttons', compact('warehouse'))->render();
    
            $data[] = [
                'id' => $warehouse->id,
                'name' => $warehouse->name,
                'phone' => $warehouse->phone,
                'email' => $warehouse->email,
                'address' => $warehouse->address,
                'is_active' => (bool) $warehouse->is_active,
                'action' => $action,
            ];
        }
    
        return response()->json([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => $totalData,
            "recordsFiltered" => $totalFiltered,
            "data" => $data,
        ]);
    }

    public function store(Request $request)
    {
        if ($response = $this->checkPermission('warehouse-add')) {
            return $response;
        }
        $this->validate($request, [
            'name' => [
                'max:255',
                    Rule::unique('warehouses')->where(function ($query) {
                    return $query->where('is_active', 1);
                }),
            ],
        ]);
        $input = $request->all();
        $input['is_active'] = true;

        $lims_warehouse_data = Warehouse::create($input);

        $lims_product_data = Product::pluck('id');
        foreach ($lims_product_data as $product) {
            Product_Warehouse::create([
                'product_id' => $product,
                'warehouse_id' => $lims_warehouse_data->id,
                'qty' => 0
            ]);
        }
        $this->cacheForget('warehouse_list');

        // if ajax - from create purchase page
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'id'      => $lims_warehouse_data->id,
                'name'    => $lims_warehouse_data->name, // Make sure 'name' exists on your model
            ]);
        }
        
        return redirect('warehouse')->with('message', __('db.Data inserted successfully'));
    }

    public function edit($id)
    {
        if ($response = $this->checkPermission('warehouse-edit')) {
            return $response;
        }
        $lims_warehouse_data = Warehouse::findOrFail($id);
        return $lims_warehouse_data;
    }

    public function update(Request $request, $id)
    {
        if ($response = $this->checkPermission('warehouse-edit')) {
            return $response;
        }
        $this->validate($request, [
            'name' => [
                'max:255',
                    Rule::unique('warehouses')->ignore($request->warehouse_id)->where(function ($query) {
                    return $query->where('is_active', 1);
                }),
            ],
        ]);
        $input = $request->all();
        $lims_warehouse_data = Warehouse::find($input['warehouse_id']);
        $lims_warehouse_data->update($input);
        $this->cacheForget('warehouse_list');
        return redirect('warehouse')->with('message', __('db.Data updated successfully'));
    }

    public function importWarehouse(Request $request)
    {
        if ($response = $this->checkPermission('warehouse-add')) {
            return $response;
        }
        //get file
        $upload=$request->file('file');
        $ext = pathinfo($upload->getClientOriginalName(), PATHINFO_EXTENSION);
        if($ext != 'csv')
            return redirect()->back()->with('not_permitted', __('db.Please upload a CSV file'));
        $filename =  $upload->getClientOriginalName();
        $upload=$request->file('file');
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
        //looping through othe columns
        while($columns=fgetcsv($file))
        {
            if($columns[0]=="")
                continue;
            foreach ($columns as $key => $value) {
                $value=preg_replace('/\D/','',$value);
            }
           $data= array_combine($escapedHeader, $columns);

           $warehouse = Warehouse::firstOrNew([ 'name'=>$data['name'], 'is_active'=>true ]);
           $warehouse->name = $data['name'];
           $warehouse->phone = $data['phone'];
           $warehouse->email = $data['email'];
           $warehouse->address = $data['address'];
           $warehouse->is_active = true;
           $warehouse->save();
        }
        $this->cacheForget('warehouse_list');
        return redirect('warehouse')->with('message', __('db.Warehouse imported successfully'));
    }

    public function deleteBySelection(Request $request)
    {
        if ($response = $this->checkPermission('warehouse-delete')) {
            return $response;
        }
        $warehouse_id = $request['warehouseIdArray'];
        foreach ($warehouse_id as $id) {
            $lims_warehouse_data = Warehouse::find($id);
            $lims_warehouse_data->deactivate();
        }
        $this->cacheForget('warehouse_list');
        return __('db.Data deleted successfully');
    }

    public function destroy($id)
    {
        if ($response = $this->checkPermission('warehouse-delete')) {
            return $response;
        }
        $lims_warehouse_data = Warehouse::find($id);
        $lims_warehouse_data->deactivate();
        $this->cacheForget('warehouse_list');
        return redirect('warehouse')->with('not_permitted', __('db.Data deleted successfully'));
    }

    public function warehouseAll()
    {
        if(Auth::user()->role_id > 2)
            $lims_warehouse_list = DB::table('warehouses')->where([
            ['is_active', true],
            ['id', Auth::user()->warehouse_id]
        ])->get();
        else
            $lims_warehouse_list = DB::table('warehouses')->where('is_active', true)->get();

        $html = '';
        foreach($lims_warehouse_list as $warehouse){
            $html .='<option value="'.$warehouse->id.'">'.$warehouse->name.'</option>';
        }

        return response()->json($html);
    }
}
