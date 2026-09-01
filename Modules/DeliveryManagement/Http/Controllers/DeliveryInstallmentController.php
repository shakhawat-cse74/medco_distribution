<?php

namespace Modules\DeliveryManagement\Http\Controllers;

use App\Http\Controllers\Controller;

use Modules\DeliveryManagement\Models\DeliveryMan;
use Modules\DeliveryManagement\Models\FieldOrder;
use Modules\DeliveryManagement\Models\DeliveryManDelivery;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductBatch;
use App\Models\Sale;
use App\Models\Product_Sale;
use App\Models\Payment;
use App\Models\PaymentWithCreditCard;
use App\Models\PaymentWithGiftCard;
use App\Models\PaymentWithPaypal;
use App\Models\Account;
use App\Models\Currency;
use App\Models\DeliveryArea;
use App\Models\ExternalService;
use App\Models\GiftCard;
use App\Models\PosSetting;
use App\Models\SmsTemplate;
use App\ViewModels\ISmsModel;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;

class DeliveryInstallmentController extends Controller
{
    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        
        if (!$role->hasPermissionTo('delivery-installments-index')) {
            return redirect()->back()->with('not_permitted', __('db Sorry! You are not allowed to access this module'));
        }
        
        $starting_date = date("Y-m-d", strtotime("-1 month"));
        $ending_date = date("Y-m-d");
        
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_delivery_man_list = DeliveryMan::active()->get();
        $lims_route_list = DeliveryArea::active()->get();
        
