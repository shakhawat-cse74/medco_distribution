<?php

namespace Modules\DeliveryManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Biller;
use App\Models\CashRegister;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\MailSetting;
use App\Models\Payment;
use App\Models\Product_Sale;
use App\Models\Product_Warehouse;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ProductReturn;
use App\Models\ProductVariant;
use App\Models\Returns;
use App\Models\RewardPointSetting;
use App\Models\Sale;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\Variant;
use App\Models\Warehouse;
use App\Traits\MailInfo;
use App\Traits\TenantInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Mail;
use Modules\DeliveryManagement\Models\DeliveryMan;
use Spatie\Permission\Models\Role;

class DeliveryReturnController extends Controller
{
    use TenantInfo, MailInfo;

    public function __construct(
        private \App\Services\AccountingService $accountingService
    ) {}

    public function index(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('delivery-sales-sale-return')) {
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission) {
                $all_permission[] = $permission->name;
            }
            if (empty($all_permission)) {
                $all_permission[] = 'dummy text';
            }

            $warehouse_id = $request->input('warehouse_id', 0);
            $delivery_man_id = $request->input('delivery_man_id', 0);

            if ($request->input('starting_date')) {
                $starting_date = $request->input('starting_date');
                $ending_date = $request->input('ending_date');
            } else {
                $starting_date = date("Y-m-d", strtotime(date('Y-m-d', strtotime('-1 year', strtotime(date('Y-m-d'))))));
                $ending_date = date("Y-m-d");
            }

            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $deliveryManIds = Sale::whereNotNull('delivery_man_id')->distinct()->pluck('delivery_man_id');
            $lims_delivery_man_list = DeliveryMan::active()->whereIn('id', $deliveryManIds)->get();

