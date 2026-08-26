<?php

namespace Modules\DeliveryManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\Product_Warehouse;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WarehouseProductController extends Controller
{
    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('warehouse-products-index')) {
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if (empty($all_permission))
                $all_permission[] = 'dummy text';

            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_product_list = Product::where('is_active', true)->get();

            return view('backend.delivery_management.warehouse_product.index', compact('lims_warehouse_list', 'lims_product_list', 'all_permission'));
        } else {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
    }

    public function warehouseProductData(Request $request)
    {
        $columns = array(
            0 => 'id',
            1 => 'warehouse_id',
            2 => 'product_id',
            3 => 'qty',
            4 => 'price',
        );

        $totalData = Product_Warehouse::count();
        $totalFiltered = $totalData;

        if ($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')] ?? 'id';
        $dir = $request->input('order.0.dir') ?? 'asc';

        $query = Product_Warehouse::query()->with(['product', 'warehouse']);

        if (!empty($request->input('search.value'))) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->whereHas('product', function ($q2) use ($search) {
                    $q2->where('name', 'LIKE', "%{$search}%")
                       ->orWhere('code', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('warehouse', function ($q2) use ($search) {
                    $q2->where('name', 'LIKE', "%{$search}%");
                });
            });
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        $warehouseProducts = $query->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir)
            ->get();

        $totalFiltered = $query->count();

        $data = array();
        if (!empty($warehouseProducts)) {
            foreach ($warehouseProducts as $key => $wp) {
                $nestedData['id'] = $wp->id;
                $nestedData['key'] = $key;
                $nestedData['warehouse'] = $wp->warehouse ? $wp->warehouse->name : 'N/A';
                $nestedData['product'] = $wp->product ? $wp->product->name . ' (' . ($wp->product->code ?? 'N/A') . ')' : 'N/A';
                $nestedData['qty'] = $wp->qty;
                $nestedData['price'] = number_format($wp->price, 2);
                $nestedData['options'] = '<div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.__("db.action").'
                              <span class="caret"></span>
                              <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                                <li>
                                    <button type="button" data-id="'.$wp->id.'" class="open-EditCategoryDialog btn btn-link" data-toggle="modal" data-target="#editModal" ><i class="ti ti-edit"></i> '.__("db.edit").'</button>
                                </li>
                                <li class="divider"></li>
                                <form action="' . route("warehouse-products.destroy", $wp->id) . '" method="POST">'.csrf_field().'' . method_field("DELETE") . '
                                <li>
                                  <button type="submit" class="btn btn-link confirm-delete-btn" data-id="'.$wp->id.'" data-name="'.$wp->product->name ?? 'Product'.'"><i class="ti ti-trash"></i> '.__("db.delete").'</button>
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

    public function create()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('warehouse-products-add')) {
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_product_list = Product::where('is_active', true)->get();

            return view('backend.delivery_management.warehouse_product.create', compact('lims_warehouse_list', 'lims_product_list'));
        } else {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
    }

    public function store(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('warehouse-products-add')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $existing = Product_Warehouse::where([
                'product_id' => $request->product_id,
                'warehouse_id' => $request->warehouse_id,
            ])->first();

            if ($existing) {
                $existing->qty += $request->qty;
                $existing->price = $request->price;
                $existing->save();
            } else {
                Product_Warehouse::create([
                    'product_id' => $request->product_id,
                    'warehouse_id' => $request->warehouse_id,
                    'qty' => $request->qty,
                    'price' => $request->price,
                ]);
            }

            DB::commit();
            return redirect()->route('warehouse-products.index')->with('message', __('db.Warehouse product added successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Warehouse product creation failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Failed to add warehouse product: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('warehouse-products-edit')) {
            $lims_warehouse_product = Product_Warehouse::with(['product', 'warehouse'])->findOrFail($id);
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_product_list = Product::where('is_active', true)->get();

            return view('backend.delivery_management.warehouse_product.edit', compact('lims_warehouse_product', 'lims_warehouse_list', 'lims_product_list'));
        } else {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
    }

    public function update(Request $request, $id)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('warehouse-products-edit')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $warehouseProduct = Product_Warehouse::findOrFail($id);
            $warehouseProduct->update([
                'warehouse_id' => $request->warehouse_id,
                'product_id' => $request->product_id,
                'qty' => $request->qty,
                'price' => $request->price,
            ]);

            DB::commit();
            return redirect()->route('warehouse-products.index')->with('message', __('db.Warehouse product updated successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Warehouse product update failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Update failed: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('warehouse-products-delete')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        try {
            DB::beginTransaction();
            Product_Warehouse::findOrFail($id)->delete();
            DB::commit();
            return redirect()->back()->with('message', __('db.Warehouse product deleted successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Warehouse product deletion failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Deletion failed: ' . $e->getMessage());
        }
    }

    public function deleteBySelection(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('warehouse-products-delete')) {
            return response()->json(['success' => false, 'message' => 'Not permitted']);
        }

        try {
            DB::beginTransaction();
            $ids = $request->warehouseProductIdArray ?? [];
            Product_Warehouse::whereIn('id', $ids)->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Selected warehouse products deleted']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk warehouse product deletion failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Deletion failed']);
        }
    }
}
