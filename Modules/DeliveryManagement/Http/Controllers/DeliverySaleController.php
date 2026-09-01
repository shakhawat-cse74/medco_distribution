<?php

namespace Modules\DeliveryManagement\Http\Controllers;

use App\Http\Controllers\Controller;

use Modules\DeliveryManagement\Models\FieldOrder;
use Modules\DeliveryManagement\Models\DeliveryMan;
use Modules\DeliveryManagement\Models\FieldOrderProduct;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductBatch;
use App\Models\Sale;
use App\Models\User;
use App\Models\Biller;
use App\Models\DeliveryArea;
use App\Models\PosSetting;
use App\Models\RewardPointSetting;
use App\Models\Account;
use App\Models\CustomerGroup;
use App\Models\CustomField;
use App\Models\Tax;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;

class DeliverySaleController extends Controller
{
    public function index()
    {
        $role = Role::find(Auth::user()->role_id);

        if ($role->hasPermissionTo('delivery-sales-index')) {
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if (empty($all_permission))
                $all_permission[] = 'dummy text';

            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_delivery_man_list = DeliveryMan::active()->with('routes')->get();
            $lims_route_list = DeliveryArea::active()->get();

            $starting_date = date('Y-m-d', strtotime('-30 days'));
            $ending_date = date('Y-m-d');

            return view('backend.delivery_management.delivery_sale.index', compact(
                'lims_warehouse_list',
                'lims_delivery_man_list',
                'lims_route_list',
                'all_permission',
                'starting_date',
                'ending_date'
            ));
        } else {
            return redirect()->back()->with('not_permitted', __('dbSorry! You are not allowed to access this module'));
        }
    }
    
