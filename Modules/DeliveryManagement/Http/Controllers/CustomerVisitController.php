<?php

namespace Modules\DeliveryManagement\Http\Controllers;

use App\Http\Controllers\Controller;

use Modules\DeliveryManagement\Models\CustomerVisit;
use Modules\DeliveryManagement\Models\DeliveryMan;
use App\Models\Customer;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\CacheForget;

class CustomerVisitController extends Controller
{
    use \App\Traits\CacheForget;

    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('customer-visits-index')) {
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if (empty($all_permission))
                $all_permission[] = 'dummy text';

            $lims_visit_list = CustomerVisit::with(['deliveryMan', 'customer'])->get();
            $lims_delivery_man_list = DeliveryMan::where('is_active', true)->get();
            $lims_customer_list = Customer::where('is_active', true)->get();

            return view('backend.delivery_management.customer_visit.index', compact('lims_visit_list', 'lims_delivery_man_list', 'lims_customer_list', 'all_permission'));
        } else {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
    }

    public function checkIn(Request $request)
    {
        $this->validate($request, [
            'delivery_man_id' => 'required|exists:delivery_men,id',
            'customer_id' => 'required|exists:customers,id',
            'check_in_latitude' => 'required',
            'check_in_longitude' => 'required',
        ]);

        $data = $request->all();

        try {
            DB::beginTransaction();
            CustomerVisit::create([
                'delivery_man_id' => $data['delivery_man_id'],
                'customer_id' => $data['customer_id'],
                'check_in_at' => now(),
                'check_in_latitude' => $data['check_in_latitude'],
                'check_in_longitude' => $data['check_in_longitude'],
                'note' => $data['note'] ?? null,
            ]);
            DB::commit();

            return response()->json(['success' => true, 'message' => __('db.Checked in successfully')]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Check in failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Check in failed: ' . $e->getMessage()]);
        }
    }

    public function checkOut($id)
    {
        $lims_visit_data = CustomerVisit::findOrFail($id);

        try {
            DB::beginTransaction();
            $lims_visit_data->check_out_at = now();
            $lims_visit_data->save();
            DB::commit();

            return response()->json(['success' => true, 'message' => __('db.Checked out successfully')]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Check out failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Check out failed: ' . $e->getMessage()]);
        }
    }

    public function history($customer_id)
    {
        $lims_visit_list = CustomerVisit::with('deliveryMan')
            ->where('customer_id', $customer_id)
            ->orderBy('check_in_at', 'desc')
            ->get();

        return response()->json($lims_visit_list);
    }

    public function logs()
    {
        $lims_visit_list = CustomerVisit::with(['deliveryMan', 'customer'])->orderBy('check_in_at', 'desc')->get();
        return view('backend.delivery_management.customer_visit.logs', compact('lims_visit_list'));
    }

    public function todayVisits()
    {
        $today = date('Y-m-d');
        $lims_visit_list = CustomerVisit::with(['deliveryMan', 'customer'])
            ->whereDate('check_in_at', $today)
            ->get();

        return response()->json($lims_visit_list);
    }
}
