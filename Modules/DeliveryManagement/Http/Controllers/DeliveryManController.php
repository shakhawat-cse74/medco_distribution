<?php

namespace Modules\DeliveryManagement\Http\Controllers;

use App\Http\Controllers\Controller;

use Modules\DeliveryManagement\Models\DeliveryMan;
use Modules\DeliveryManagement\Models\FieldOrder;
use Modules\DeliveryManagement\Models\FieldPayment;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Models\User;
use Modules\DeliveryManagement\Models\DeliveryManAssignment;
use Modules\DeliveryManagement\Models\DeliveryManVehicle;
use Modules\DeliveryManagement\Models\DeliveryManCommission;
use Modules\Ecommerce\Entities\DeliveryArea;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\DeliveryManagement\Traits\LogsDeliveryActivity;

class DeliveryManController extends Controller
{
    use \App\Traits\CacheForget;
    use LogsDeliveryActivity;

    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('delivery-men-index')) {
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if (empty($all_permission))
                $all_permission[] = 'dummy text';

            $lims_delivery_man_list = DeliveryMan::with('user')->get();
            $lims_route_list = DeliveryArea::active()->get();

            return view('backend.delivery_management.delivery_man.index', compact('lims_delivery_man_list', 'lims_route_list', 'all_permission'));
        } else {
            return redirect()->back()->with('not_permitted', __('db Sorry! You are not allowed to access this module'));
        }
    }

    public function create()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('delivery-men-add')) {
            $lims_route_list = DeliveryArea::active()->get();
            return view('backend.delivery_management.delivery_man.create', compact('lims_route_list'));
        } else {
            return redirect()->back()->with('not_permitted', __('db Sorry! You are not allowed to access this module'));
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
            'image' => 'nullable|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->all();

        try {
            DB::beginTransaction();

            if (empty($data['delivery_man_id'])) {
                $data['delivery_man_id'] = 'DM-' . date('Ymd') . '-' . str_pad(DeliveryMan::count() + 1, 4, '0', STR_PAD_LEFT);
            }

            // Create or find Delivery Man role
            $deliveryManRole = Role::where('name', 'Delivery Man')->first();
            if (!$deliveryManRole) {
                $deliveryManRole = Role::create([
                    'name' => 'Delivery Man',
                    'description' => 'Delivery Man role for field operations',
                    'guard_name' => 'web',
                ]);
            }

            // Create user for delivery man
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password'] ?? 'password123'),
                'phone' => $data['phone_number'],
                'role_id' => $deliveryManRole->id,
                'is_active' => true,
                'is_deleted' => false,
            ]);

            $data['user_id'] = $user->id;

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

            $deliveryMan = DeliveryMan::create(collect($data)->only([
                'delivery_man_id', 'name', 'address', 'city', 'country', 'nid_number', 'image', 'user_id'
            ])->toArray());

            if (!empty($data['route_ids'])) {
                foreach ($data['route_ids'] as $routeId) {
                    DB::table('delivery_men_routes')->insert([
                        'delivery_man_id' => $deliveryMan->id,
                        'route_id' => $routeId,
                        'is_primary' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            if (!empty($data['vehicle_type'])) {
                $vehicleData = [
                    'delivery_man_id' => $deliveryMan->id,
                    'vehicle_type' => $data['vehicle_type'],
                    'vehicle_number' => $data['vehicle_number'] ?? null,
                    'brand' => $data['brand'] ?? null,
                    'model' => $data['model'] ?? null,
                    'color' => $data['color'] ?? null,
                    'registration_number' => $data['registration_number'] ?? null,
                    'license_number' => $data['license_number'] ?? null,
                    'registration_expiry' => $data['registration_expiry'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if ($request->hasFile('vehicle_image')) {
                    $vehicleImage = $request->file('vehicle_image');
                    $ext = pathinfo($vehicleImage->getClientOriginalName(), PATHINFO_EXTENSION);
                    $vehicleImageName = 'vehicle_' . date("Ymdhis") . '.' . $ext;
                    if (!config('database.connections.saleprosaas_landlord')) {
                        $vehicleImage->move(public_path('images/delivery_man_vehicle'), $vehicleImageName);
                    } else {
                        $vehicleImage->move(public_path('images/delivery_man_vehicle'), 'tenant_' . $vehicleImageName);
                    }
                    $vehicleData['image'] = $vehicleImageName;
                }

                DB::table('delivery_man_vehicles')->insert($vehicleData);
            }

            $this->logActivity('delivery_man_created', $deliveryMan->delivery_man_id, 'Delivery man created: ' . $deliveryMan->name);

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
            $lims_delivery_man_data = DeliveryMan::with(['user', 'routes', 'vehicles'])->findOrFail($id);
            $lims_route_list = DeliveryArea::active()->get();
            $selected_routes = $lims_delivery_man_data->routes->pluck('id')->toArray();
            $lims_vehicle_data = $lims_delivery_man_data->vehicles->first();

            return view('backend.delivery_management.delivery_man.edit', compact('lims_delivery_man_data', 'lims_route_list', 'selected_routes', 'lims_vehicle_data'));
        } else {
            return redirect()->back()->with('not_permitted', __('db Sorry! You are not allowed to access this module'));
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
            'image' => 'nullable|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->all();
        $lims_delivery_man_data = DeliveryMan::findOrFail($data['id']);

        try {
            DB::beginTransaction();

            // Update user table
            $user = User::find($lims_delivery_man_data->user_id);
            if ($user) {
                $user->update([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone_number'],
                ]);
                if ($request->password) {
                    $user->update(['password' => bcrypt($request->password)]);
                }
            }

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

            $lims_delivery_man_data->update(collect($data)->only([
                'name', 'address', 'city', 'country', 'nid_number', 'image'
            ])->toArray());

            // Update routes
            DB::table('delivery_men_routes')->where('delivery_man_id', $lims_delivery_man_data->id)->delete();
            if (!empty($data['route_ids'])) {
                foreach ($data['route_ids'] as $routeId) {
                    DB::table('delivery_men_routes')->insert([
                        'delivery_man_id' => $lims_delivery_man_data->id,
                        'route_id' => $routeId,
                        'is_primary' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Update vehicle only if vehicle_type is provided
            if (!empty($data['vehicle_type'])) {
                DB::table('delivery_man_vehicles')->where('delivery_man_id', $lims_delivery_man_data->id)->delete();

                $vehicleData = [
                    'delivery_man_id' => $lims_delivery_man_data->id,
                    'vehicle_type' => $data['vehicle_type'],
                    'vehicle_number' => $data['vehicle_number'] ?? null,
                    'brand' => $data['brand'] ?? null,
                    'model' => $data['model'] ?? null,
                    'color' => $data['color'] ?? null,
                    'registration_number' => $data['registration_number'] ?? null,
                    'license_number' => $data['license_number'] ?? null,
                    'registration_expiry' => $data['registration_expiry'] ?? null,
                    'updated_at' => now(),
                ];

                if ($request->hasFile('vehicle_image')) {
                    $vehicleImage = $request->file('vehicle_image');
                    $ext = pathinfo($vehicleImage->getClientOriginalName(), PATHINFO_EXTENSION);
                    $vehicleImageName = 'vehicle_' . date("Ymdhis") . '.' . $ext;
                    if (!config('database.connections.saleprosaas_landlord')) {
                        $vehicleImage->move(public_path('images/delivery_man_vehicle'), $vehicleImageName);
                    } else {
                        $vehicleImage->move(public_path('images/delivery_man_vehicle'), 'tenant_' . $vehicleImageName);
                    }
                    $vehicleData['image'] = $vehicleImageName;
                }

                DB::table('delivery_man_vehicles')->insert($vehicleData);
            }

            $this->logActivity('delivery_man_updated', $lims_delivery_man_data->delivery_man_id, 'Delivery man updated: ' . $lims_delivery_man_data->name);

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
                $lims_delivery_man_data->delete();

                // Also deactivate the associated user
                if ($lims_delivery_man_data->user_id) {
                    $user = User::find($lims_delivery_man_data->user_id);
                    if ($user) {
                        $user->update(['is_deleted' => true, 'is_active' => false]);
                    }
                }

                $this->logActivity('delivery_man_deleted', $lims_delivery_man_data->delivery_man_id, 'Delivery man deleted: ' . $lims_delivery_man_data->name);

                DB::commit();
                $this->cacheForget('delivery_man_list');

                return redirect('delivery-men')->with('message', __('db.Delivery man deleted successfully'));
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Delivery man deletion failed: ' . $e->getMessage());
                return redirect()->back()->with('not_permitted', 'Delivery man deletion failed: ' . $e->getMessage());
            }
        } else {
            return redirect()->back()->with('not_permitted', __('db Sorry! You are not allowed to access this module'));
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
                    $lims_delivery_man_data->delete();

                    if ($lims_delivery_man_data->user_id) {
                        $user = User::find($lims_delivery_man_data->user_id);
                        if ($user) {
                            $user->update(['is_deleted' => true, 'is_active' => false]);
                        }
                    }

                DB::table('delivery_men_routes')->where('delivery_man_id', $id)->delete();
                    DB::table('delivery_man_vehicles')->where('delivery_man_id', $id)->delete();
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
            $lims_delivery_man_data = DeliveryMan::with(['routes', 'assignments.route', 'vehicles', 'fieldOrders.customer'])->findOrFail($id);

            return view('backend.delivery_management.delivery_man.view', compact('lims_delivery_man_data'));
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
            2 => 'email',
            3 => 'phone_number',
            4 => 'city',
            5 => 'status',
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

        $query = DeliveryMan::with('user');

        if (!empty($request->input('search.value'))) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('email', 'LIKE', "%{$search}%")
                         ->orWhere('phone', 'LIKE', "%{$search}%");
                  });
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
                $nestedData['email'] = $deliveryMan->user->email ?? 'N/A';
                $nestedData['phone_number'] = $deliveryMan->user->phone ?? 'N/A';
                $nestedData['city'] = $deliveryMan->city ?? 'N/A';
                $nestedData['status'] = $deliveryMan->user && $deliveryMan->user->is_active == 1
                    ? '<span class="badge badge-success">Active</span>'
                    : '<span class="badge badge-danger">Inactive</span>';
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
                                      <a href="' . route("delivery-men.edit", $deliveryMan->id) . '" class="btn btn-link"><i class="ti ti-edit"></i> '.__("db.edit").'</a>
                                  </li>
                                  <li class="divider"></li>
                                  <li>
                                       <a href="' . route("delivery-man-routes.index") . '?delivery_man_id=' . $deliveryMan->id . '" class="btn btn-link"><i class="ti ti-route"></i> '.__("db.view_routes").'</a>
                                  </li>
                                   <li>
                                       <a href="' . route("delivery-man-routes.index") . '?delivery_man_id=' . $deliveryMan->id . '" class="btn btn-link"><i class="ti ti-plus"></i> '.__("db.Assign Route").'</a>
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

    public function assignedCustomers($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('delivery-men-index')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $lims_delivery_man_data = DeliveryMan::findOrFail($id);
        $customers = Customer::whereHas('fieldOrders', function ($query) use ($id) {
            $query->where('delivery_man_id', $id);
        })->with(['fieldOrders' => function ($query) use ($id) {
            $query->where('delivery_man_id', $id)->orderByDesc('created_at');
        }])->get();

        return view('backend.delivery_management.delivery_man.customers', compact('lims_delivery_man_data', 'customers'));
    }

    public function customerOrderHistory($delivery_man_id, $customer_id)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('delivery-men-index')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $lims_delivery_man_data = DeliveryMan::findOrFail($delivery_man_id);
        $customer = Customer::findOrFail($customer_id);
        $orders = FieldOrder::where('delivery_man_id', $delivery_man_id)
            ->where('customer_id', $customer_id)
            ->orderByDesc('created_at')
            ->get();

        return view('backend.delivery_management.delivery_man.customer_orders', compact('lims_delivery_man_data', 'customer', 'orders'));
    }

    public function customerLedger($delivery_man_id, $customer_id)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('delivery-men-index')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $lims_delivery_man_data = DeliveryMan::findOrFail($delivery_man_id);
        $customer = Customer::findOrFail($customer_id);
        $orders = FieldOrder::where('delivery_man_id', $delivery_man_id)
            ->where('customer_id', $customer_id)
            ->orderByDesc('created_at')
            ->get();

        $totalOrders = $orders->count();
        $totalOrderAmount = $orders->sum('grand_total');
        $totalPaid = $orders->sum('paid_amount');
        $totalDue = $orders->sum('due_amount');

        return view('backend.delivery_management.delivery_man.customer_ledger', compact(
            'lims_delivery_man_data', 'customer', 'orders', 'totalOrders', 'totalOrderAmount', 'totalPaid', 'totalDue'
        ));
    }

    public function collectDuePayment(Request $request, $delivery_man_id, $customer_id)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('delivery-men-edit')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $this->validate($request, [
            'field_order_id' => 'required|exists:field_orders,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $fieldOrder = FieldOrder::where('delivery_man_id', $delivery_man_id)
                ->where('customer_id', $customer_id)
                ->findOrFail($request->field_order_id);

            $payment = FieldPayment::create([
                'field_order_id' => $fieldOrder->id,
                'payment_method' => $request->payment_method,
                'amount' => $request->amount,
                'reference_no' => 'PAY-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 6, '0', STR_PAD_LEFT),
                'note' => $request->note ?? null,
                'created_by' => Auth::id(),
            ]);

            $fieldOrder->paid_amount += $request->amount;
            $fieldOrder->due_amount = $fieldOrder->grand_total - $fieldOrder->paid_amount;
            $fieldOrder->save();

            DB::commit();

            return redirect()->back()->with('message', __('db.Payment collected successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Due payment collection failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Payment collection failed: ' . $e->getMessage());
        }
    }
}
