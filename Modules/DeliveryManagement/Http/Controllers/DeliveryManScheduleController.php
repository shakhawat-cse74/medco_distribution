<?php

namespace Modules\DeliveryManagement\Http\Controllers;

use App\Http\Controllers\Controller;

use Modules\DeliveryManagement\Models\DeliveryManSchedule;
use Modules\DeliveryManagement\Models\DeliveryMan;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\CacheForget;

class DeliveryManScheduleController extends Controller
{
    use \App\Traits\CacheForget;

    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('delivery-man-schedules-index')) {
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if (empty($all_permission))
                $all_permission[] = 'dummy text';

            $lims_schedule_list = DeliveryManSchedule::with('deliveryMan')->get();
            $lims_delivery_man_list = DeliveryMan::active()->get();

            return view('backend.delivery_management.delivery_man_schedule.index', compact('lims_schedule_list', 'lims_delivery_man_list', 'all_permission'));
        } else {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
    }

    public function store(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('delivery-man-schedules-add')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $this->validate($request, [
            'delivery_man_id' => 'required|exists:delivery_men,id',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        $data = $request->all();
        $data['created_by'] = Auth::id();

        try {
            DB::beginTransaction();
            DeliveryManSchedule::create($data);
            DB::commit();
            $this->cacheForget('delivery_man_schedule_list');

            return redirect('delivery-man-schedules')->with('message', __('db.Schedule created successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Schedule creation failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Schedule creation failed: ' . $e->getMessage());
        }
    }

    public function update($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('delivery-man-schedules-edit')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $lims_schedule_data = DeliveryManSchedule::findOrFail($id);
        $data = request()->all();

        $this->validate(request(), [
            'delivery_man_id' => 'required|exists:delivery_men,id',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        try {
            DB::beginTransaction();
            $lims_schedule_data->update($data);
            DB::commit();
            $this->cacheForget('delivery_man_schedule_list');

            return redirect('delivery-man-schedules')->with('message', __('db.Schedule updated successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Schedule update failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Schedule update failed: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('delivery-man-schedules-delete')) {
            try {
                DB::beginTransaction();
                $lims_schedule_data = DeliveryManSchedule::findOrFail($id);
                $lims_schedule_data->delete();
                DB::commit();
                $this->cacheForget('delivery_man_schedule_list');

                return redirect('delivery-man-schedules')->with('message', __('db.Schedule deleted successfully'));
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Schedule deletion failed: ' . $e->getMessage());
                return redirect()->back()->with('not_permitted', 'Schedule deletion failed: ' . $e->getMessage());
            }
        } else {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
    }

    public function getByDeliveryMan($delivery_man_id)
    {
        $schedules = DeliveryManSchedule::where('delivery_man_id', $delivery_man_id)
            ->where('is_active', true)
            ->get();

        return response()->json($schedules);
    }

    public function calendar()
    {
        $lims_schedule_list = DeliveryManSchedule::with('deliveryMan')->get();
        return view('backend.delivery_management.delivery_man_schedule.calendar', compact('lims_schedule_list'));
    }
}