        return view('backend.delivery_management.delivery_installment.index', compact(
            'lims_warehouse_list',
            'lims_delivery_man_list',
            'lims_route_list',
            'starting_date',
            'ending_date'
        ));
    }
    
    public function create()
    {
        $role = Role::find(Auth::user()->role_id);
        
        if (!$role->hasPermissionTo('delivery-installments-add')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
        
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_customer_list = Customer::active()->get();
        $lims_delivery_man_list = DeliveryMan::active()->get();
        $lims_route_list = DeliveryArea::active()->get();
        
        return view('backend.delivery_management.delivery_installment.create', compact(
            'lims_warehouse_list',
            'lims_customer_list',
            'lims_delivery_man_list',
            'lims_route_list'
        ));
    }
    
    public function store(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        
        if (!$role->hasPermissionTo('delivery-installments-add')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
        
        $this->validate($request, [
            'warehouse_id' => 'required|exists:warehouses,id',
            'customer_id' => 'required|exists:customers,id',
            'route_id' => 'required|exists:delivery_areas,id',
            'delivery_man_id' => 'required|exists:delivery_men,id',
            'installment_amount' => 'required|numeric|min:0',
            'installment_months' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'payment_method' => 'required|string|max:255',
        ]);
        
        $data = $request->all();
        $data['reference_no'] = 'INS-' . date('Ymd') . '-' . str_pad(Sale::count() + 1, 6, '0', STR_PAD_LEFT);
        $data['created_by'] = Auth::id();
        
        try {
            DB::beginTransaction();
            
            $lims_installment_data = Sale::create(collect($data)->only([
                'reference_no', 'warehouse_id', 'customer_id', 'delivery_man_id', 
                'route_id', 'installment_amount', 'installment_months', 
                'start_date', 'payment_method', 'payment_status', 'due_date',
                'sale_date', 'sale_status', 'created_by'
            ])->toArray());
            
            // Create installment plan
            for ($i = 1; $i <= $request->installment_months; $i++) {
                $due_date = date('Y-m-d', strtotime($request->start_date . '+' . $i . ' months'));
                
                $lims_sale_data = Sale::create([
                    'reference_no' => 'INS-' . date('Ymd') . '-' . str_pad(Sale::count() + 1, 6, '0', STR_PAD_LEFT),
                    'warehouse_id' => $request->warehouse_id,
                    'customer_id' => $request->customer_id,
                    'delivery_man_id' => $request->delivery_man_id,
                    'route_id' => $request->route_id,
                    'installment_parent_id' => $lims_installment_data->id,
                    'due_date' => $due_date,
                    'installment_number' => $i,
                    'amount' => $request->installment_amount / $request->installment_months,
                    'payment_status' => 'pending',
                    'sale_date' => $request->start_date,
                    'sale_status' => 'pending',
                    'created_by' => Auth::id(),
                ]);
            }
            
            DB::commit();
            
            return redirect('delivery-installment.show', $lims_installment_data->id)->with('message', __('db.Delivery installment created successfully'));
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delivery installment creation failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Delivery installment creation failed: ' . $e->getMessage());
        }
    }
    
    public function show($id)
    {
        $lims_installment_data = Sale::with(['customer', 'warehouse', 'deliveryMan', 'route', 'products.product'])->findOrFail($id);
        $lims_installment_details = Sale::where('installment_parent_id', $id)->get();
        
        $role = Role::find(Auth::user()->role_id);
        
        if (!$role->hasPermissionTo('delivery-installments-index')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
        
        return view('backend.delivery_management.delivery_installment.show', compact('lims_installment_data', 'lims_installment_details'));
    }
    
    public function edit($id)
    {
        $role = Role::find(Auth::user()->role_id);
        
        if (!$role->hasPermissionTo('delivery-installments-edit')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
        
        $lims_installment_data = Sale::with(['customer', 'warehouse', 'deliveryMan', 'route'])->findOrFail($id);
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_customer_list = Customer::active()->get();
        $lims_delivery_man_list = DeliveryMan::active()->get();
        $lims_route_list = DeliveryArea::active()->get();
        
        return view('backend.delivery_management.delivery_installment.edit', compact(
            'lims_installment_data',
            'lims_warehouse_list',
            'lims_customer_list',
            'lims_delivery_man_list',
            'lims_route_list'
        ));
    }
    
    public function update(Request $request, $id)
    {
        $role = Role::find(Auth::user()->role_id);
        
        if (!$role->hasPermissionTo('delivery-installments-edit')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
        
        $this->validate($request, [
            'warehouse_id' => 'required|exists:warehouses,id',
            'customer_id' => 'required|exists:customers,id',
            'route_id' => 'required|exists:delivery_areas,id',
            'delivery_man_id' => 'required|exists:delivery_men,id',
            'installment_amount' => 'required|numeric|min:0',
            'installment_months' => 'required|integer|min:1',
            'start_date' => 'required|date',
        ]);
        
        $lims_installment_data = Sale::findOrFail($id);
        $data = $request->all();
        
        try {
            DB::beginTransaction();
            
            $lims_installment_data->update(collect($data)->only([
                'warehouse_id', 'customer_id', 'delivery_man_id', 
                'route_id', 'installment_amount', 'installment_months', 
                'start_date', 'payment_method', 'payment_status', 'due_date'
            ])->toArray());
            
            // Update or create installment details
            $existingInstallments = Sale::where('installment_parent_id', $id)->get();
            
            if (count($existingInstallments) > 0) {
                // Update existing installments
                foreach ($existingInstallments as $installment) {
                    $installment->amount = $request->installment_amount / $request->installment_months;
                    $installment->due_date = date('Y-m-d', strtotime($request->start_date . '+' . $installment->installment_number . ' months'));
                    $installment->save();
                }
            } else {
                // Create new installments
                for ($i = 1; $i <= $request->installment_months; $i++) {
                    $due_date = date('Y-m-d', strtotime($request->start_date . '+' . $i . ' months'));
                    
                    $lims_sale_data = Sale::create([
                        'reference_no' => 'INS-' . date('Ymd') . '-' . str_pad(Sale::count() + 1, 6, '0', STR_PAD_LEFT),
                        'warehouse_id' => $request->warehouse_id,
                        'customer_id' => $request->customer_id,
                        'delivery_man_id' => $request->delivery_man_id,
                        'route_id' => $request->route_id,
                        'installment_parent_id' => $lims_installment_data->id,
                        'due_date' => $due_date,
                        'installment_number' => $i,
                        'amount' => $request->installment_amount / $request->installment_months,
                        'payment_status' => 'pending',
                        'sale_date' => $request->start_date,
                        'sale_status' => 'pending',
                        'created_by' => Auth::id(),
                    ]);
                }
            }
            
            DB::commit();
            
            return redirect('delivery-installment.index')->with('message', __('db.Delivery installment updated successfully'));
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delivery installment update failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Delivery installment update failed: ' . $e->getMessage());
        }
    }
    
    public function destroy($id)
    {
        $role = Role::find(Auth::user()->role_id);
        
        if (!$role->hasPermissionTo('delivery-installments-delete')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
        
        try {
            DB::beginTransaction();
            
            $lims_installment_data = Sale::findOrFail($id);
            
            // Delete related installment details
            Sale::where('installment_parent_id', $id)->delete();
            
            // Delete the installment record
            $lims_installment_data->delete();
            
            DB::commit();
            
            return redirect('delivery-installment.index')->with('message', __('db.Delivery installment deleted successfully'));
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delivery installment deletion failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Delivery installment deletion failed: ' . $e->getMessage());
        }
    }
    
    public function installmentData(Request $request)
    {
        $columns = array(
            0 => 'id',
            1 => 'reference_no',
            2 => 'customer',
            3 => 'warehouse',
            4 => 'delivery_man',
            5 => 'start_date',
            6 => 'installment_amount',
            7 => 'installment_months',
            8 => 'payment_status',
        );
        
        $totalData = Sale::whereNotNull('installment_parent_id')->count();
        $totalFiltered = $totalData;
        
        if ($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        
        $query = Sale::with(['customer', 'warehouse', 'deliveryMan', 'route'])
            ->whereNotNull('installment_parent_id');
        
        if (!empty($request->input('search.value'))) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('reference_no', 'LIKE', "%{$search}%")
                  ->orWhereHas('customer', function ($q2) use ($search) {
                      $q2->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }
        
        $installments = $query->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir)
            ->get();
        
        $totalFiltered = $query->count();
        
        $data = array();
        if (!empty($installments)) {
            foreach ($installments as $key => $installment) {
                $nestedData['id'] = $installment->id;
                $nestedData['key'] = $key;
                $nestedData['reference_no'] = $installment->reference_no;
                $nestedData['customer'] = $installment->customer ? $installment->customer->name : 'N/A';
                $nestedData['warehouse'] = $installment->warehouse ? $installment->warehouse->name : 'N/A';
                $nestedData['delivery_man'] = $installment->deliveryMan ? $installment->deliveryMan->name : 'N/A';
                $nestedData['route'] = $installment->route ? $installment->route->name : 'N/A';
                $nestedData['start_date'] = date(config('date_format'), strtotime($installment->sale_date));
                $nestedData['installment_amount'] = $installment->installment_amount;
                $nestedData['installment_months'] = $installment->installment_months ?? 0;
                $nestedData['payment_status'] = ucfirst($installment->payment_status);
                $nestedData['options'] = '<div class="btn-group">
                              <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.__('db.action').'
                                <span class="caret"></span>
                                <span class="sr-only">Toggle Dropdown</span>
                              </button>
                               <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                                   <li>
                                       <a href="' . route("delivery-installment.show", $installment->id) . '" class="btn btn-link"><i class="ti ti-eye"></i> '.__("db.View").'</a>
                                   </li>
                                   <li>
                                       <a href="' . route("delivery-installment.edit", $installment->id) . '" class="btn btn-link"><i class="ti ti-edit"></i> '.__("db.edit").'</a>
                                   </li>
                                   <li class="divider"></li>
                                   <li>
                                       <button type="button" class="toggle-status btn btn-link" data-id="'.$installment->id.'"><i class="ti ti-toggle-left"></i> '.__("db.Toggle Status").'</button>
                                   </li>
                                   <li class="divider"></li>
                                   <form action="' . route("delivery-installment.delete", $installment->id) . '" method="POST">'.csrf_field().'' . method_field("POST") . '
                                   <li>
                                     <button type="submit" class="btn btn-link confirm-delete-btn" data-id="'.$installment->id.'" data-name="'.$installment->reference_no.'"><i class="ti ti-trash"></i> '.__("db.delete").'</button>
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
}