    public function create()
    {
        $role = Role::find(Auth::user()->role_id);
        
        if (!$role->hasPermissionTo('delivery-sales-add')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
        
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_customer_list = Customer::where('is_active', true)->get();
        $lims_biller_list = Biller::where('is_active', true)->get();
        $lims_route_list = DeliveryArea::active()->get();
        $lims_tax_list = Tax::where('is_active', true)->get();
        $currency_list = cache()->get('currency_list');
        $lims_pos_setting_data = PosSetting::latest()->first();
        $lims_reward_point_setting_data = RewardPointSetting::first();
        $lims_account_list = Account::where('is_active', true)->get();
        $lims_customer_group_all = CustomerGroup::where('is_active', true)->get();
        $custom_fields = CustomField::where('belongs_to', 'sale')->get();
        
        if ($lims_pos_setting_data)
            $options = explode(',', $lims_pos_setting_data->payment_options);
        else
            $options = ['cash'];

        $all_permission = Role::findByName($role->name)->permissions->pluck('name');

        $lims_delivery_man_list = DeliveryMan::active()->with('routes')->get()->map(function($dm) {
            $dm->assigned_routes = $dm->routes->pluck('id')->toArray();
            return $dm;
        });

        return view('backend.delivery_management.delivery_sale.create', compact(
            'lims_warehouse_list',
            'lims_customer_list',
            'lims_biller_list',
            'lims_delivery_man_list',
            'lims_route_list',
            'lims_tax_list',
            'currency_list',
            'lims_pos_setting_data',
            'lims_reward_point_setting_data',
            'lims_account_list',
            'lims_customer_group_all',
            'custom_fields',
            'all_permission',
            'options'
        ));
    }
    
    public function getWarehouseProducts($warehouse_id)
    {
        $query = Product::leftJoin('product_warehouse', function ($join) use ($warehouse_id) {
            $join->on('products.id', '=', 'product_warehouse.product_id')
                ->where('product_warehouse.warehouse_id', '=', $warehouse_id);
        })
            ->leftJoin('product_batches', 'product_warehouse.product_batch_id', '=', 'product_batches.id')
            ->where('products.is_active', true)
            ->where('products.type', '!=', 'combo')
            ->where(function ($q) {
                $q->whereNull('products.is_imei')
                    ->orWhere('products.is_imei', 0);
            });

        if (config('without_stock') == 'no') {
            $query = $query->where(function ($q) {
                $q->where('product_warehouse.qty', '>', 0)
                    ->orWhere('products.qty', '>', 0)
                    ->orWhereIn('products.type', ['service', 'digital']);
            });
        }

        $query = $query->where(function ($q) {
            $q->whereNull('product_warehouse.product_batch_id')
                ->orWhereNull('product_batches.expired_date')
                ->orWhereDate('product_batches.expired_date', '>=', now()->toDateString());
        });

        $products = $query->groupBy(
            'products.id',
            'product_warehouse.product_batch_id',
            'product_batches.expired_date'
        )
        ->select(
            'products.id',
            'products.name',
            'products.code',
            'products.type',
            'products.is_imei',
            'products.is_variant',
            'products.is_embeded',
            'products.is_batch',
            'products.price',
            DB::raw('COALESCE(product_warehouse.qty, products.qty) as qty'),
            'product_warehouse.product_batch_id'
        )
        ->get();

        return response()->json($products);
    }
    
    public function store(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        
        if (!$role->hasPermissionTo('delivery-sales-add')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
        
        $this->validate($request, [
            'warehouse_id' => 'required|exists:warehouses,id',
            'customer_id' => 'required|exists:customers,id',
            'route_id' => 'required|exists:delivery_areas,id',
            'delivery_man_id' => 'required|exists:delivery_men,id',
            'payment_status' => 'required|in:1,2,3,4',
            'sale_status' => 'required|in:1,2,3',
        ]);
        
        $data = $request->all();
        $data['reference_no'] = 'DS-' . date('Ymd') . '-' . str_pad(Sale::count() + 1, 6, '0', STR_PAD_LEFT);
        $data['created_by'] = Auth::id();
        $data['user_id'] = Auth::id();

        $document = $request->document;
        if ($document) {
            $ext = pathinfo($document->getClientOriginalName(), PATHINFO_EXTENSION);
            $documentName = date("Ymdhis") . '.' . $ext;
            $document->move(public_path('documents/sale'), $documentName);
            $data['document'] = $documentName;
        }

        try {
            DB::beginTransaction();

            $lims_sale_data = Sale::create(collect($data)->only([
                'reference_no', 'user_id', 'warehouse_id', 'customer_id', 'biller_id', 'route_id',
                'delivery_man_id', 'item', 'total_qty', 'total_discount', 'total_tax', 'total_price',
                'order_tax_rate', 'order_tax', 'order_discount_type', 'order_discount_value', 'order_discount',
                'shipping_cost', 'grand_total', 'currency_id', 'exchange_rate', 'sale_status', 'payment_status',
                'payment_mode', 'paid_amount', 'document', 'sale_note', 'staff_note', 'created_at', 'created_by'
            ])->toArray());
            
            DB::commit();
            
            return redirect('delivery-sale.show', $lims_sale_data->id)->with('message', __('db.Delivery sale created successfully'));
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delivery sale creation failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Delivery sale creation failed: ' . $e->getMessage());
        }
    }
    
    public function show($id)
    {
        $lims_sale_data = Sale::with(['customer', 'warehouse', 'biller', 'deliveryMan', 'route', 'products.product', 'payments'])->findOrFail($id);
        
        $role = Role::find(Auth::user()->role_id);
        
        if (!$role->hasPermissionTo('delivery-sales-index')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
        
        return view('backend.delivery_management.delivery_sale.show', compact('lims_sale_data'));
    }
    
    public function edit($id)
    {
        $role = Role::find(Auth::user()->role_id);
        
        if (!$role->hasPermissionTo('delivery-sales-edit')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
        
        $lims_sale_data = Sale::with(['customer', 'warehouse', 'biller', 'deliveryMan', 'route', 'products.product'])->findOrFail($id);
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_customer_list = Customer::active()->get();
        $lims_biller_list = User::where('is_active', true)->get();
        $lims_delivery_man_list = DeliveryMan::active()->get();
        $lims_route_list = DeliveryArea::active()->get();
        
        return view('backend.delivery_management.delivery_sale.edit', compact(
            'lims_sale_data',
            'lims_warehouse_list',
            'lims_customer_list',
            'lims_biller_list',
            'lims_delivery_man_list',
            'lims_route_list'
        ));
    }
    
    public function update(Request $request, $id)
    {
        $role = Role::find(Auth::user()->role_id);
        
        if (!$role->hasPermissionTo('delivery-sales-edit')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
        
        $this->validate($request, [
            'warehouse_id' => 'required|exists:warehouses,id',
            'customer_id' => 'required|exists:customers,id',
            'route_id' => 'required|exists:delivery_areas,id',
            'delivery_man_id' => 'required|exists:delivery_men,id',
            'payment_status' => 'required|in:1,2,3,4',
            'sale_status' => 'required|in:1,2,3',
        ]);
        
        $lims_sale_data = Sale::findOrFail($id);
        $data = $request->all();
        
        try {
            DB::beginTransaction();
            
            $lims_sale_data->update(collect($data)->only([
                'warehouse_id', 'customer_id', 'biller_id', 'route_id', 
                'delivery_man_id', 'sale_date', 'due_date', 'payment_status', 
                'sale_status', 'paid_amount', 'order_tax_rate', 'order_discount_value',
                'shipping_cost', 'sale_note'
            ])->toArray());
            
            DB::commit();
            
            return redirect('delivery-sale.index')->with('message', __('db.Delivery sale updated successfully'));
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delivery sale update failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Delivery sale update failed: ' . $e->getMessage());
        }
    }
    
    public function destroy($id)
    {
        $role = Role::find(Auth::user()->role_id);
        
        if (!$role->hasPermissionTo('delivery-sales-delete')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
        
        try {
            DB::beginTransaction();
            
            $lims_sale_data = Sale::findOrFail($id);
            
            // Delete related records first
            Product_Sale::where('sale_id', $id)->delete();
            Payment::where('sale_id', $id)->delete();
            
            // Delete the sale record
            $lims_sale_data->delete();
            
            DB::commit();
            
            return redirect('delivery-sale.index')->with('message', __('db.Delivery sale deleted successfully'));
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delivery sale deletion failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Delivery sale deletion failed: ' . $e->getMessage());
        }
    }
    
    public function saleData(Request $request)
    {
        $columns = array(
            0 => 'id',
            1 => 'reference_no',
            2 => 'customer',
            3 => 'warehouse',
            4 => 'delivery_man',
            5 => 'sale_date',
            6 => 'sale_status',
            7 => 'payment_status',
            8 => 'grand_total',
            9 => 'paid_amount',
            10 => 'due_amount',
        );

        $totalData = Sale::whereNotNull('delivery_man_id')->count();
        $totalFiltered = $totalData;

        if ($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        $query = Sale::with(['customer', 'warehouse', 'deliveryMan'])
            ->whereNotNull('delivery_man_id');

        if ($request->has('warehouse_id') && $request->warehouse_id) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->has('delivery_man_id') && $request->delivery_man_id) {
            $query->where('delivery_man_id', $request->delivery_man_id);
        }

        if (!empty($request->input('search.value'))) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('reference_no', 'LIKE', "%{$search}%")
                  ->orWhereHas('customer', function ($q2) use ($search) {
                      $q2->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        $sales = $query->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir)
            ->get();

        $totalFiltered = $query->count();
        
        $data = array();
        if (!empty($sales)) {
            foreach ($sales as $key => $sale) {
                $nestedData['id'] = $sale->id;
                $nestedData['key'] = $key;
                $nestedData['reference_no'] = $sale->reference_no;
                $nestedData['customer'] = $sale->customer ? $sale->customer->name : 'N/A';
                $nestedData['warehouse'] = $sale->warehouse ? $sale->warehouse->name : 'N/A';
                $nestedData['delivery_man'] = $sale->deliveryMan ? $sale->deliveryMan->name : 'N/A';
                $nestedData['sale_date'] = date(config('date_format'), strtotime($sale->sale_date));
                $nestedData['sale_status'] = ucfirst($sale->sale_status);
                $nestedData['payment_status'] = ucfirst($sale->payment_status);
                $nestedData['grand_total'] = $sale->grand_total;
                $nestedData['paid_amount'] = $sale->paid_amount;
                $nestedData['due_amount'] = $sale->due_amount;
                $nestedData['options'] = '<div class="btn-group">
                              <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.__('db.action').'
                                <span class="caret"></span>
                                <span class="sr-only">Toggle Dropdown</span>
                              </button>
                               <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                                   <li>
                                       <a href="' . route("delivery-sale.show", $sale->id) . '" class="btn btn-link"><i class="ti ti-eye"></i> '.__("db.View").'</a>
                                   </li>
                                   <li>
                                       <a href="' . route("delivery-sale.edit", $sale->id) . '" class="btn btn-link"><i class="ti ti-edit"></i> '.__("db.edit").'</a>
                                   </li>
                                   <li class="divider"></li>
                                   <li>
                                       <button type="button" class="toggle-status btn btn-link" data-id="'.$sale->id.'"><i class="ti ti-toggle-left"></i> '.__("db.Toggle Status").'</button>
                                   </li>
                                   <li class="divider"></li>
                                   <form action="' . route("delivery-sale.delete", $sale->id) . '" method="POST">'.csrf_field().'' . method_field("POST") . '
                                   <li>
                                     <button type="submit" class="btn btn-link confirm-delete-btn" data-id="'.$sale->id.'" data-name="'.$sale->reference_no.'"><i class="ti ti-trash"></i> '.__("db.delete").'</button>
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
    
    public function pos()
    {
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_customer_list = Customer::active()->get();
        $lims_biller_list = User::where('is_active', true)->get();
        $lims_delivery_man_list = DeliveryMan::active()->get();
        $lims_route_list = DeliveryArea::active()->get();
        
        return view('backend.delivery_management.delivery_sale.pos', compact(
            'lims_warehouse_list',
            'lims_customer_list',
            'lims_biller_list',
            'lims_delivery_man_list',
            'lims_route_list'
        ));
    }
    
    public function challanList()
    {
        $role = Role::find(Auth::user()->role_id);
        
        if (!$role->hasPermissionTo('delivery-sales-challan-list')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
        
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_delivery_man_list = DeliveryMan::active()->get();
        $lims_route_list = DeliveryArea::active()->get();
        
        return view('backend.delivery_management.delivery_sale.challan_list', compact(
            'lims_warehouse_list',
            'lims_delivery_man_list',
            'lims_route_list'
        ));
    }
    
    public function challanSlipList()
    {
        $role = Role::find(Auth::user()->role_id);
        
        if (!$role->hasPermissionTo('delivery-sales-challan-slip-list')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
        
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_delivery_man_list = DeliveryMan::active()->get();
        $lims_route_list = DeliveryArea::active()->get();
        
        return view('backend.delivery_management.delivery_sale.challan_slip_list', compact(
            'lims_warehouse_list',
            'lims_delivery_man_list',
            'lims_route_list'
        ));
    }
    
    public function packingSlipList()
    {
        $role = Role::find(Auth::user()->role_id);
        
        if (!$role->hasPermissionTo('delivery-sales-packing-slip-list')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
        
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_delivery_man_list = DeliveryMan::active()->get();
        $lims_route_list = DeliveryArea::active()->get();
        
        return view('backend.delivery_management.delivery_sale.packing_slip_list', compact(
            'lims_warehouse_list',
            'lims_delivery_man_list',
            'lims_route_list'
        ));
    }
    
    public function saleReturn()
    {
        $role = Role::find(Auth::user()->role_id);
        
        if (!$role->hasPermissionTo('delivery-sales-sale-return')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
        
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_delivery_man_list = DeliveryMan::active()->get();
        $lims_route_list = DeliveryArea::active()->get();
        
        return view('backend.delivery_management.delivery_sale.sale_return', compact(
            'lims_warehouse_list',
            'lims_delivery_man_list',
            'lims_route_list'
        ));
    }
    
    public function installmentList()
    {
        $role = Role::find(Auth::user()->role_id);
        
        if (!$role->hasPermissionTo('delivery-sales-installment-list')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
        
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_delivery_man_list = DeliveryMan::active()->get();
        $lims_route_list = DeliveryArea::active()->get();
        
        return view('backend.delivery_management.delivery_sale.installment_list', compact(
            'lims_warehouse_list',
            'lims_delivery_man_list',
            'lims_route_list'
        ));
    }
    
    public function couponList()
    {
        $role = Role::find(Auth::user()->role_id);
        
        if (!$role->hasPermissionTo('delivery-sales-coupon-list')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
        
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_delivery_man_list = DeliveryMan::active()->get();
        $lims_route_list = DeliveryArea::active()->get();
        
        return view('backend.delivery_management.delivery_sale.coupon_list', compact(
            'lims_warehouse_list',
            'lims_delivery_man_list',
            'lims_route_list'
        ));
    }
    
    public function cuponList()
    {
        $role = Role::find(Auth::user()->role_id);
        
        if (!$role->hasPermissionTo('delivery-sales-cupon-list')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
        
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_delivery_man_list = DeliveryMan::active()->get();
        $lims_route_list = DeliveryArea::active()->get();
        
        return view('backend.delivery_management.delivery_sale.cupon_list', compact(
            'lims_warehouse_list',
            'lims_delivery_man_list',
            'lims_route_list'
        ));
    }
    
    public function courierList()
    {
        $role = Role::find(Auth::user()->role_id);
        
        if (!$role->hasPermissionTo('delivery-sales-courier-list')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
        
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_delivery_man_list = DeliveryMan::active()->get();
        $lims_route_list = DeliveryArea::active()->get();
        
        return view('backend.delivery_management.delivery_sale.courier_list', compact(
            'lims_warehouse_list',
            'lims_delivery_man_list',
            'lims_route_list'
        ));
    }
    
    public function curirerList()
    {
        $role = Role::find(Auth::user()->role_id);
        
        if (!$role->hasPermissionTo('delivery-sales-curirer-list')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
        
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_delivery_man_list = DeliveryMan::active()->get();
        $lims_route_list = DeliveryArea::active()->get();
        
        return view('backend.delivery_management.delivery_sale.curirer_list', compact(
            'lims_warehouse_list',
            'lims_delivery_man_list',
            'lims_route_list'
        ));
    }
    
    public function deliveryList()
    {
        $role = Role::find(Auth::user()->role_id);
        
        if (!$role->hasPermissionTo('delivery-sales-delivery-list')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
        
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_delivery_man_list = DeliveryMan::active()->get();
        $lims_route_list = DeliveryArea::active()->get();
        
        return view('backend.delivery_management.delivery_sale.delivery_list', compact(
            'lims_warehouse_list',
            'lims_delivery_man_list',
            'lims_route_list'
        ));
    }
    
    public function giftCardList()
    {
        $role = Role::find(Auth::user()->role_id);
        
        if (!$role->hasPermissionTo('delivery-sales-gift-card-list')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
        
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_delivery_man_list = DeliveryMan::active()->get();
        $lims_route_list = DeliveryArea::active()->get();
        
        return view('backend.delivery_management.delivery_sale.gift_card_list', compact(
            'lims_warehouse_list',
            'lims_delivery_man_list',
            'lims_route_list'
        ));
    }
    
    public function saleExchange()
    {
        $role = Role::find(Auth::user()->role_id);
        
        if (!$role->hasPermissionTo('delivery-sales-sale-exchange')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
        
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_delivery_man_list = DeliveryMan::active()->get();
        $lims_route_list = DeliveryArea::active()->get();
        
        return view('backend.delivery_management.delivery_sale.sale_exchange', compact(
            'lims_warehouse_list',
            'lims_delivery_man_list',
            'lims_route_list'
        ));
    }
    
    public function toggleStatus($id)
    {
        $lims_sale_data = Sale::findOrFail($id);
        $lims_sale_data->sale_status = $lims_sale_data->sale_status == 'completed' ? 'pending' : 'completed';
        $lims_sale_data->save();
        
        return response()->json(['success' => true, 'message' => __('db.Status updated successfully')]);
    }
}