            return view('backend.delivery_management.delivery_return.index', compact(
                'starting_date',
                'ending_date',
                'warehouse_id',
                'delivery_man_id',
                'all_permission',
                'lims_warehouse_list',
                'lims_delivery_man_list'
            ));
        }

        return redirect()->back()->with('not_permitted', __('db Sorry! You are not allowed to access this module'));
    }

    public function returnData(Request $request)
    {
        $columns = [
            1 => 'returns.created_at',
            2 => 'returns.reference_no',
            3 => 'customers.name',
            4 => 'warehouses.name',
            5 => 'sales.delivery_man_id',
            6 => 'returns.grand_total',
        ];

        $warehouse_id = $request->input('warehouse_id', 0);
        $delivery_man_id = $request->input('delivery_man_id', 0);

        $query = Returns::with(['biller', 'customer', 'warehouse', 'user', 'sale.deliveryMan'])
            ->join('sales', 'returns.sale_id', '=', 'sales.id')
            ->join('customers', 'returns.customer_id', '=', 'customers.id')
            ->leftJoin('warehouses', 'returns.warehouse_id', '=', 'warehouses.id')
            ->whereNotNull('sales.delivery_man_id');

        if (Auth::user()->role_id > 2 && config('staff_access') == 'own') {
            $query->where('returns.user_id', Auth::id());
        }

        if ($warehouse_id != 0) {
            $query->where('returns.warehouse_id', $warehouse_id);
        }

        if ($delivery_man_id != 0) {
            $query->where('sales.delivery_man_id', $delivery_man_id);
        }

        $query->whereDate('returns.created_at', '>=', $request->input('starting_date'))
            ->whereDate('returns.created_at', '<=', $request->input('ending_date'));

        $totalData = $query->count();
        $totalFiltered = $totalData;

        $limit = $request->input('length') != -1 ? $request->input('length') : $totalData;
        $start = $request->input('start');
        $orderColumnIndex = $request->input('order.0.column');
        $order = $columns[$orderColumnIndex] ?? 'returns.created_at';
        $dir = $request->input('order.0.dir') ?? 'desc';

        if (!empty($request->input('search.value'))) {
            $search = $request->input('search.value');

            $query->where(function ($q) use ($search) {
                $q->where('returns.reference_no', 'LIKE', "%{$search}%")
                    ->orWhere('customers.name', 'LIKE', "%{$search}%")
                    ->orWhere('customers.phone_number', 'LIKE', "%{$search}%");
            });

            $totalFiltered = $query->distinct('returns.id')->count('returns.id');
        }

        $returns = $query->select('returns.*')
            ->with(['biller', 'customer', 'warehouse', 'user', 'sale.deliveryMan'])
            ->distinct('returns.id')
            ->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir)
            ->get();

        $data = [];
        if (!empty($returns)) {
            foreach ($returns as $key => $return) {
                $deliveryManName = 'N/A';
                if ($return->sale && $return->sale->deliveryMan) {
                    $deliveryManName = $return->sale->deliveryMan->name;
                } elseif ($return->sale && $return->sale->delivery_man_id) {
                    $deliveryMan = DeliveryMan::find($return->sale->delivery_man_id);
                    $deliveryManName = $deliveryMan ? $deliveryMan->name : 'N/A';
                }

                $nestedData['id'] = $return->id;
                $nestedData['key'] = $key;
                $nestedData['date'] = date(config('date_format'), strtotime($return->created_at->toDateString()));
                $nestedData['reference_no'] = $return->reference_no;
                $nestedData['customer'] = $return->customer ? $return->customer->name : 'N/A';
                $nestedData['warehouse'] = $return->warehouse ? $return->warehouse->name : 'N/A';
                $nestedData['delivery_man'] = $deliveryManName;
                $nestedData['grand_total'] = number_format($return->grand_total / $return->exchange_rate, config('decimal'));
                $nestedData['options'] = $this->buildActionButtons($return);
                $data[] = $nestedData;
            }
        }

        $json_data = [
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data" => $data
        ];

        return response()->json($json_data);
    }

    private function buildActionButtons($return)
    {
        $html = '<div class="btn-group">
            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' . __("db.action") . '
              <span class="caret"></span>
              <span class="sr-only">Toggle Dropdown</span>
            </button>
            <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                <li>
                    <a href="' . route('delivery-return.show', $return->id) . '" class="btn btn-link"><i class="ti ti-eye"></i> ' . __('db.View') . '</a>
                </li>
            </ul>
        </div>';

        return $html;
    }

    public function create(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('delivery-sales-sale-return')) {
            return redirect()->back()->with('not_permitted', __('db Sorry! You are not allowed to access this module'));
        }

        $lims_sale_data = Sale::whereNotNull('delivery_man_id')
            ->where('reference_no', $request->input('reference_no'))
            ->whereNull('deleted_at')
            ->first();

        if (!$lims_sale_data) {
            return redirect()->back()->with('not_permitted', __('db.Sale not found or is not a delivery sale'));
        }

        $lims_product_sale_data = Product_Sale::where('sale_id', $lims_sale_data->id)->get();
        $lims_tax_list = Tax::where('is_active', true)->get();
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_account_list = Account::where('is_active', true)->get();
        $lims_delivery_man_list = DeliveryMan::active()->get();

        return view('backend.delivery_management.delivery_return.create', compact(
            'lims_tax_list',
            'lims_sale_data',
            'lims_product_sale_data',
            'lims_warehouse_list',
            'lims_account_list',
            'lims_delivery_man_list'
        ));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->except('document', 'total_sale_discount');
            $data['reference_no'] = 'drr-' . date("Ymd") . '-' . date("his");
            $data['total_discount'] = $request->total_sale_discount;
            $data['user_id'] = Auth::id();

            $lims_sale_data = Sale::whereNotNull('delivery_man_id')
                ->whereNull('deleted_at')
                ->select('id', 'warehouse_id', 'customer_id', 'biller_id', 'currency_id', 'exchange_rate', 'sale_status', 'payment_status', 'paid_amount', 'delivery_man_id')
                ->find($data['sale_id']);

            if (!$lims_sale_data) {
                throw new \Exception('Delivery sale not found');
            }

            $data['user_id'] = Auth::id();
            $data['customer_id'] = $lims_sale_data->customer_id;
            $data['warehouse_id'] = $lims_sale_data->warehouse_id;
            $data['biller_id'] = $lims_sale_data->biller_id;
            $data['currency_id'] = $lims_sale_data->currency_id;
            $data['exchange_rate'] = $lims_sale_data->exchange_rate;

            $refund = $request->refund ?? 0;
            $hasRefund = $refund && $lims_sale_data->paid_amount > 0;

            if ($hasRefund) {
                if (empty($data['account_id'])) {
                    $lims_account_data = Account::where('is_default', true)->first();
                    $data['account_id'] = $lims_account_data?->id;
                }
            } else {
                $data['account_id'] = null;
            }

            $document = $request->document;
            if ($document) {
                $v = Validator::make(
                    ['extension' => strtolower($request->document->getClientOriginalExtension())],
                    ['extension' => 'in:jpg,jpeg,png,gif,pdf,csv,docx,xlsx,txt']
                );
                if ($v->fails()) {
                    return redirect()->back()->withErrors($v->errors());
                }

                $ext = pathinfo($document->getClientOriginalName(), PATHINFO_EXTENSION);
                $documentName = date("Ymdhis") . '.' . $ext;
                $document->move(public_path('documents/sale_return'), $documentName);
                $data['document'] = $documentName;
            }

            $lims_return_data = Returns::create($data);

            if ($hasRefund) {
                $cash_register_data = CashRegister::where([
                    ['user_id', $data['user_id']],
                    ['warehouse_id', $data['warehouse_id']],
                    ['status', true]
                ])->first();

                if ($cash_register_data) {
                    $data['cash_register_id'] = $cash_register_data->id;
                }

                $refund_amount = $request->refund_amount ?? $lims_sale_data->paid_amount;
                $paying_method = $request->paying_method ?? 'Cash';
                $payment_reference = 'dpr-' . date("Ymd") . '-' . date("his");

                $payment_data = [
                    'payment_reference' => $payment_reference,
                    'sale_id' => $data['sale_id'],
                    'return_id' => $lims_return_data->id,
                    'cash_register_id' => $data['cash_register_id'] ?? null,
                    'user_id' => Auth::id(),
                    'account_id' => $data['account_id'],
                    'amount' => $refund_amount,
                    'paying_method' => $paying_method,
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                $refundPayment = Payment::create($payment_data);
                $this->accountingService->recordPayment($refundPayment);
            }

            $product_sale_ids = $data['product_sale_id'] ?? [];
            $imei_number = $data['imei_number'] ?? [];
            $product_batch_id = $data['product_batch_id'] ?? [];
            $product_code = $data['product_code'] ?? [];
            $qty = $data['qty'] ?? [];
            $sale_unit = $data['sale_unit'] ?? [];
            $net_unit_price = $data['net_unit_price'] ?? [];
            $discount = $data['discount'] ?? [];
            $tax_rate = $data['tax_rate'] ?? [];
            $tax = $data['tax_value'] ?? [];
            $total = $data['subtotal_value'] ?? [];

            if (empty($product_sale_ids) || empty($qty)) {
                return redirect()->back()->with('not_permitted', 'No products to return');
            }

            foreach ($product_sale_ids as $key => $product_sale_id) {
                $pro_id = $data['product_id'][$key] ?? null;
                if (!$pro_id) {
                    continue;
                }
                $lims_product_data = Product::find($pro_id);
                if (!$lims_product_data) {
                    continue;
                }
                $variant_id = null;
                $sale_unit_id = 0;

                $qty_val = $qty[$key] ?? 0;
                $code_val = $product_code[$key] ?? null;
                $batch_val = $product_batch_id[$key] ?? null;

                if (isset($sale_unit[$key]) && $sale_unit[$key] != 'n/a') {
                    $lims_sale_unit_data = Unit::where('unit_name', $sale_unit[$key])->first();
                    if ($lims_sale_unit_data) {
                        $sale_unit_id = $lims_sale_unit_data->id;
                        if ($lims_sale_unit_data->operator == '*') {
                            $quantity = $qty_val * $lims_sale_unit_data->operation_value;
                        } elseif ($lims_sale_unit_data->operator == '/') {
                            $quantity = $qty_val / $lims_sale_unit_data->operation_value;
                        }
                    }
                } else {
                    $quantity = $qty_val;
                }

                if ($lims_product_data->is_variant) {
                    $lims_product_variant_data = ProductVariant::select('id', 'variant_id', 'qty')
                        ->FindExactProductWithCode($pro_id, $code_val)
                        ->first();
                    $lims_product_warehouse_data = Product_Warehouse::FindProductWithVariant($pro_id, $lims_product_variant_data->variant_id, $data['warehouse_id'])->first();
                    if ($lims_product_variant_data) {
                        $lims_product_variant_data->qty += $quantity;
                        $lims_product_variant_data->save();
                    }
                    $variant_data = Variant::find($lims_product_variant_data->variant_id);
                    $variant_id = $variant_data->id;
                } elseif ($batch_val) {
                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_batch_id', $batch_val],
                        ['warehouse_id', $data['warehouse_id']]
                    ])->first();
                    $lims_product_batch_data = ProductBatch::find($batch_val);
                    if ($lims_product_batch_data) {
                        $lims_product_batch_data->qty += $quantity;
                        $lims_product_batch_data->save();
                    }
                } else {
                    $lims_product_warehouse_data = Product_Warehouse::FindProductWithoutVariant($pro_id, $data['warehouse_id'])->first();
                }

                if ($lims_product_warehouse_data) {
                    $lims_product_warehouse_data->qty += $quantity;
                    $lims_product_warehouse_data->save();
                }

                $lims_product_data->qty += $quantity;
                $lims_product_data->save();

                $imei_val = $imei_number[$key] ?? null;
                if ($imei_val && !str_contains($imei_val, "null")) {
                    if ($lims_product_warehouse_data->imei_number) {
                        $lims_product_warehouse_data->imei_number .= ',' . $imei_val;
                    } else {
                        $lims_product_warehouse_data->imei_number = $imei_val;
                    }
                    $lims_product_warehouse_data->save();
                }

                ProductReturn::insert([
                    'return_id' => $lims_return_data->id,
                    'product_id' => $pro_id,
                    'product_batch_id' => $batch_val,
                    'variant_id' => $variant_id,
                    'imei_number' => $imei_val,
                    'qty' => $qty_val,
                    'sale_unit_id' => $sale_unit_id,
                    'net_unit_price' => $net_unit_price[$key] ?? 0,
                    'discount' => $discount[$key] ?? 0,
                    'tax_rate' => $tax_rate[$key] ?? 0,
                    'tax' => $tax[$key] ?? 0,
                    'total' => $total[$key] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $product_sale_data = Product_Sale::where([
                    ['product_id', $pro_id],
                    ['sale_id', $data['sale_id']]
                ])->select('id', 'return_qty')->first();
                if ($product_sale_data) {
                    $product_sale_data->return_qty += $qty_val;
                    $product_sale_data->save();
                }
            }

            if ($data['change_sale_status']) {
                $lims_sale_data->update(['sale_status' => 4]);
            }

            DB::commit();
            return redirect('delivery-return')->with('message', __('db.Return created successfully'));
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Delivery Return creation failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('delivery-sales-sale-return')) {
            return redirect()->back()->with('not_permitted', __('db Sorry! You are not allowed to access this module'));
        }

        $lims_return_data = Returns::with(['biller', 'customer', 'warehouse', 'user', 'sale.deliveryMan'])->find($id);
        $lims_product_return_data = ProductReturn::where('return_id', $id)->get();

        return view('backend.delivery_management.delivery_return.show', compact(
            'lims_return_data',
            'lims_product_return_data'
        ));
    }

    public function getCustomerGroup($id)
    {
        $lims_customer_data = Customer::find($id);
        $lims_customer_group_data = CustomerGroup::find($lims_customer_data->customer_group_id);
        return $lims_customer_group_data->percentage;
    }

    public function getProduct($id)
    {
        $lims_product_warehouse_data = Product::join('product_warehouse', 'products.id', '=', 'product_warehouse.product_id')
            ->where([
                ['products.is_active', true],
                ['product_warehouse.warehouse_id', $id],
            ])
            ->whereNull('product_warehouse.variant_id')
            ->whereNull('product_warehouse.product_batch_id')
            ->select('product_warehouse.*')
            ->get();

        config()->set('database.connections.mysql.strict', false);
        \DB::reconnect();

        $lims_product_with_batch_warehouse_data = Product::join('product_warehouse', 'products.id', '=', 'product_warehouse.product_id')
            ->where([
                ['products.is_active', true],
                ['product_warehouse.warehouse_id', $id],
            ])
            ->whereNull('product_warehouse.variant_id')
            ->whereNotNull('product_warehouse.product_batch_id')
            ->select('product_warehouse.*')
            ->groupBy('product_warehouse.product_id')
            ->get();

        config()->set('database.connections.mysql.strict', true);
        \DB::reconnect();

        $lims_product_with_variant_warehouse_data = Product::join('product_warehouse', 'products.id', '=', 'product_warehouse.product_id')
            ->where([
                ['products.is_active', true],
                ['product_warehouse.warehouse_id', $id],
            ])->whereNotNull('product_warehouse.variant_id')->select('product_warehouse.*')->get();

        $product_code = [];
        $product_name = [];
        $product_qty = [];
        $product_price = [];
        $product_type = [];
        $is_batch = [];
        $product_data = [];

        foreach ($lims_product_warehouse_data as $product_warehouse) {
            $product_qty[] = $product_warehouse->qty;
            $product_price[] = $product_warehouse->price;
            $lims_product_data = Product::select('code', 'name', 'type', 'is_batch')->find($product_warehouse->product_id);
            $product_code[] = $lims_product_data->code;
            $product_name[] = htmlspecialchars($lims_product_data->name);
            $product_type[] = $lims_product_data->type;
            $is_batch[] = null;
        }

        foreach ($lims_product_with_batch_warehouse_data as $product_warehouse) {
            $product_qty[] = $product_warehouse->qty;
            $product_price[] = $product_warehouse->price;
            $lims_product_data = Product::select('code', 'name', 'type', 'is_batch')->find($product_warehouse->product_id);
            $product_code[] = $lims_product_data->code;
            $product_name[] = htmlspecialchars($lims_product_data->name);
            $product_type[] = $lims_product_data->type;
            $product_batch_data = ProductBatch::select('id', 'batch_no')->find($product_warehouse->product_batch_id);
            $is_batch[] = $lims_product_data->is_batch;
        }

        foreach ($lims_product_with_variant_warehouse_data as $product_warehouse) {
            $product_qty[] = $product_warehouse->qty;
            $lims_product_data = Product::select('name', 'type')->find($product_warehouse->product_id);
            $lims_product_variant_data = ProductVariant::select('item_code')->FindExactProduct($product_warehouse->product_id, $product_warehouse->variant_id)->first();
            $product_code[] = $lims_product_variant_data->item_code;
            $product_name[] = htmlspecialchars($lims_product_data->name);
            $product_type[] = $lims_product_data->type;
            $is_batch[] = null;
        }

        $product_data[] = $product_code;
        $product_data[] = $product_name;
        $product_data[] = $product_qty;
        $product_data[] = $product_type;
        $product_data[] = $product_price;
        $product_data[] = $is_batch;

        return $product_data;
    }

    public function limsProductSearch(Request $request)
    {
        $todayDate = date('Y-m-d');
        $product_code = explode("(", $request['data']);
        $product_code[0] = rtrim($product_code[0], " ");

        $lims_product_data = Product::where('code', $product_code[0])->first();
        $product_variant_id = null;

        if (!$lims_product_data) {
            $lims_product_data = Product::join('product_variants', 'products.id', 'product_variants.product_id')
                ->select('products.*', 'product_variants.id as product_variant_id', 'product_variants.item_code', 'product_variants.additional_price')
                ->where('product_variants.item_code', $product_code[0])
                ->first();
            $lims_product_data->code = $lims_product_data->item_code;
            $lims_product_data->price += $lims_product_data->additional_price;
            $product_variant_id = $lims_product_data->product_variant_id;
        }

        $product[] = $lims_product_data->name;
        $product[] = $lims_product_data->code;

        if ($lims_product_data->promotion && $todayDate <= $lims_product_data->last_date) {
            $product[] = $lims_product_data->promotion_price;
        } else {
            $product[] = $lims_product_data->price;
        }

        if ($lims_product_data->tax_id) {
            $lims_tax_data = Tax::find($lims_product_data->tax_id);
            $product[] = $lims_tax_data->rate;
            $product[] = $lims_tax_data->name;
        } else {
            $product[] = 0;
            $product[] = 'No Tax';
        }

        $product[] = $lims_product_data->tax_method;

        if ($lims_product_data->type == 'standard') {
            $units = Unit::where("base_unit", $lims_product_data->unit_id)
                ->orWhere('id', $lims_product_data->unit_id)
                ->get();
            $unit_name = [];
            $unit_operator = [];
            $unit_operation_value = [];

            foreach ($units as $unit) {
                if ($lims_product_data->sale_unit_id == $unit->id) {
                    array_unshift($unit_name, $unit->unit_name);
                    array_unshift($unit_operator, $unit->operator);
                    array_unshift($unit_operation_value, $unit->operation_value);
                } else {
                    $unit_name[] = $unit->unit_name;
                    $unit_operator[] = $unit->operator;
                    $unit_operation_value[] = $unit->operation_value;
                }
            }

            $product[] = implode(",", $unit_name) . ',';
            $product[] = implode(",", $unit_operator) . ',';
            $product[] = implode(",", $unit_operation_value) . ',';
        } else {
            $product[] = 'n/a' . ',';
            $product[] = 'n/a' . ',';
            $product[] = 'n/a' . ',';
        }

        $product[] = $lims_product_data->id;
        $product[] = $product_variant_id;
        $product[] = $lims_product_data->promotion;
        $product[] = $lims_product_data->is_imei;

        return $product;
    }

    public function productReturnData($id)
    {
        $lims_product_return_data = ProductReturn::where('return_id', $id)->get();
        foreach ($lims_product_return_data as $key => $product_return_data) {
            $product = Product::find($product_return_data->product_id);
            if ($product_return_data->sale_unit_id != 0) {
                $unit_data = Unit::find($product_return_data->sale_unit_id);
                $unit = $unit_data->unit_code;
            } else {
                $unit = '';
            }

            if ($product_return_data->variant_id) {
                $lims_product_variant_data = ProductVariant::select('item_code')->FindExactProduct($product_return_data->product_id, $product_return_data->variant_id)->first();
                $product->code = $lims_product_variant_data->item_code;
            }

            if ($product_return_data->product_batch_id) {
                $product_batch_data = ProductBatch::select('batch_no')->find($product_return_data->product_batch_id);
                $product_return[7][$key] = $product_batch_data->batch_no;
            } else {
                $product_return[7][$key] = 'N/A';
            }

            $product_return[0][$key] = $product->name . ' [' . $product->code . ']';
            if ($product_return_data->imei_number) {
                $product_return[0][$key] .= '<br>IMEI or Serial Number: ' . $product_return_data->imei_number;
            }
            $product_return[1][$key] = $product_return_data->qty;
            $product_return[2][$key] = $unit;
            $product_return[3][$key] = $product_return_data->tax;
            $product_return[4][$key] = $product_return_data->tax_rate;
            $product_return[5][$key] = $product_return_data->discount;
            $product_return[6][$key] = $product_return_data->total;
        }

        return $product_return;
    }

    public function sendMail(Request $request)
    {
        $data = $request->all();
        $lims_return_data = Returns::find($data['return_id']);
        $lims_product_return_data = ProductReturn::where('return_id', $data['return_id'])->get();
        $lims_customer_data = Customer::find($lims_return_data->customer_id);
        $mail_setting = MailSetting::latest()->first();

        if (!$mail_setting) {
            $message = 'Please setup your mail setting to send mail.';
        } elseif (!$lims_customer_data->email) {
            $message = 'Customer does not have email!';
        } else {
            $mail_data['email'] = $lims_customer_data->email;
            $mail_data['reference_no'] = $lims_return_data->reference_no;
            $mail_data['total_qty'] = $lims_return_data->total_qty;
            $mail_data['total_price'] = $lims_return_data->total_price;
            $mail_data['order_tax'] = $lims_return_data->order_tax;
            $mail_data['order_tax_rate'] = $lims_return_data->order_tax_rate;
            $mail_data['grand_total'] = $lims_return_data->grand_total;

            foreach ($lims_product_return_data as $key => $product_return_data) {
                $lims_product_data = Product::find($product_return_data->product_id);
                if ($product_return_data->variant_id) {
                    $variant_data = Variant::find($product_return_data->variant_id);
                    $mail_data['products'][$key] = $lims_product_data->name . ' [' . $variant_data->name . ']';
                } else {
                    $mail_data['products'][$key] = $lims_product_data->name;
                }

                if ($product_return_data->sale_unit_id) {
                    $lims_unit_data = Unit::find($product_return_data->sale_unit_id);
                    $mail_data['unit'][$key] = $lims_unit_data->unit_code;
                } else {
                    $mail_data['unit'][$key] = '';
                }

                $mail_data['qty'][$key] = $product_return_data->qty;
                $mail_data['total'][$key] = $product_return_data->qty;
            }

            $this->setMailInfo($mail_setting);
            try {
                Mail::to($mail_data['email'])->send(new \App\Mail\ReturnDetails($mail_data));
                $message = 'Mail sent successfully';
            } catch (\Exception $e) {
                $message = 'Please setup your mail setting to send mail.';
            }
        }

        return redirect()->back()->with('message', $message);
    }
}
