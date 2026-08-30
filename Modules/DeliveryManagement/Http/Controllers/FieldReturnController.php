<?php

namespace Modules\DeliveryManagement\Http\Controllers;

use App\Http\Controllers\Controller;

use Modules\DeliveryManagement\Models\FieldReturn;
use Modules\DeliveryManagement\Models\FieldReturnProduct;
use Modules\DeliveryManagement\Models\FieldOrder;
use Modules\DeliveryManagement\Models\DeliveryMan;
use App\Models\Product;
use App\Models\Warehouse;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\CacheForget;

class FieldReturnController extends Controller
{
    use \App\Traits\CacheForget;

    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('field-returns-index')) {
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if (empty($all_permission))
                $all_permission[] = 'dummy text';

            $lims_return_list = FieldReturn::with(['deliveryMan', 'customer', 'fieldOrder'])->get();
            $lims_delivery_man_list = DeliveryMan::active()->get();

            return view('backend.delivery_management.field_return.index', compact('lims_return_list', 'lims_delivery_man_list', 'all_permission'));
        } else {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
    }

    public function create($field_order_id)
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('field-returns-add')) {
            $lims_field_order_data = FieldOrder::with(['deliveryMan', 'customer', 'products.product'])->findOrFail($field_order_id);
            $lims_delivery_man_list = DeliveryMan::active()->get();

            return view('backend.delivery_management.field_return.create', compact('lims_field_order_data', 'lims_delivery_man_list'));
        } else {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
    }

    public function store(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('field-returns-add')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $this->validate($request, [
            'field_order_id' => 'required|exists:field_orders,id',
            'delivery_man_id' => 'required|exists:delivery_men,id',
            'reason' => 'required|max:255',
            'products' => 'required|array|min:1',
        ]);

        $data = $request->all();
        $lims_field_order_data = FieldOrder::findOrFail($data['field_order_id']);

        try {
            DB::beginTransaction();

            $fieldReturn = FieldReturn::create([
                'reference_no' => 'FR-' . date("Ymd") . '-' . date("his"),
                'field_order_id' => $data['field_order_id'],
                'delivery_man_id' => $data['delivery_man_id'],
                'customer_id' => $lims_field_order_data->customer_id,
                'reason' => $data['reason'],
                'status' => 'pending',
                'note' => $data['note'] ?? null,
                'refund_amount' => $data['refund_amount'] ?? 0,
                'created_by' => Auth::id(),
            ]);

            foreach ($data['products'] as $product) {
                FieldReturnProduct::create([
                    'field_return_id' => $fieldReturn->id,
                    'product_id' => $product['product_id'] ?? null,
                    'product_variant_id' => $product['product_variant_id'] ?? null,
                    'product_batch_id' => $product['product_batch_id'] ?? null,
                    'code' => $product['code'] ?? null,
                    'name' => $product['name'],
                    'qty' => $product['qty'],
                    'unit_price' => $product['unit_price'],
                    'sub_total' => $product['qty'] * $product['unit_price'],
                    'note' => $product['note'] ?? null,
                    'photo' => $product['photo'] ?? null,
                ]);
            }

            DB::commit();
            $this->cacheForget('field_return_list');

            return redirect('field-returns')->with('message', __('db.Field return created successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Field return creation failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Field return creation failed: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $lims_return_data = FieldReturn::with(['deliveryMan', 'customer', 'fieldOrder', 'products.product'])->findOrFail($id);
        return view('backend.delivery_management.field_return.view', compact('lims_return_data'));
    }

    public function confirm($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('field-returns-edit')) {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $lims_return_data = FieldReturn::with('products')->findOrFail($id);

        try {
            DB::beginTransaction();

            $lims_return_data->status = 'confirmed';
            $lims_return_data->save();

            foreach ($lims_return_data->products as $returnProduct) {
                $product = Product::find($returnProduct->product_id);
                if ($product) {
                    $product->qty += $returnProduct->qty;
                    $product->save();
                }
            }

            DB::commit();
            $this->cacheForget('field_return_list');

            return redirect('field-returns')->with('message', __('db.Field return confirmed successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Field return confirmation failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Field return confirmation failed: ' . $e->getMessage());
        }
    }

    public function getReasons()
    {
        $reasons = [
            'damaged', 'defective', 'wrong_item', 'expired', 'customer_request',
            'quality_issue', 'packaging_damaged', 'other'
        ];

        return response()->json($reasons);
    }

    public function uploadPhoto(Request $request)
    {
        if ($request->hasFile('photo')) {
            $image = $request->file('photo');
            $ext = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
            $imageName = date("Ymdhis");
            if (!config('database.connections.saleprosaas_landlord')) {
                $imageName = $imageName . '.' . $ext;
                $image->move(public_path('images/field_return'), $imageName);
            } else {
                $imageName = 'tenant_' . $imageName . '.' . $ext;
                $image->move(public_path('images/field_return'), $imageName);
            }

            return response()->json(['success' => true, 'image_name' => $imageName]);
        }

        return response()->json(['success' => false, 'message' => 'No image uploaded']);
    }

    public function createReplacement($id)
    {
        $lims_return_data = FieldReturn::with('fieldOrder', 'products')->findOrFail($id);

        try {
            DB::beginTransaction();

            $newFieldOrder = FieldOrder::create([
                'reference_no' => 'FO-' . date("Ymd") . '-' . date("his"),
                'delivery_man_id' => $lims_return_data->delivery_man_id,
                'customer_id' => $lims_return_data->customer_id,
                'warehouse_id' => $lims_return_data->fieldOrder->warehouse_id,
                'sale_id' => $lims_return_data->fieldOrder->sale_id,
                'status' => 'pending',
                'order_type' => 'replacement',
                'sub_total' => 0,
                'grand_total' => 0,
                'paid_amount' => 0,
                'due_amount' => 0,
                'created_by' => Auth::id(),
            ]);

            foreach ($lims_return_data->products as $returnProduct) {
                FieldOrderProduct::create([
                    'field_order_id' => $newFieldOrder->id,
                    'product_id' => $returnProduct->product_id,
                    'product_variant_id' => $returnProduct->product_variant_id,
                    'product_batch_id' => $returnProduct->product_batch_id,
                    'code' => $returnProduct->code,
                    'name' => $returnProduct->name,
                    'qty' => $returnProduct->qty,
                    'unit_price' => $returnProduct->unit_price,
                    'sub_total' => $returnProduct->qty * $returnProduct->unit_price,
                ]);
            }

            $lims_return_data->status = 'replacement_created';
            $lims_return_data->save();

            DB::commit();
            $this->cacheForget('field_return_list');

            return redirect('field-returns')->with('message', __('db.Replacement order created successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Replacement order creation failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Replacement order creation failed: ' . $e->getMessage());
        }
    }
}
