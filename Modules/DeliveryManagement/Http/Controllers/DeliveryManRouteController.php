<?php

namespace Modules\DeliveryManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\DeliveryManagement\Models\DeliveryManRoute;
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

            return view('backend.delivery_management.delivery_man_route.index', compact('all_permission'));
        } else {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
    }

    public function routeData(Request $request)
    {
        if ($response = $this->checkPermission('delivery-man-routes-index')) {
            return $response;
        }

        $columns = [
            1 => 'name',
            2 => 'city',
            3 => 'zone',
            4 => 'delivery_charge',
            5 => 'estimated_days',
            6 => 'is_active',
        ];

        $totalData = DeliveryManRoute::count();
        $totalFiltered = $totalData;

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')] ?? 'id';
        $dir = $request->input('order.0.dir', 'asc');

        $query = DeliveryManRoute::query();

        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('city', 'LIKE', "%{$search}%")
                  ->orWhere('zone', 'LIKE', "%{$search}%");
            });

            $totalFiltered = (clone $query)->count();
        }

        $routes = $query
            ->orderBy($order, $dir)
            ->offset($start)
            ->limit($limit)
            ->get();

        $data = [];
        foreach ($routes as $route) {
            $action = '<div class="btn-group">';
            $action .= '<button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.__("db.action");
            $action .= '<span class="caret"></span><span class="sr-only">Toggle Dropdown</span>';
            $action .= '</button>';
            $action .= '<ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">';

            if ($request->user()->can('delivery-man-routes-edit')) {
                $action .= '<li>';
                $action .= '<button type="button" data-id="'.$route->id.'" class="open-EditRouteDialog btn btn-link" data-toggle="modal" data-target="#editModal"><i class="ti ti-edit"></i> '.__("db.edit").'</button>';
                $action .= '</li>';
            }

            if ($request->user()->can('delivery-man-routes-delete')) {
                $action .= '<li class="divider"></li>';
                $action .= '<form action="'.route('delivery-man-routes.delete', $route->id).'" method="POST" onsubmit="return confirm('."'Are you sure?'".')">'.csrf_field().'' . method_field("POST") . '';
                $action .= '<li>';
                $action .= '<button type="submit" class="btn btn-link" data-id="'.$route->id.'" data-name="'.$route->name.'"><i class="ti ti-trash"></i> '.__("db.delete").'</button>';
                $action .= '</li></form>';
            }

            $action .= '</ul></div>';

            $data[] = [
                'id' => $route->id,
                'name' => $route->name,
                'city' => $route->city ?? 'N/A',
                'zone' => $route->zone ?? 'N/A',
                'delivery_charge' => $route->delivery_charge,
                'estimated_days' => $route->estimated_days,
                'is_active' => (bool) $route->is_active,
                'options' => $action,
            ];
        }

        return response()->json([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => $totalData,
            "recordsFiltered" => $totalFiltered,
            "data" => $data,
        ]);
    }

    private function checkPermission($permission)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo($permission)) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
        return null;
    }

    public function store(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('delivery-man-routes-add')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $this->validate($request, [
            'name'            => 'required|string|max:255',
            'city'            => 'nullable|string|max:255',
            'zone'            => 'nullable|string|max:255',
            'delivery_charge' => 'required|numeric|min:0',
            'estimated_days'  => 'required|integer|min:1',
            'is_active'       => 'nullable|boolean',
            'note'            => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();
            DeliveryManRoute::create([
                'name'            => $request->name,
                'city'            => $request->city,
                'zone'            => $request->zone,
                'delivery_charge' => $request->delivery_charge,
                'estimated_days'  => $request->estimated_days,
                'is_active'       => $request->has('is_active') ? 1 : 0,
                'note'            => $request->note,
            ]);
            DB::commit();

            return redirect('delivery-man-routes')->with('message', __('db.Route created successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Route creation failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Route creation failed: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('delivery-man-routes-edit')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $lims_route_data = DeliveryManRoute::findOrFail($id);
        return response()->json($lims_route_data);
    }

    public function update(Request $request, $id)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('delivery-man-routes-edit')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $this->validate($request, [
            'name'            => 'required|string|max:255',
            'city'            => 'nullable|string|max:255',
            'zone'            => 'nullable|string|max:255',
            'delivery_charge' => 'required|numeric|min:0',
            'estimated_days'  => 'required|integer|min:1',
            'is_active'       => 'nullable|boolean',
            'note'            => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();
            $lims_route_data = DeliveryManRoute::findOrFail($id);
            $lims_route_data->update([
                'name'            => $request->name,
                'city'            => $request->city,
                'zone'            => $request->zone,
                'delivery_charge' => $request->delivery_charge,
                'estimated_days'  => $request->estimated_days,
                'is_active'       => $request->has('is_active') ? 1 : 0,
                'note'            => $request->note,
            ]);
            DB::commit();

            return redirect('delivery-man-routes')->with('message', __('db.Route updated successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Route update failed: ' . $e->getMessage());
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

                return redirect('delivery-man-routes')->with('message', __('db.Route deleted successfully'));
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Route deletion failed: ' . $e->getMessage());
                return redirect()->back()->with('not_permitted', 'Route deletion failed: ' . $e->getMessage());
            }
        } else {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
    }
}
