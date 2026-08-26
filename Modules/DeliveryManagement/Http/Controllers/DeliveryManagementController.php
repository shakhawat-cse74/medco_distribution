<?php

namespace Modules\DeliveryManagement\Http\Controllers;

use App\Http\Controllers\Controller;

use Modules\DeliveryManagement\Models\DeliveryManDelivery;
use Modules\DeliveryManagement\Models\DeliveryMan;
use Modules\DeliveryManagement\Models\FieldOrder;
use App\Models\Customer;
use Modules\DeliveryManagement\Models\DeliveryManRoute;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DeliveryManagementController extends Controller
{
    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('delivery-man-delivery-index')) {
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if (empty($all_permission))
                $all_permission[] = 'dummy text';

            $lims_delivery_man_list = DeliveryMan::where('is_active', true)->get();
            $lims_delivery_list = DeliveryManDelivery::with(['deliveryMan', 'customer', 'fieldOrder'])->get();

            return view('backend.delivery_management.index', compact('lims_delivery_list', 'lims_delivery_man_list', 'all_permission'));
        } else {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
    }

    public function deliveryListData(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);

        $limit = (int) $request->input('length', 10);
        $start = (int) $request->input('start', 0);

        if ($limit < 0) {
            $limit = 10;
        }

        $search = $request->input('search.value');

        $query = DeliveryManDelivery::with(['deliveryMan', 'customer', 'fieldOrder']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reference_no', 'LIKE', "%{$search}%")
                    ->orWhereHas('deliveryMan', function ($q2) use ($search) {
                        $q2->where('name', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('customer', function ($q2) use ($search) {
                        $q2->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        $totalData = DeliveryManDelivery::count();
        $totalFiltered = $query->count();

        $deliveries = $query
            ->orderBy('id', 'desc')
            ->skip($start)
            ->take($limit)
            ->get();

        $data = [];
        foreach ($deliveries as $key => $delivery) {
            $nestedData['key'] = $key;
            $nestedData['reference_no'] = $delivery->reference_no;
            $nestedData['delivery_man'] = $delivery->deliveryMan ? $delivery->deliveryMan->name : 'N/A';
            $nestedData['customer'] = $delivery->customer->name ?? 'N/A';
            $nestedData['address'] = $delivery->address;
            $nestedData['status'] = ucfirst($delivery->status);
            $nestedData['priority'] = ucfirst($delivery->priority);
            $nestedData['id'] = $delivery->id;
            $nestedData['date'] = date(config('date_format'), strtotime($delivery->created_at));

            $nestedData['options'] = '<div class="btn-group">
                <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' . __("db.action") . '
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                    <li>
                        <button type="button" data-id="' . $delivery->id . '" class="open-EditCategoryDialog btn btn-link" data-toggle="modal" data-target="#editModal" ><i class="ti ti-edit"></i> ' . __("db.edit") . '</button>
                    </li>
                    <li>
                        <form action="' . route("delivery-man-delivery.delete", $delivery->id) . '" method="POST">' . csrf_field() . '' . method_field("DELETE") . '
                            <button type="submit" class="btn btn-link" onclick="return confirmDelete()"><i class="ti ti-trash"></i> ' . __("db.delete") . '</button>
                        </form>
                    </li>
                </ul>
            </div>';

            $data[] = $nestedData;
        }

        return response()->json([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data" => $data
        ]);
    }

    public function assign(Request $request)
    {
        $request->validate([
            'field_order_id' => 'required|string|exists:field_orders,reference_no',
            'delivery_man_id' => 'required|exists:delivery_men,id',
            'priority' => 'nullable|in:normal,high,urgent',
        ]);

        $fieldOrder = FieldOrder::where('reference_no', $request->field_order_id)->firstOrFail();
        $customer = Customer::findOrFail($fieldOrder->customer_id);

        $existingDelivery = DeliveryManDelivery::where('field_order_id', $fieldOrder->id)
            ->whereIn('status', ['assigned', 'started'])
            ->first();

        if ($existingDelivery) {
            return response()->json([
                'success' => false,
                'message' => __('db.This order is already assigned to a delivery man')
            ]);
        }

        try {
            DB::beginTransaction();

            $delivery = DeliveryManDelivery::create([
                'reference_no' => 'DLV-' . date("Ymd") . '-' . strtoupper(Str::random(6)),
                'field_order_id' => $fieldOrder->id,
                'delivery_man_id' => $request->delivery_man_id,
                'customer_id' => $fieldOrder->customer_id,
                'address' => $fieldOrder->delivery_address,
                'city' => $fieldOrder->delivery_city,
                'country' => $fieldOrder->delivery_country,
                'status' => 'assigned',
                'priority' => $request->priority ?? 'normal',
                'assigned_by' => Auth::id(),
                'assigned_at' => now(),
            ]);

            $fieldOrder->status = 'assigned';
            $fieldOrder->save();

            DB::commit();

            return response()->json(['success' => true, 'message' => __('db.Delivery assigned successfully')]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delivery assignment failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Assignment failed: ' . $e->getMessage()]);
        }
    }

    public function autoAssign(Request $request)
    {
        $fieldOrder = FieldOrder::findOrFail($request->field_order_id);

        $assignment = DeliveryManAssignment::whereHas('route', function ($q) use ($fieldOrder) {
                $q->where('area_ids', 'LIKE', "%{$fieldOrder->delivery_city}%");
            })
            ->orWhere('area_id', $fieldOrder->delivery_city)
            ->first();

        if (!$assignment) {
            return response()->json(['success' => false, 'message' => 'No delivery man available for this area']);
        }

        return $this->assign($request->merge(['delivery_man_id' => $assignment->delivery_man_id]));
    }

    public function updateStatus($id)
    {
        $lims_delivery_data = DeliveryManDelivery::findOrFail($id);
        $status = request('status');

        try {
            DB::beginTransaction();
            $lims_delivery_data->status = $status;

            if ($status == 'started') {
                $lims_delivery_data->started_at = now();
            } elseif ($status == 'completed') {
                $lims_delivery_data->completed_at = now();

                $fieldOrder = FieldOrder::find($lims_delivery_data->field_order_id);
                if ($fieldOrder) {
                    $fieldOrder->status = 'completed';
                    $fieldOrder->save();
                }
            }

            $lims_delivery_data->save();
            DB::commit();

            return response()->json(['success' => true, 'message' => __('db.Delivery status updated successfully')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Status update failed: ' . $e->getMessage()]);
        }
    }

    public function mapView()
    {
        $lims_delivery_list = DeliveryManDelivery::with(['deliveryMan', 'customer'])->whereIn('status', ['assigned', 'started'])->get();
        return view('backend.delivery_management.map_view', compact('lims_delivery_list'));
    }

    public function liveTracking()
    {
        $lims_delivery_list = DeliveryManDelivery::with(['deliveryMan', 'customer'])->whereIn('status', ['assigned', 'started'])->get();
        return response()->json($lims_delivery_list);
    }

    public function routeOptimization($delivery_man_id)
    {
        $deliveries = DeliveryManDelivery::where('delivery_man_id', $delivery_man_id)
            ->whereIn('status', ['assigned', 'started'])
            ->with('fieldOrder')
            ->get();

        return response()->json(['deliveries' => $deliveries]);
    }

    public function setPriority($id)
    {
        $lims_delivery_data = DeliveryManDelivery::findOrFail($id);
        $lims_delivery_data->priority = request('priority', 'normal');
        $lims_delivery_data->save();

        return response()->json(['success' => true, 'message' => __('db.Priority updated successfully')]);
    }

    public function pendingDeliveries()
    {
        $lims_delivery_list = DeliveryManDelivery::with(['deliveryMan', 'customer'])->where('status', 'assigned')->get();
        return response()->json($lims_delivery_list);
    }

    public function completedDeliveries()
    {
        $lims_delivery_list = DeliveryManDelivery::with(['deliveryMan', 'customer'])->where('status', 'completed')->get();
        return response()->json($lims_delivery_list);
    }

    public function dueDeliveries()
    {
        $lims_delivery_list = DeliveryManDelivery::with(['deliveryMan', 'customer'])->where('status', 'due')->get();
        return response()->json($lims_delivery_list);
    }

    public function delete($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('delivery-man-delivery-update')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        try {
            DB::beginTransaction();
            $lims_delivery_data = DeliveryManDelivery::findOrFail($id);
            $lims_delivery_data->delete();
            DB::commit();

            return redirect('delivery-man-delivery')->with('message', __('db.Delivery deleted successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delivery deletion failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Delivery deletion failed: ' . $e->getMessage());
        }
    }
}
