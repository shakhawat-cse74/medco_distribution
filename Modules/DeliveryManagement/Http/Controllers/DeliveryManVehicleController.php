<?php

namespace Modules\DeliveryManagement\Http\Controllers;

use App\Http\Controllers\Controller;

use Modules\DeliveryManagement\Models\DeliveryManVehicle;
use Modules\DeliveryManagement\Models\DeliveryMan;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeliveryManVehicleController extends Controller
{
    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('delivery-man-vehicles-index')) {
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if (empty($all_permission))
                $all_permission[] = 'dummy text';

            $lims_vehicle_list = DeliveryManVehicle::with('deliveryMan')->get();
            $lims_delivery_man_list = DeliveryMan::active()->get();

            return view('backend.delivery_management.delivery_man_vehicle.index', compact('lims_vehicle_list', 'lims_delivery_man_list', 'all_permission'));
        } else {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
    }

    public function store(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('delivery-man-vehicles-add')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $this->validate($request, [
            'delivery_man_id' => 'required|exists:delivery_men,id',
            'vehicle_type' => 'required|max:255',
            'vehicle_number' => 'required|max:255',
        ]);

        $data = $request->all();

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $ext = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
            $imageName = date("Ymdhis");
            if (!config('database.connections.saleprosaas_landlord')) {
                $imageName = $imageName . '.' . $ext;
                $image->move(public_path('images/delivery_man_vehicle'), $imageName);
            } else {
                $imageName = 'tenant_' . $imageName . '.' . $ext;
                $image->move(public_path('images/delivery_man_vehicle'), $imageName);
            }
            $data['image'] = $imageName;
        }

        try {
            DB::beginTransaction();
            DeliveryManVehicle::create($data);
            DB::commit();

            return redirect('delivery-man-vehicles')->with('message', __('db.Vehicle created successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Vehicle creation failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Vehicle creation failed: ' . $e->getMessage());
        }
    }

    public function update($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('delivery-man-vehicles-edit')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $lims_vehicle_data = DeliveryManVehicle::findOrFail($id);
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $this->fileDelete(public_path('images/delivery_man_vehicle/'), $lims_vehicle_data->image);
            $image = $request->file('image');
            $ext = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
            $imageName = date("Ymdhis");
            if (!config('database.connections.saleprosaas_landlord')) {
                $imageName = $imageName . '.' . $ext;
                $image->move(public_path('images/delivery_man_vehicle'), $imageName);
            } else {
                $imageName = 'tenant_' . $imageName . '.' . $ext;
                $image->move(public_path('images/delivery_man_vehicle'), $imageName);
            }
            $data['image'] = $imageName;
        }

        try {
            DB::beginTransaction();
            $lims_vehicle_data->update($data);
            DB::commit();

            return redirect('delivery-man-vehicles')->with('message', __('db.Vehicle updated successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Vehicle update failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Vehicle update failed: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('delivery-man-vehicles-delete')) {
            try {
                DB::beginTransaction();
                $lims_vehicle_data = DeliveryManVehicle::findOrFail($id);
                $this->fileDelete(public_path('images/delivery_man_vehicle/'), $lims_vehicle_data->image);
                $lims_vehicle_data->delete();
                DB::commit();

                return redirect('delivery-man-vehicles')->with('message', __('db.Vehicle deleted successfully'));
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Vehicle deletion failed: ' . $e->getMessage());
                return redirect()->back()->with('not_permitted', 'Vehicle deletion failed: ' . $e->getMessage());
            }
        } else {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
    }
}
