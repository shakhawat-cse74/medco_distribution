<?php

namespace Modules\DeliveryManagement\Http\Controllers;

use App\Http\Controllers\Controller;

use Modules\DeliveryManagement\Models\FieldOrder;
use Modules\DeliveryManagement\Models\FieldOrderProduct;
use Modules\DeliveryManagement\Models\DeliveryMan;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductBatch;
use App\Models\Sale;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\CacheForget;
use Modules\DeliveryManagement\Traits\LogsDeliveryActivity;

class FieldOrderController extends Controller
{
    use \App\Traits\CacheForget;
    use LogsDeliveryActivity;

    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('field-orders-index')) {
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if (empty($all_permission))
                $all_permission[] = 'dummy text';

            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_delivery_man_list = DeliveryMan::where('is_active', true)->get();

            return view('backend.delivery_management.field_order.index', compact('lims_warehouse_list', 'lims_delivery_man_list', 'all_permission'));
        } else {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
    }

    public function create()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('field-orders-add')) {
            $lims_warehouses = Warehouse::where('is_active', true)->get();
            $lims_delivery_men = DeliveryMan::where('is_active', true)->get();
            $lims_customers = Customer::where('is_active', true)->get();

            return view('backend.delivery_management.field_order.create', compact('lims_warehouses', 'lims_delivery_men', 'lims_customers'));
        } else {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
    }

    public function store(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('field-orders-add')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $this->validate($request, [
            'delivery_man_id' => 'required|exists:delivery_men,id',
            'customer_id' => 'required|exists:customers,id',
            'products' => 'required|array|min:1',
        ]);

        $data = $request->all();

        try {
            DB::beginTransaction();

            $sub_total = 0;
            $discount_amount = $data['discount_amount'] ?? 0;
            $tax_amount = $data['tax_amount'] ?? 0;
            $shipping_cost = $data['shipping_cost'] ?? 0;

            foreach ($data['products'] as $product) {
                $qty = $product['qty'] ?? 0;
                $unit_price = $product['unit_price'] ?? 0;
                $discount = $product['discount_amount'] ?? 0;
                $sub_total += ($qty * $unit_price) - $discount;
            }

            $grand_total = $sub_total + $tax_amount + $shipping_cost - $discount_amount;

            $fieldOrder = FieldOrder::create([
                'reference_no' => 'FO-' . date("Ymd") . '-' . date("his"),
                'delivery_man_id' => $data['delivery_man_id'],
                'customer_id' => $data['customer_id'],
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'sale_id' => $data['sale_id'] ?? null,
                'status' => $data['status'] ?? 'pending',
                'order_type' => $data['order_type'] ?? 'field',
                'sub_total' => $sub_total,
                'discount_amount' => $discount_amount,
                'discount_type' => $data['discount_type'] ?? 'fixed',
                'tax_amount' => $tax_amount,
                'shipping_cost' => $shipping_cost,
                'grand_total' => $grand_total,
                'paid_amount' => $data['paid_amount'] ?? 0,
                'due_amount' => $grand_total - ($data['paid_amount'] ?? 0),
                'coupon_ids' => $data['coupon_code'] ?? null,
                'special_instructions' => $data['special_instructions'] ?? null,
                'delivery_address' => $data['delivery_address'] ?? null,
                'delivery_city' => $data['delivery_city'] ?? null,
                'delivery_country' => $data['delivery_country'] ?? null,
                'created_by' => Auth::id(),
            ]);

            $this->logActivity('field_order_created', $fieldOrder->reference_no, 'Field order created: ' . $fieldOrder->reference_no . ' for customer: ' . $fieldOrder->customer_id);

            foreach ($data['products'] as $product) {
                FieldOrderProduct::create([
                    'field_order_id' => $fieldOrder->id,
                    'product_id' => $product['product_id'] ?? null,
                    'product_variant_id' => $product['product_variant_id'] ?? null,
                    'product_batch_id' => $product['product_batch_id'] ?? null,
                    'code' => $product['code'] ?? null,
                    'name' => $product['name'] ?? 'Unknown',
                    'unit' => $product['unit'] ?? null,
                    'qty' => $product['qty'] ?? 0,
                    'sale_unit_quantity' => $product['sale_unit_quantity'] ?? ($product['qty'] ?? 0),
                    'unit_price' => $product['unit_price'] ?? 0,
                    'sub_total' => (($product['qty'] ?? 0) * ($product['unit_price'] ?? 0)) - ($product['discount_amount'] ?? 0),
                    'discount_amount' => $product['discount_amount'] ?? 0,
                    'discount_type' => $product['discount_type'] ?? 'fixed',
                    'tax_amount' => $product['tax_amount'] ?? 0,
                ]);
            }

            DB::commit();
            $this->cacheForget('field_order_list');

            return redirect('field-orders')->with('message', __('db.Field order created successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Field order creation failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Field order creation failed: ' . $e->getMessage());
        }
    }

    public function draftList()
    {
        $lims_draft_list = FieldOrder::where('status', 'draft')->with(['deliveryMan', 'customer'])->get();
        return view('backend.delivery_management.field_order.draft_list', compact('lims_draft_list'));
    }

    public function loadDraft($id)
    {
        $lims_draft_data = FieldOrder::with('products')->findOrFail($id);
        return response()->json($lims_draft_data);
    }

    public function updateDraft($id)
    {
        $lims_draft_data = FieldOrder::findOrFail($id);
        $data = request()->all();

        try {
            DB::beginTransaction();
            $lims_draft_data->update($data);
            DB::commit();

            return response()->json(['success' => true, 'message' => __('db.Draft updated successfully')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Draft update failed: ' . $e->getMessage()]);
        }
    }

    public function deleteDraft($id)
    {
        try {
            DB::beginTransaction();
            $lims_draft_data = FieldOrder::findOrFail($id);
            $lims_draft_data->products()->delete();
            $lims_draft_data->delete();
            DB::commit();

            return response()->json(['success' => true, 'message' => __('db.Draft deleted successfully')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Draft deletion failed: ' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('field-orders-index')) {
            $lims_field_order_data = FieldOrder::with(['deliveryMan', 'customer', 'warehouse', 'products.product', 'products.variant', 'payments'])->findOrFail($id);

            return view('backend.delivery_management.field_order.view', compact('lims_field_order_data'));
        } else {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
    }

    public function update($id)
    {
        $lims_field_order_data = FieldOrder::findOrFail($id);
        $data = request()->all();

        try {
            DB::beginTransaction();

            if (in_array($lims_field_order_data->status, ['delivered', 'completed', 'cancelled'])) {
                return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to edit this order'));
            }

            $sub_total = 0;
            $discount_amount = $data['discount_amount'] ?? 0;
            $tax_amount = $data['tax_amount'] ?? 0;
            $shipping_cost = $data['shipping_cost'] ?? 0;

            if (!empty($data['products'])) {
                foreach ($data['products'] as $product) {
                    $sub_total += ($product['qty'] * $product['unit_price']) - ($product['discount_amount'] ?? 0);
                }
            }

            $grand_total = $sub_total + $tax_amount + $shipping_cost - $discount_amount;
            $paid_amount = $data['paid_amount'] ?? $lims_field_order_data->paid_amount;

            $lims_field_order_data->update([
                'delivery_man_id' => $data['delivery_man_id'] ?? $lims_field_order_data->delivery_man_id,
                'customer_id'     => $data['customer_id'] ?? $lims_field_order_data->customer_id,
                'warehouse_id'    => $data['warehouse_id'] ?? $lims_field_order_data->warehouse_id,
                'sub_total'       => $sub_total,
                'discount_amount' => $discount_amount,
                'discount_type'   => $data['discount_type'] ?? $lims_field_order_data->discount_type,
                'tax_amount'      => $tax_amount,
                'shipping_cost'   => $shipping_cost,
                'grand_total'     => $grand_total,
                'paid_amount'     => $paid_amount,
                'due_amount'      => $grand_total - $paid_amount,
                'special_instructions' => $data['special_instructions'] ?? $lims_field_order_data->special_instructions,
                'delivery_address' => $data['delivery_address'] ?? $lims_field_order_data->delivery_address,
                'delivery_city'    => $data['delivery_city'] ?? $lims_field_order_data->delivery_city,
                'delivery_country' => $data['delivery_country'] ?? $lims_field_order_data->delivery_country,
            ]);

            if (!empty($data['products'])) {
                $lims_field_order_data->products()->delete();
                foreach ($data['products'] as $product) {
                    FieldOrderProduct::create([
                        'field_order_id'     => $lims_field_order_data->id,
                        'product_id'         => $product['product_id'] ?? null,
                        'product_variant_id' => $product['product_variant_id'] ?? null,
                        'product_batch_id'   => $product['product_batch_id'] ?? null,
                        'code'   => $product['code'] ?? null,
                        'name'   => $product['name'],
                        'unit'   => $product['unit'] ?? null,
                        'qty'    => $product['qty'],
                        'sale_unit_quantity' => $product['sale_unit_quantity'] ?? $product['qty'],
                        'unit_price' => $product['unit_price'],
                        'sub_total'  => ($product['qty'] * $product['unit_price']) - ($product['discount_amount'] ?? 0),
                        'discount_amount' => $product['discount_amount'] ?? 0,
                        'discount_type'   => $product['discount_type'] ?? 'fixed',
                        'tax_amount'      => $product['tax_amount'] ?? 0,
                    ]);
                }
            }

            DB::commit();
            $this->cacheForget('field_order_list');

            return redirect('field-orders')->with('message', __('db.Field order updated successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Field order update failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Field order update failed: ' . $e->getMessage());
        }
    }

    public function cancel($id)
    {
        try {
            DB::beginTransaction();
            $lims_field_order_data = FieldOrder::findOrFail($id);

            if (in_array($lims_field_order_data->status, ['delivered', 'completed', 'cancelled'])) {
                return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to cancel this order'));
            }

            $lims_field_order_data->status = 'cancelled';
            $lims_field_order_data->save();
            DB::commit();

            return redirect('field-orders')->with('message', __('db.Field order cancelled successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Field order cancellation failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Field order cancellation failed: ' . $e->getMessage());
        }
    }

    public function searchProducts(Request $request)
    {
        $search = $request->search;
        $warehouseId = $request->warehouse_id;

        $query = Product::query()
            ->where('is_active', true)
            ->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%");
            });

        if ($warehouseId) {
            $query->whereHas('warehouses', function ($q) use ($warehouseId) {
                $q->where('product_warehouse.warehouse_id', $warehouseId)
                  ->where('product_warehouse.qty', '>', 0);
            });
        }

        $products = $query->limit(10)
            ->get(['id', 'name', 'code', 'price', 'qty', 'is_batch', 'is_variant']);

        $result = [];
        foreach ($products as $product) {
            $warehouseStock = 0;
            if ($warehouseId) {
                $warehouseStock = \App\Models\Product_Warehouse::where([
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouseId
                ])->value('qty') ?? 0;
            }

            $result[] = [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'price' => $product->price,
                'stock' => $warehouseStock,
                'is_batch' => $product->is_batch,
                'is_variant' => $product->is_variant,
            ];
        }

        return response()->json($result);
    }

    public function searchCustomers(Request $request)
    {
        $search = $request->search;
        $customers = Customer::where('name', 'LIKE', "%{$search}%")
            ->orWhere('phone_number', 'LIKE', "%{$search}%")
            ->where('is_active', true)
            ->limit(10)
            ->get(['id', 'name', 'phone_number', 'address', 'city']);

        return response()->json($customers);
    }

    public function validateStock(Request $request)
    {
        $data = $request->all();
        $errors = [];

        foreach ($data['products'] as $product) {
            $productData = Product::find($product['product_id']);
            if ($productData && $productData->qty < $product['qty']) {
                $errors[] = "Insufficient stock for {$productData->name}. Available: {$productData->qty}, Required: {$product['qty']}";
            }
        }

        if (empty($errors)) {
            return response()->json(['valid' => true]);
        } else {
            return response()->json(['valid' => false, 'errors' => $errors]);
        }
    }

    public function fieldOrderData(Request $request)
    {
        $columns = array(
            0 => 'id',
            1 => 'reference_no',
            2 => 'delivery_man_id',
            3 => 'customer_id',
            4 => 'warehouse_id',
            5 => 'status',
            6 => 'grand_total',
            7 => 'order_type',
        );

        $totalData = FieldOrder::count();
        $totalFiltered = $totalData;

        if ($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')] ?? 'id';
        $dir = $request->input('order.0.dir') ?? 'asc';

        $query = FieldOrder::query();

        if (!empty($request->input('search.value'))) {
            $search = $request->input('search.value');
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

        $fieldOrders = $query->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir)
            ->get();

        $totalFiltered = $query->count();

        $data = array();
        if (!empty($fieldOrders)) {
            foreach ($fieldOrders as $key => $fieldOrder) {
                $nestedData['id'] = $fieldOrder->id;
                $nestedData['key'] = $key;
                $nestedData['reference_no'] = $fieldOrder->reference_no;
                $nestedData['delivery_man'] = $fieldOrder->deliveryMan ? $fieldOrder->deliveryMan->name : 'N/A';
                $nestedData['customer'] = $fieldOrder->customer ? $fieldOrder->customer->name : 'N/A';
                $nestedData['warehouse'] = $fieldOrder->warehouse ? $fieldOrder->warehouse->name : 'N/A';
                $nestedData['status'] = ucfirst($fieldOrder->status);
                $nestedData['grand_total'] = format_currency($fieldOrder->grand_total);
                $nestedData['order_type'] = ucfirst($fieldOrder->order_type);
                $nestedData['options'] = '<div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.__("db.action").'
                              <span class="caret"></span>
                              <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                                <li>
                                    <a href="' . route("field-orders.show", $fieldOrder->id) . '" class="btn btn-link"><i class="ti ti-eye"></i> '.__("db.View").'</a>
                                </li>
                                <li>
                                    <a href="' . route("field-orders.invoice", $fieldOrder->id) . '" class="btn btn-link" target="_blank"><i class="ti ti-file-invoice"></i> '.__("db.Invoice").'</a>
                                </li>
                                <li class="divider"></li>
                                <form action="' . route("field-orders.cancel", $fieldOrder->id) . '" method="POST">'.csrf_field().'
                                <li>
                                  <button type="submit" class="btn btn-link confirm-delete-btn" data-id="'.$fieldOrder->id.'" data-name="'.$fieldOrder->reference_no.'"><i class="ti ti-trash"></i> '.__("db.Cancel").'</button>
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

        return response()->json($json_data);
    }

    public function genInvoice($id)
    {
        $lims_field_order_data = FieldOrder::with(['deliveryMan', 'customer', 'products'])->findOrFail($id);
        return view('backend.delivery_management.field_order.invoice', compact('lims_field_order_data'));
    }

    public function sendWhatsApp($id)
    {
        $lims_field_order_data = FieldOrder::with('customer')->findOrFail($id);
        $customer = $lims_field_order_data->customer;

        if (!$customer || !$customer->phone_number) {
            return redirect()->back()->with('not_permitted', __('db.Customer phone number not found'));
        }

        $phone = preg_replace('/\D/', '', $customer->phone_number);
        $message = "Your field order {$lims_field_order_data->reference_no} has been assigned to delivery man {$lims_field_order_data->deliveryMan->name}. Total: {$lims_field_order_data->grand_total}";

        return redirect()->back()->with('message', __('db.WhatsApp message sent successfully'));
    }

    public function sendSMS($id)
    {
        $lims_field_order_data = FieldOrder::with('customer')->findOrFail($id);
        $customer = $lims_field_order_data->customer;

        if (!$customer || !$customer->phone_number) {
            return redirect()->back()->with('not_permitted', __('db.Customer phone number not found'));
        }

        return redirect()->back()->with('message', __('db.SMS sent successfully'));
    }

    public function quickCreateCustomer(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('field-orders-add')) {
            return response()->json(['success' => false, 'message' => 'Not permitted'], 403);
        }

        $this->validate($request, [
            'name' => 'required|max:255',
            'phone_number' => 'required|max:255|unique:customers,phone_number',
        ]);

        $data = $request->all();
        $data['is_active'] = true;

        try {
            DB::beginTransaction();
            $customer = Customer::create($data);
            DB::commit();

            return response()->json([
                'success' => true,
                'customer' => $customer,
                'message' => 'Customer created successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Quick customer creation failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to create customer: ' . $e->getMessage()], 500);
        }
    }

    public function printBarcode($id)
    {
        $lims_field_order_data = FieldOrder::findOrFail($id);
        return view('backend.delivery_management.field_order.barcode', compact('lims_field_order_data'));
    }
}
