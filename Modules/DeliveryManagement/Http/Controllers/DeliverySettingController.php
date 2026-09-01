<?php

namespace Modules\DeliveryManagement\Http\Controllers;

use App\Http\Controllers\Controller;

use Modules\DeliveryManagement\Models\DeliverySetting;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\CacheForget;

class DeliverySettingController extends Controller
{
    use \App\Traits\CacheForget;

    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('delivery-settings-index')) {
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if (empty($all_permission))
                $all_permission[] = 'dummy text';

            $lims_settings = DeliverySetting::all();

            return view('backend.delivery_management.delivery_setting.index', compact('lims_settings', 'all_permission'));
        } else {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
    }

    public function update(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('delivery-settings-edit')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $data = $request->all();

        try {
            DB::beginTransaction();
            foreach ($data as $key => $value) {
                if ($key != '_token') {
                    DeliverySetting::updateOrCreate(
                        ['key' => $key],
                        ['value' => $value, 'type' => 'general']
                    );
                }
            }
            DB::commit();
            $this->cacheForget('delivery_settings');

            return redirect('delivery-settings')->with('message', __('db.Settings updated successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Settings update failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Settings update failed: ' . $e->getMessage());
        }
    }

    public function commissionSettings()
    {
        $lims_commission_settings = DeliverySetting::where('key', 'LIKE', 'commission_%')->get();
        return view('backend.delivery_management.delivery_setting.commission_settings', compact('lims_commission_settings'));
    }

    public function updateCommissionSettings(Request $request)
    {
        $data = $request->all();

        try {
            DB::beginTransaction();
            foreach ($data as $key => $value) {
                if ($key != '_token') {
                    DeliverySetting::updateOrCreate(
                        ['key' => $key],
                        ['value' => $value, 'type' => 'commission']
                    );
                }
            }
            DB::commit();
            $this->cacheForget('delivery_settings');

            return redirect('delivery-settings/commission-settings')->with('message', __('db.Commission settings updated successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Commission settings update failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Commission settings update failed: ' . $e->getMessage());
        }
    }

    public function routeSettings()
    {
        $lims_route_settings = DeliverySetting::where('key', 'LIKE', 'route_%')->get();
        return view('backend.delivery_management.delivery_setting.route_settings', compact('lims_route_settings'));
    }

    public function updateRouteSettings(Request $request)
    {
        $data = $request->all();

        try {
            DB::beginTransaction();
            foreach ($data as $key => $value) {
                if ($key != '_token') {
                    DeliverySetting::updateOrCreate(
                        ['key' => $key],
                        ['value' => $value, 'type' => 'route']
                    );
                }
            }
            DB::commit();
            $this->cacheForget('delivery_settings');

            return redirect('delivery-settings/route-settings')->with('message', __('db.Route settings updated successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Route settings update failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Route settings update failed: ' . $e->getMessage());
        }
    }

    public function deliveryChargeSettings()
    {
        $lims_charge_settings = DeliverySetting::where('key', 'LIKE', 'delivery_charge_%')->get();
        return view('backend.delivery_management.delivery_setting.delivery_charge_settings', compact('lims_charge_settings'));
    }

    public function updateDeliveryChargeSettings(Request $request)
    {
        $data = $request->all();

        try {
            DB::beginTransaction();
            foreach ($data as $key => $value) {
                if ($key != '_token') {
                    DeliverySetting::updateOrCreate(
                        ['key' => $key],
                        ['value' => $value, 'type' => 'delivery_charge']
                    );
                }
            }
            DB::commit();
            $this->cacheForget('delivery_settings');

            return redirect('delivery-settings/delivery-charge-settings')->with('message', __('db.Delivery charge settings updated successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delivery charge settings update failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Delivery charge settings update failed: ' . $e->getMessage());
        }
    }

    public function timeSlotSettings()
    {
        $lims_time_slot_settings = DeliverySetting::where('key', 'LIKE', 'time_slot_%')->get();
        return view('backend.delivery_management.delivery_setting.time_slot_settings', compact('lims_time_slot_settings'));
    }

    public function updateTimeSlotSettings(Request $request)
    {
        $data = $request->all();

        try {
            DB::beginTransaction();
            foreach ($data as $key => $value) {
                if ($key != '_token') {
                    DeliverySetting::updateOrCreate(
                        ['key' => $key],
                        ['value' => $value, 'type' => 'time_slot']
                    );
                }
            }
            DB::commit();
            $this->cacheForget('delivery_settings');

            return redirect('delivery-settings/time-slot-settings')->with('message', __('db.Time slot settings updated successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Time slot settings update failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Time slot settings update failed: ' . $e->getMessage());
        }
    }

    public function generalSettings()
    {
        $lims_general_settings = DeliverySetting::where('key', 'NOT LIKE', 'commission_%')
            ->where('key', 'NOT LIKE', 'route_%')
            ->where('key', 'NOT LIKE', 'delivery_charge_%')
            ->where('key', 'NOT LIKE', 'time_slot_%')
            ->get();
        return view('backend.delivery_management.delivery_setting.general_settings', compact('lims_general_settings'));
    }

    public function updateGeneralSettings(Request $request)
    {
        $data = $request->all();

        try {
            DB::beginTransaction();
            foreach ($data as $key => $value) {
                if ($key != '_token') {
                    DeliverySetting::updateOrCreate(
                        ['key' => $key],
                        ['value' => $value, 'type' => 'general']
                    );
                }
            }
            DB::commit();
            $this->cacheForget('delivery_settings');

            return redirect('delivery-settings/general-settings')->with('message', __('db.General settings updated successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('General settings update failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'General settings update failed: ' . $e->getMessage());
        }
    }
}
