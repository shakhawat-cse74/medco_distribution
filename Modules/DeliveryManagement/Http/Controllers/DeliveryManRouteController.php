<?php

namespace Modules\DeliveryManagement\Http\Controllers;

use App\Http\Controllers\Controller;

use Modules\DeliveryManagement\Models\DeliveryManRoute;
use App\Models\Warehouse;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeliveryManRouteController extends Controller
{
    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('delivery-man-routes-index')) {
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if (empty($all_permission))
                $all_permission[] = 'dummy text';

            $lims_route_list = DeliveryManRoute::with('warehouse')->get();
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();

            return view('backend.delivery_management.delivery_man_route.index', compact('lims_route_list', 'lims_warehouse_list', 'all_permission'));
        } else {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
    }

    public function store(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('delivery-man-routes-add')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $this->validate($request, [
            'name' => 'required|max:255',
            'code' => 'required|max:255|unique:delivery_man_routes,code',
        ]);

        $data = $request->all();
        $data['created_by'] = Auth::id();

        try {
            DB::beginTransaction();
            DeliveryManRoute::create($data);
            DB::commit();

            return redirect('delivery-man-routes')->with('message', __('db.Delivery man route created successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delivery man route creation failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Route creation failed: ' . $e->getMessage());
        }
    }

    public function update($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('delivery-man-routes-edit')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $lims_route_data = DeliveryManRoute::findOrFail($id);
        $data = request()->all();

        $this->validate(request(), [
            'name' => 'required|max:255',
            'code' => 'required|max:255|unique:delivery_man_routes,code,' . $id,
        ]);

        try {
            DB::beginTransaction();
            $lims_route_data->update($data);
            DB::commit();

            return redirect('delivery-man-routes')->with('message', __('db.Delivery man route updated successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delivery man route update failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Route update failed: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('delivery-man-routes-delete')) {
            try {
                DB::beginTransaction();
                $lims_route_data = DeliveryManRoute::findOrFail($id);
                $lims_route_data->delete();
                DB::commit();

                return redirect('delivery-man-routes')->with('message', __('db.Delivery man route deleted successfully'));
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Delivery man route deletion failed: ' . $e->getMessage());
                return redirect()->back()->with('not_permitted', 'Route deletion failed: ' . $e->getMessage());
            }
        } else {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
    }

    public function assignDeliveryMan(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('delivery-man-routes-edit')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $this->validate($request, [
            'route_id' => 'required|exists:delivery_man_routes,id',
            'delivery_man_id' => 'required|exists:delivery_men,id',
        ]);

        try {
            DB::beginTransaction();
            $assignment = DeliveryManAssignment::updateOrCreate(
                ['route_id' => $request->route_id, 'delivery_man_id' => $request->delivery_man_id],
                ['created_by' => Auth::id()]
            );
            DB::commit();

            return redirect('delivery-man-routes')->with('message', __('db.Delivery man assigned to route successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delivery man route assignment failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Route assignment failed: ' . $e->getMessage());
        }
    }
}
