<?php

namespace Modules\DeliveryManagement\Http\Controllers;

use App\Http\Controllers\Controller;

use Modules\DeliveryManagement\Models\DeliveryMan;
use App\Models\Warehouse;
use App\Models\User;
use Modules\DeliveryManagement\Models\DeliveryManAssignment;
use Modules\DeliveryManagement\Models\DeliveryManVehicle;
use Modules\DeliveryManagement\Models\FieldOrder;
use Modules\DeliveryManagement\Models\DeliveryManCommission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeliveryManController extends Controller
{
    use \App\Traits\CacheForget;

    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('delivery-men-index')) {
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if (empty($all_permission))
                $all_permission[] = 'dummy text';

            $lims_delivery_man_list = DeliveryMan::with('warehouse')->get();
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();

            return view('backend.delivery_management.delivery_man.index', compact('lims_delivery_man_list', 'lims_warehouse_list', 'all_permission'));
        } else {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
    }

    public function create()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('delivery-men-add')) {
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_user_list = User::where('is_deleted', false)->where('is_active', true)->get();

            return view('backend.delivery_management.delivery_man.create', compact('lims_warehouse_list', 'lims_user_list'));
        } else {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
    }

    public function store(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('delivery-men-add')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $this->validate($request, [
            'name' => 'required|max:255',
            'phone_number' => 'required|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        $data = $request->all();

        try {
            DB::beginTransaction();

            $data['is_active'] = true;

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $ext = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
                $imageName = date("Ymdhis");
                if (!config('database.connections.saleprosaas_landlord')) {
                    $imageName = $imageName . '.' . $ext;
                    $image->move(public_path('images/delivery_man'), $imageName);
                } else {
                    $imageName = 'tenant_' . $imageName . '.' . $ext;
                    $image->move(public_path('images/delivery_man'), $imageName);
                }
                $data['image'] = $imageName;
            }

            if ($request->password) {
                $data['password'] = bcrypt($request->password);
            }

            $deliveryMan = DeliveryMan::create($data);

            DB::commit();
            $this->cacheForget('delivery_man_list');

            return redirect('delivery-men')->with('message', __('db.Delivery man created successfully'));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delivery man creation failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Delivery man creation failed: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('delivery-men-edit')) {
            $lims_delivery_man_data = DeliveryMan::findOrFail($id);
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_user_list = User::where('is_deleted', false)->where('is_active', true)->get();

            $data = [
                'id' => $lims_delivery_man_data->id,
                'name' => $lims_delivery_man_data->name,
                'email' => $lims_delivery_man_data->email,
                'phone_number' => $lims_delivery_man_data->phone_number,
                'address' => $lims_delivery_man_data->address,
                'city' => $lims_delivery_man_data->city,
                'country' => $lims_delivery_man_data->country,
                'nid_number' => $lims_delivery_man_data->nid_number,
                'license_number' => $lims_delivery_man_data->license_number,
                'vehicle_type' => $lims_delivery_man_data->vehicle_type,
                'vehicle_number' => $lims_delivery_man_data->vehicle_number,
                'image' => $lims_delivery_man_data->image,
                'user_id' => $lims_delivery_man_data->user_id,
                'warehouse_id' => $lims_delivery_man_data->warehouse_id,
                'note' => $lims_delivery_man_data->note,
                'is_active' => $lims_delivery_man_data->is_active,
                'fcm_token' => $lims_delivery_man_data->fcm_token,
            ];

            return $data;
        } else {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
    }

    public function update(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('delivery-men-edit')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $this->validate($request, [
            'name' => 'required|max:255',
            'phone_number' => 'required|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        $data = $request->all();
        $lims_delivery_man_data = DeliveryMan::findOrFail($data['id']);

        try {
            DB::beginTransaction();

            if ($request->hasFile('image')) {
                $this->fileDelete(public_path('images/delivery_man/'), $lims_delivery_man_data->image);
                $image = $request->file('image');
                $ext = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
                $imageName = date("Ymdhis");
                if (!config('database.connections.saleprosaas_landlord')) {
                    $imageName = $imageName . '.' . $ext;
                    $image->move(public_path('images/delivery_man'), $imageName);
                } else {
                    $imageName = 'tenant_' . $imageName . '.' . $ext;
                    $image->move(public_path('images/delivery_man'), $imageName);
                }
                $data['image'] = $imageName;
            }

            if ($request->password) {
                $data['password'] = bcrypt($request->password);
            } else {
                unset($data['password']);
            }

            $lims_delivery_man_data->update($data);

            DB::commit();
            $this->cacheForget('delivery_man_list');

            return redirect('delivery-men')->with('message', __('db.Delivery man updated successfully'));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delivery man update failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Delivery man update failed: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('delivery-men-delete')) {
            try {
                DB::beginTransaction();
                $lims_delivery_man_data = DeliveryMan::findOrFail($id);
                $lims_delivery_man_data->is_active = false;
                $lims_delivery_man_data->save();

                DB::commit();
                $this->cacheForget('delivery_man_list');

                return redirect('delivery-men')->with('message', __('db.Delivery man deleted successfully'));
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Delivery man deletion failed: ' . $e->getMessage());
                return redirect()->back()->with('not_permitted', 'Delivery man deletion failed: ' . $e->getMessage());
            }
        } else {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
    }

    public function deleteBySelection(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('delivery-men-delete')) {
            try {
                DB::beginTransaction();
                $delivery_man_id = $request['deliveryManIdArray'];
                foreach ($delivery_man_id as $id) {
                    $lims_delivery_man_data = DeliveryMan::find($id);
                    $lims_delivery_man_data->is_active = false;
                    $lims_delivery_man_data->save();
                }
                DB::commit();
                $this->cacheForget('delivery_man_list');

                return 'Delivery man deleted successfully!';
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Bulk delivery man deletion failed: ' . $e->getMessage());
                return 'Delivery man deletion failed: ' . $e->getMessage();
            }
        } else {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
    }

    public function show($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('delivery-men-index')) {
            $lims_delivery_man_data = DeliveryMan::with(['warehouse', 'assignments.route', 'vehicles', 'fieldOrders.customer'])->findOrFail($id);

            return view('backend.delivery_management.delivery_man.view', compact('lims_delivery_man_data'));
        } else {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
    }

    public function toggleStatus(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('delivery-men-edit')) {
            $lims_delivery_man_data = DeliveryMan::findOrFail($request->id);
            $lims_delivery_man_data->is_active = !$lims_delivery_man_data->is_active;
            $lims_delivery_man_data->save();

            return 'Delivery man status updated successfully';
        } else {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
    }

    public function performance($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('delivery-men-index')) {
            $lims_delivery_man_data = DeliveryMan::with(['fieldOrders', 'deliveries', 'commissions', 'cashDeposits', 'visits'])->findOrFail($id);

            $totalOrders = $lims_delivery_man_data->fieldOrders()->count();
            $completedOrders = $lims_delivery_man_data->fieldOrders()->where('status', 'completed')->count();
            $totalCollection = $lims_delivery_man_data->fieldOrders()->sum('paid_amount');
            $totalDue = $lims_delivery_man_data->fieldOrders()->sum('due_amount');
            $totalCommission = $lims_delivery_man_data->commissions()->sum('commission_amount');
            $totalDeposits = $lims_delivery_man_data->cashDeposits()->sum('amount');
            $totalVisits = $lims_delivery_man_data->visits()->count();

            $performance = [
                'delivery_man' => $lims_delivery_man_data,
                'total_orders' => $totalOrders,
                'completed_orders' => $completedOrders,
                'total_collection' => $totalCollection,
                'total_due' => $totalDue,
                'total_commission' => $totalCommission,
                'total_deposits' => $totalDeposits,
                'total_visits' => $totalVisits,
            ];

            return response()->json($performance);
        } else {
            return response()->json(['error' => 'Not permitted'], 403);
        }
    }

    public function deliveryManData(Request $request)
    {
        $columns = array(
            0 => 'id',
            1 => 'name',
            2 => 'phone_number',
            3 => 'warehouse_id',
            4 => 'is_active',
        );

        $totalData = DeliveryMan::count();
        $totalFiltered = $totalData;

        if ($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        $query = DeliveryMan::query();

        if (!empty($request->input('search.value'))) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('phone_number', 'LIKE', "%{$search}%");
            });
        }

        $deliveryMen = $query->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir)
            ->get();

        $totalFiltered = $query->count();

        $data = array();
        if (!empty($deliveryMen)) {
            foreach ($deliveryMen as $key => $deliveryMan) {
                $nestedData['id'] = $deliveryMan->id;
                $nestedData['key'] = $key;
                $nestedData['name'] = $deliveryMan->name;
                $nestedData['phone_number'] = $deliveryMan->phone_number;
                $nestedData['warehouse_id'] = $deliveryMan->warehouse ? $deliveryMan->warehouse->name : 'N/A';
                $nestedData['is_active'] = '<span class="badge badge-'.($deliveryMan->is_active ? 'success' : 'danger').'">'.($deliveryMan->is_active ? __('db.Active') : __('db.Inactive')).'</span>';
                $nestedData['performance'] = '<a href="'.route('delivery-men.performance', $deliveryMan->id).'" class="btn btn-sm btn-info" target="_blank"><i class="ti ti-chart-bar"></i> '.__('db.Performance').'</a>';
                $nestedData['options'] = '<div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.__("db.action").'
                              <span class="caret"></span>
                              <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                                <li>
                                    <a href="' . route("delivery-men.show", $deliveryMan->id) . '" class="btn btn-link"><i class="ti ti-eye"></i> '.__("db.View").'</a>
                                </li>
                                <li>
                                    <button type="button" data-id="'.$deliveryMan->id.'" class="open-EditCategoryDialog btn btn-link" data-toggle="modal" data-target="#editModal" ><i class="ti ti-edit"></i> '.__("db.edit").'</button>
                                </li>
                                <li class="divider"></li>
                                <li>
                                    <button type="button" class="toggle-status btn btn-link" data-id="'.$deliveryMan->id.'"><i class="ti ti-toggle-left"></i> '.__("db.Toggle Status").'</button>
                                </li>
                                <li class="divider"></li>
                                <form action="' . route("delivery-men.delete", $deliveryMan->id) . '" method="POST">'.csrf_field().'' . method_field("POST") . '
                                <li>
                                  <button type="submit" class="btn btn-link confirm-delete-btn" data-id="'.$deliveryMan->id.'" data-name="'.$deliveryMan->name.'"><i class="ti ti-trash"></i> '.__("db.delete").'</button>
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

        echo json_encode($json_data);
    }

    public function uploadPhoto(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('delivery-men-edit')) {
            if ($request->hasFile('photo')) {
                $image = $request->file('photo');
                $ext = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
                $imageName = date("Ymdhis");
                if (!config('database.connections.saleprosaas_landlord')) {
                    $imageName = $imageName . '.' . $ext;
                    $image->move(public_path('images/delivery_man'), $imageName);
                } else {
                    $imageName = 'tenant_' . $imageName . '.' . $ext;
                    $image->move(public_path('images/delivery_man'), $imageName);
                }

                return response()->json(['success' => true, 'image_name' => $imageName]);
            }

            return response()->json(['success' => false, 'message' => 'No image uploaded']);
        } else {
            return response()->json(['error' => 'Not permitted'], 403);
        }
    }
}
