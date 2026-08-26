<?php

namespace Modules\DeliveryManagement\Http\Controllers;

use App\Http\Controllers\Controller;

use Modules\DeliveryManagement\Models\DeliveryManAssignment;
use Modules\DeliveryManagement\Models\DeliveryMan;
use App\Models\Warehouse;
use Modules\DeliveryManagement\Models\DeliveryManRoute;
use App\Models\Area;
use App\Models\Customer;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeliveryManAssignmentController extends Controller
{
    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('delivery-man-assignments-index')) {
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if (empty($all_permission))
                $all_permission[] = 'dummy text';

            $lims_assignment_list = DeliveryManAssignment::with(['deliveryMan', 'warehouse', 'route'])->get();
            $lims_delivery_man_list = DeliveryMan::where('is_active', true)->get();
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_route_list = DeliveryManRoute::where('is_active', true)->get();

            return view('backend.delivery_management.delivery_man_assignment.index', compact('lims_assignment_list', 'lims_delivery_man_list', 'lims_warehouse_list', 'lims_route_list', 'all_permission'));
        } else {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
    }

    public function store(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('delivery-man-assignments-add')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $this->validate($request, [
            'delivery_man_id' => 'required|exists:delivery_men,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'route_id' => 'nullable|exists:delivery_man_routes,id',
            'area_id' => 'nullable|exists:areas,id',
        ]);

        $data = $request->all();
        $data['created_by'] = Auth::id();

        try {
            DB::beginTransaction();
            DeliveryManAssignment::create($data);
            DB::commit();

            return redirect('delivery-man-assignments')->with('message', __('db.Delivery man assignment created successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delivery man assignment creation failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Assignment creation failed: ' . $e->getMessage());
        }
    }

    public function update($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('delivery-man-assignments-edit')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $lims_assignment_data = DeliveryManAssignment::findOrFail($id);
        $data = request()->all();

        try {
            DB::beginTransaction();
            $lims_assignment_data->update($data);
            DB::commit();

            return redirect('delivery-man-assignments')->with('message', __('db.Delivery man assignment updated successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delivery man assignment update failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Assignment update failed: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('delivery-man-assignments-delete')) {
            try {
                DB::beginTransaction();
                $lims_assignment_data = DeliveryManAssignment::findOrFail($id);
                $lims_assignment_data->delete();
                DB::commit();

                return redirect('delivery-man-assignments')->with('message', __('db.Delivery man assignment deleted successfully'));
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Delivery man assignment deletion failed: ' . $e->getMessage());
                return redirect()->back()->with('not_permitted', 'Assignment deletion failed: ' . $e->getMessage());
            }
        } else {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
    }

    public function getDeliveryMenByWarehouse($warehouse_id)
    {
        $delivery_men = DeliveryMan::where('warehouse_id', $warehouse_id)->where('is_active', true)->get(['id', 'name']);
        return response()->json($delivery_men);
    }

    public function getDeliveryMenByRoute($route_id)
    {
        $assignment = DeliveryManAssignment::where('route_id', $route_id)->with('deliveryMan')->get();
        $delivery_men = $assignment->map(function ($item) {
            return $item->deliveryMan;
        });
        return response()->json($delivery_men);
    }

    public function getDeliveryMenByArea($area_id)
    {
        $assignment = DeliveryManAssignment::where('area_id', $area_id)->with('deliveryMan')->get();
        $delivery_men = $assignment->map(function ($item) {
            return $item->deliveryMan;
        });
        return response()->json($delivery_men);
    }
}
