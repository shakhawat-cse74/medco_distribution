<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use App\Models\SaleExchange;
use App\Models\ProductExchange;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Models\Biller;
use App\Models\CustomerGroup;
use App\Models\CustomField;
use App\Models\GeneralSetting;
use App\Models\Product;
use App\Models\Product_Sale;
use App\Models\Product_Warehouse;
use App\Models\ProductBatch;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\Tax;
use App\Models\Unit;
use App\Services\AccountingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class ExchangeController extends Controller
{
    public function index(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);

        if ($role->hasPermissionTo('returns-index')) {
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission) {
                $all_permission[] = $permission->name;
            }
            if (empty($all_permission)) {
                $all_permission[] = 'dummy text';
            }

            $warehouse_id = $request->input('warehouse_id') ?: 0;

            if ($request->input('starting_date')) {
                $starting_date = $request->input('starting_date');
                $ending_date = $request->input('ending_date');
            } else {
                $starting_date = date("Y-m-d", strtotime(date('Y-m-d', strtotime('-1 year', strtotime(date('Y-m-d'))))));
                $ending_date = date("Y-m-d");
            }

            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $general_setting = cache()->get('general_setting');

            return view('backend.sale-exchange.index', compact(
                'starting_date',
                'ending_date',
                'warehouse_id',
                'all_permission',
                'lims_warehouse_list',
                'general_setting'
            ));
        }

        return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
    }

    public function exchangeData(Request $request)
    {
        $columns = [
            1 => 'created_at',
            2 => 'reference_no',
        ];

        $warehouse_id = $request->input('warehouse_id');
        $query = SaleExchange::query();

        if (Auth::user()->role_id > 2 && config('staff_access') == 'own') {
            $query->where('user_id', Auth::id());
        } elseif (Auth::user()->role_id > 2 && config('staff_access') == 'warehouse') {
            $query->where('warehouse_id', Auth::user()->warehouse_id);
        } elseif ($warehouse_id != 0) {
            $query->where('warehouse_id', $warehouse_id);
        }

        $query->whereDate('created_at', '>=', $request->input('starting_date'))
            ->whereDate('created_at', '<=', $request->input('ending_date'));

        $totalData = $query->count();
        $totalFiltered = $totalData;

        $limit = $request->input('length') != -1 ? $request->input('length') : $totalData;
        $start = $request->input('start');
        $order = 'sale_exchanges.' . $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if (!empty($request->input('search.value'))) {
            $search = $request->input('search.value');

            $query->join('customers', 'sale_exchanges.customer_id', '=', 'customers.id')
                ->join('billers', 'sale_exchanges.biller_id', '=', 'billers.id')
                ->select('sale_exchanges.*')
                ->where(function ($q) use ($search) {
                    $q->where('sale_exchanges.reference_no', 'LIKE', "%{$search}%")
                        ->orWhere('customers.name', 'LIKE', "%{$search}%")
                        ->orWhere('customers.phone_number', 'LIKE', "%{$search}%")
                        ->orWhere('billers.name', 'LIKE', "%{$search}%")
                        ->orWhereDate('sale_exchanges.created_at', '=', date('Y-m-d', strtotime(str_replace('/', '-', $search))));
                });

            $totalFiltered = $query->count();
        }

        $exchanges = $query->with(['biller', 'customer', 'warehouse', 'user', 'sale'])
            ->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir)
            ->get();

        $data = [];
        if ($exchanges->isNotEmpty()) {
            foreach ($exchanges as $key => $exchange) {
                $saleReference = 'N/A';
                if ($exchange->sale_id && $exchange->sale) {
                    $saleReference = $exchange->sale->reference_no;
                }

                $nestedData = [
                    'key' => $key,
                    'date' => date(config('date_format'), strtotime($exchange->created_at->toDateString())),
                    'reference_no' => $exchange->reference_no,
                    'sale_reference' => $saleReference,
                    'warehouse' => $exchange->warehouse->name,
                    'biller' => $exchange->biller->name,
                    'customer' => $exchange->customer->name,
                    'payment_type' => $exchange->payment_type == 'pay'
                        ? '<span class="badge badge-danger">Pay</span>'
                        : '<span class="badge badge-success">Receive</span>',
                    'amount' => number_format($exchange->amount, config('decimal')),
                    'options' => $this->buildActionButtons($exchange, $request['all_permission']),
                    'exchange' => json_encode([
                        date(config('date_format'), strtotime($exchange->created_at->toDateString())),
                        $exchange->reference_no,
                        $exchange->warehouse->name,
                        $exchange->biller->name,
                        $exchange->biller->company_name ?? '',
                        $exchange->biller->email,
                        $exchange->biller->phone_number,
                        $exchange->biller->address,
                        $exchange->biller->city,
                        $exchange->customer->name,
                        $exchange->customer->phone_number,
                        $exchange->customer->address,
                        $exchange->customer->city,
                        $exchange->id,
                        $exchange->total_tax,
                        $exchange->total_discount,
                        $exchange->amount,
                        $exchange->order_tax,
                        $exchange->order_tax_rate,
                        $exchange->grand_total,
                        nl2br($exchange->exchange_note ?? ''),
                        nl2br($exchange->staff_note ?? ''),
                        $exchange->user->name,
                        $exchange->user->email,
                        $saleReference,
                        $exchange->document,
                        config('currency', 'BDT'),
                        $exchange->exchange_rate ?? '',
                        $exchange->payment_type ?? '',
                    ])
                ];

                $data[] = $nestedData;
            }
        }

        return response()->json([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data" => $data
        ]);
    }

    private function buildActionButtons($exchange, $permissions)
    {
        $html = '<div class="btn-group">
            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' . __("db.action") . '
              <span class="caret"></span>
              <span class="sr-only">Toggle Dropdown</span>
            </button>
            <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                <li>
                    <button type="button" class="btn btn-link view"><i class="ti ti-eye"></i> ' . __('db.View') . '</button>
                </li>';

        if (in_array("exchanges-edit", $permissions)) {
            $html .= '<li>
                <a href="' . route('exchange.edit', $exchange->id) . '" class="btn btn-link"><i class="ti ti-edit"></i> ' . __('db.edit') . '</a>
            </li>';
        }

        if (in_array("exchanges-delete", $permissions)) {
            $html .= '<form action="' . route("exchange.destroy", $exchange->id) . '" method="POST" class="delete-form">' . csrf_field() . method_field("DELETE") . '
                <li>
                  <button type="submit" class="btn btn-link" onclick="return confirmDelete()"><i class="ti ti-trash"></i> ' . __("db.delete") . '</button>
                </li></form>';
        }

        $html .= '</ul></div>';
        return $html;
    }

    public function productExchange($id)
    {
        try {
            $exchange = SaleExchange::with(['products.product', 'products.saleUnit'])->findOrFail($id);

            $productsData = ['new' => [], 'returned' => []];

            foreach ($exchange->products as $item) {
                $productInfo = [
                    'name' => $item->product->name,
                    'code' => $item->product->code,
                    'name_code' => $item->product->name . ' [' . $item->product->code . ']',
                    'batch_no' => $item->product->batch_no ?? 'N/A',
                    'qty' => $item->qty,
                    'unit_code' => $item->saleUnit->unit_code ?? '',
                    'unit_price' => number_format($item->net_unit_price, config('decimal')),
                    'tax' => number_format($item->tax, config('decimal')),
                    'tax_rate' => $item->tax_rate,
                    'discount' => number_format($item->discount, config('decimal')),
                    'subtotal' => number_format($item->total, config('decimal')),
                    'type' => $item->type,
                ];

                if ($item->type === 'new') {
                    $productsData['new'][] = $productInfo;
                } else {
                    $productsData['returned'][] = $productInfo;
                }
            }

            $newTotal = $exchange->products->where('type', 'new')->sum('total');
            $returnedTotal = $exchange->products->where('type', 'returned')->sum('total');

            $productsData['totals'] = [
                'new' => number_format($newTotal, config('decimal')),
                'returned' => number_format($returnedTotal, config('decimal')),
                'tax' => number_format($exchange->total_tax, config('decimal')),
                'discount' => number_format($exchange->total_discount, config('decimal')),
                'amount' => number_format($exchange->amount, config('decimal')),
                'order_tax' => number_format($exchange->order_tax, config('decimal')),
                'order_tax_rate' => $exchange->order_tax_rate,
                'grand_total' => number_format($exchange->grand_total, config('decimal')),
            ];

            return response()->json($productsData);
        } catch (\Exception $e) {
            Log::error('Exchange product fetch error: ' . $e->getMessage());
            return response()->json(['error' => 'Exchange not found'], 404);
        }
    }

    public function create(Request $request)
    {
        // Get logged-in user's role
        $role = Role::find(Auth::user()->role_id);

        // Check permission
        if (!$role->hasPermissionTo('exchange-add')) {
            return redirect()->back()
                ->with('not_permitted', __('Sorry! You are not allowed to access this module'));
        }

        // Load required data for the exchange page
        $lims_customer_list   = Customer::where('is_active', true)->get();
        $lims_account_list    = Account::latest()->get();
        $lims_warehouse_list  = Warehouse::where('is_active', true)->get();
        $lims_biller_list     = Biller::where('is_active', true)->get();
        $lims_tax_list        = Tax::where('is_active', true)->get();
        $numberOfInvoice      = Sale::whereNull('deleted_at')->count();

        // Default values
        $lims_sale_data = null;
        $lims_product_sale_data = collect([]);

        /**
         * Handle optional inputs:
         * 1. reference_no (from modal input)
         * 2. sale_id (direct navigation)
         */
        if ($request->filled('reference_no')) {

            // Find sale by reference number
            $lims_sale_data = Sale::where('reference_no', $request->reference_no)
                ->whereNull('deleted_at')
                ->first();
        } elseif ($request->filled('sale_id')) {

            // Find sale by ID
            $lims_sale_data = Sale::whereNull('deleted_at')
                ->find($request->sale_id);
        }

        // If sale found, load its products
        if ($lims_sale_data) {
            $lims_product_sale_data = Product_Sale::where('sale_id', $lims_sale_data->id)->get();
        }

        // Currency exchange rate (default = 1 if no sale selected)
        $currency_exchange_rate = $lims_sale_data->exchange_rate ?? 1;

        // Custom fields for sale
        $custom_fields = CustomField::where('belongs_to', 'sale')->get();

        // Return exchange create view
        return view('backend.sale-exchange.create', compact(
            'lims_account_list',
            'lims_customer_list',
            'lims_warehouse_list',
            'lims_biller_list',
            'lims_tax_list',
            'lims_sale_data',
            'lims_product_sale_data',
            'currency_exchange_rate',
            'custom_fields',
            'numberOfInvoice'
        ));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->except('document', 'total_sale_discount', 'type');
            $data['reference_no'] = 'exc-' . date("Ymd") . '-' . date("his") . '-' . random_int(1000, 9999);
            $data['total_discount'] = $request->total_sale_discount ?? 0;
            $data['user_id'] = Auth::id();

            $lims_sale_data = null;

            if (!empty($data['sale_id']) && is_numeric($data['sale_id'])) {
                $lims_sale_data = Sale::whereNull('deleted_at')
                    ->select('id', 'warehouse_id', 'customer_id', 'biller_id', 'grand_total', 'paid_amount', 'payment_status')
                    ->find($data['sale_id']);
            }

            $data['sale_id'] = $lims_sale_data ? $lims_sale_data->id : 0;

            $validator = Validator::make($data, [
                'customer_id' => 'required|exists:customers,id',
                'warehouse_id' => 'required|exists:warehouses,id',
                'biller_id' => 'required|exists:billers,id',
                'product_id' => 'required|array|min:1',
            ]);

            if ($validator->fails()) {
                DB::rollBack();
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $document = $request->document;
            if ($document) {
                $v = Validator::make(
                    ['extension' => strtolower($document->getClientOriginalExtension())],
                    ['extension' => 'in:jpg,jpeg,png,gif,pdf,csv,docx,xlsx,txt']
                );
                if ($v->fails()) {
                    DB::rollBack();
                    return redirect()->back()->withErrors($v->errors());
                }
                $ext = pathinfo($document->getClientOriginalName(), PATHINFO_EXTENSION);
                $documentName = date("Ymdhis");

                if (!config('database.connections.saleprosaas_landlord')) {
                    $documentName = $documentName . '.' . $ext;
                } else {
                    $documentName = $this->getTenantId() . '_' . $documentName . '.' . $ext;
                }

                $document->move(public_path('documents/exchange'), $documentName);
                $data['document'] = $documentName;
            }

            $lims_exchange_data = SaleExchange::create($data);

            $type_array = $request->type ?? [];
            $product_id = $data['product_id'] ?? [];
            $product_batch_id = $data['product_batch_id'] ?? [];
            $imei_number = $data['imei_number'] ?? [];
            $product_code = $data['product_code'] ?? [];
            $qty = $data['qty'] ?? [];
            $sale_unit = $data['sale_unit'] ?? [];
            $net_unit_price = $data['net_unit_price'] ?? [];
            $discount = $data['discount'] ?? [];
            $tax_rate = $data['tax_rate'] ?? [];
            $tax = $data['tax'] ?? [];
            $total = $data['subtotal'] ?? [];
            $product_sale_id = $data['product_sale_id'] ?? [];
            $is_exchange = $request->is_exchange ?? [];

            $new_products_count = 0;
            $returned_products_count = 0;

            foreach ($product_id as $index => $id) {
                $product_type = $type_array[$index] ?? 'new';

                if ($product_type === 'return') {
                    $product_code_value = $product_code[$index] ?? null;
                    $should_return = $product_code_value && in_array($product_code_value, $is_exchange);

                    if ($should_return) {
                        $original_sale_id = $product_sale_id[$index] ?? null;
                        $original_product_sale = $original_sale_id ? Product_Sale::find($original_sale_id) : null;

                        $this->processReturnProduct(
                            $id,
                            $index,
                            $lims_exchange_data->id,
                            $data['warehouse_id'],
                            $qty,
                            $sale_unit,
                            $net_unit_price,
                            $discount,
                            $tax_rate,
                            $tax,
                            $total,
                            $product_code,
                            $product_batch_id,
                            $imei_number,
                            $original_product_sale
                        );
                        $returned_products_count++;
                    }
                } elseif ($product_type === 'new') {
                    $this->processNewProduct(
                        $id,
                        $index,
                        $lims_exchange_data->id,
                        $data['warehouse_id'],
                        $qty,
                        $sale_unit,
                        $net_unit_price,
                        $discount,
                        $tax_rate,
                        $tax,
                        $total,
                        $product_code,
                        $product_batch_id,
                        $imei_number
                    );
                    $new_products_count++;
                }
            }

            $returnedTotal = $lims_exchange_data->products()->where('type', 'returned')->sum('total');
            $newTotal = $lims_exchange_data->products()->where('type', 'new')->sum('total');

            if ($lims_sale_data) {
                $previousGrandTotal = (float) $lims_sale_data->grand_total;
                $previousPaidAmount = (float) $lims_sale_data->paid_amount;
                $adjustedGrandTotal = max(0, $previousGrandTotal - $returnedTotal + $newTotal);
                $paidAmount = $previousPaidAmount;

                if ($lims_exchange_data->payment_type === 'receive' && $lims_exchange_data->amount > 0) {
                    $paidAmount += (float) $lims_exchange_data->amount;
                } elseif ($lims_exchange_data->payment_type === 'pay' && $lims_exchange_data->amount > 0) {
                    $overpaidAfterExchange = max(0, $previousPaidAmount - $adjustedGrandTotal);
                    $refundAmount = min((float) $lims_exchange_data->amount, $overpaidAfterExchange);
                    $paidAmount -= $refundAmount;

                    if (bccomp((string) $refundAmount, (string) $lims_exchange_data->amount, 4) !== 0) {
                        $lims_exchange_data->amount = $refundAmount;
                        if ($refundAmount <= 0) {
                            $lims_exchange_data->payment_type = null;
                        }
                        $lims_exchange_data->save();
                    }
                }

                $lims_sale_data->grand_total = $adjustedGrandTotal;
                $lims_sale_data->paid_amount = max(0, $paidAmount);
                $balance = $lims_sale_data->grand_total - $lims_sale_data->paid_amount;
                $lims_sale_data->payment_status = abs($balance) < 0.01 ? 4 : 2;
                $lims_sale_data->save();
            }

            $accountingResult = app(AccountingService::class)->recordSaleExchange($lims_exchange_data);
            if (!$accountingResult->success) {
                throw new \App\Exceptions\AccountingException($accountingResult->error);
            }

            DB::commit();
            $message = "Exchange created successfully with {$new_products_count} new product(s) and {$returned_products_count} returned product(s)";
            return redirect('exchange')->with('message', $message);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Exchange Store Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);
            return redirect()->back()->with('not_permitted', 'Something went wrong: ' . $e->getMessage());
        }
    }

    private function sanitizeDecimal($value, $decimals = null): float
    {
        $decimals = $decimals ?? config('decimal', 2);

        if ($value === null || $value === '' || $value === 'NaN' || $value === 'null' || $value === 'undefined') {
            return 0.00;
        }

        $numeric = floatval($value);

        if (is_nan($numeric) || !is_finite($numeric)) {
            return 0.00;
        }

        return round($numeric, $decimals);
    }

    private function processNewProduct(
        $product_id,
        $index,
        $exchange_id,
        $warehouse_id,
        $qty,
        $sale_unit,
        $net_unit_price,
        $discount,
        $tax_rate,
        $tax,
        $total,
        $product_code,
        $product_batch_id,
        $imei_number
    ) {
        $lims_product_data = Product::find($product_id);

        if (!$lims_product_data) {
            throw new \Exception("Product not found: {$product_id}");
        }

        $sale_unit_id = 0;
        $quantity = floatval($qty[$index] ?? 0);

        if (!empty($sale_unit[$index]) && $sale_unit[$index] != 'n/a') {
            $lims_sale_unit_data = Unit::where('unit_name', $sale_unit[$index])->first();
            if ($lims_sale_unit_data) {
                $sale_unit_id = $lims_sale_unit_data->id;
                if ($lims_sale_unit_data->operator == '*') {
                    $quantity = floatval($qty[$index]) * floatval($lims_sale_unit_data->operation_value);
                } elseif ($lims_sale_unit_data->operator == '/') {
                    $quantity = floatval($qty[$index]) / floatval($lims_sale_unit_data->operation_value);
                }
            }
        }

        $lims_product_warehouse_data = null;

        if ($lims_product_data->type == 'combo') {
            $this->moveComboComponents($lims_product_data, $warehouse_id, $quantity, -1);
        } elseif ($lims_product_data->is_variant) {
            $lims_product_data->qty -= $quantity;
            $lims_product_data->save();

            $lims_product_variant_data = ProductVariant::select('id', 'variant_id', 'qty')
                ->FindExactProductWithCode($product_id, $product_code[$index] ?? '')
                ->first();

            if ($lims_product_variant_data) {
                $lims_product_variant_data->qty -= $quantity;
                $lims_product_variant_data->save();
                $lims_product_warehouse_data = Product_Warehouse::FindProductWithVariant(
                    $product_id,
                    $lims_product_variant_data->variant_id,
                    $warehouse_id
                )->first();
            }
        } elseif (!empty($product_batch_id[$index])) {
            $lims_product_data->qty -= $quantity;
            $lims_product_data->save();

            $lims_product_batch_data = ProductBatch::find($product_batch_id[$index]);
            if ($lims_product_batch_data) {
                $lims_product_batch_data->qty -= $quantity;
                $lims_product_batch_data->save();
            }
            $lims_product_warehouse_data = Product_Warehouse::where([
                ['product_batch_id', $product_batch_id[$index]],
                ['warehouse_id', $warehouse_id]
            ])->first();
        } else {
            $lims_product_data->qty -= $quantity;
            $lims_product_data->save();

            $lims_product_warehouse_data = Product_Warehouse::FindProductWithoutVariant(
                $product_id,
                $warehouse_id
            )->first();
        }

        if ($lims_product_warehouse_data) {
            if (gen_setting()->without_stock == 'no' && $lims_product_data->type != 'service' && $lims_product_warehouse_data->qty < $quantity) {
                throw new \Exception(__('db.Quantity exceeds stock quantity!'));
            }

            $lims_product_warehouse_data->qty -= $quantity;

            if (!empty($imei_number[$index]) && !str_contains($imei_number[$index], "null")) {
                $imei_numbers = explode(",", $imei_number[$index]);
                $all_imei_numbers = explode(",", $lims_product_warehouse_data->imei_number ?? '');
                foreach ($imei_numbers as $number) {
                    if (($j = array_search($number, $all_imei_numbers)) !== false) {
                        unset($all_imei_numbers[$j]);
                    }
                }
                $lims_product_warehouse_data->imei_number = implode(",", array_filter($all_imei_numbers));
            }
            $lims_product_warehouse_data->save();
        } elseif (gen_setting()->without_stock == 'no' && $lims_product_data->type != 'service' && $lims_product_data->type != 'combo') {
            throw new \Exception(__('db.Quantity exceeds stock quantity!'));
        }

        $netUnitPrice = $this->sanitizeDecimal($net_unit_price[$index] ?? 0);
        $discountVal = $this->sanitizeDecimal($discount[$index] ?? 0);
        $taxRateVal = $this->sanitizeDecimal($tax_rate[$index] ?? 0);
        $taxVal = $this->sanitizeDecimal($tax[$index] ?? 0);
        $totalVal = $this->sanitizeDecimal($total[$index] ?? 0);

        ProductExchange::create([
            'exchange_id' => $exchange_id,
            'product_id' => $product_id,
            'qty' => $quantity,
            'sale_unit_id' => $sale_unit_id,
            'net_unit_price' => $netUnitPrice,
            'discount' => $discountVal,
            'tax_rate' => $taxRateVal,
            'tax' => $taxVal,
            'total' => $totalVal,
            'type' => 'new',
        ]);
    }

    private function processReturnProduct(
        $product_id,
        $index,
        $exchange_id,
        $warehouse_id,
        $qty,
        $sale_unit,
        $net_unit_price,
        $discount,
        $tax_rate,
        $tax,
        $total,
        $product_code,
        $product_batch_id,
        $imei_number,
        $original_product_sale = null
    ) {
        $lims_product_data = Product::find($product_id);

        if (!$lims_product_data) {
            throw new \Exception("Product not found: {$product_id}");
        }

        $sale_unit_id = 0;
        $quantity = floatval($qty[$index] ?? 0);

        if (!empty($sale_unit[$index]) && $sale_unit[$index] != 'n/a') {
            $lims_sale_unit_data = Unit::where('unit_name', $sale_unit[$index])->first();
            if ($lims_sale_unit_data) {
                $sale_unit_id = $lims_sale_unit_data->id;
                if ($lims_sale_unit_data->operator == '*') {
                    $quantity = floatval($qty[$index]) * floatval($lims_sale_unit_data->operation_value);
                } elseif ($lims_sale_unit_data->operator == '/') {
                    $quantity = floatval($qty[$index]) / floatval($lims_sale_unit_data->operation_value);
                }
            }
        }

        $lims_product_warehouse_data = null;
        $variant_id = $original_product_sale->variant_id ?? null;
        $batch_id = $product_batch_id[$index] ?? ($original_product_sale->product_batch_id ?? null);

        if ($lims_product_data->type == 'combo') {
            $this->moveComboComponents($lims_product_data, $warehouse_id, $quantity, 1);
        } elseif ($lims_product_data->is_variant && $variant_id) {
            $lims_product_data->qty += $quantity;
            $lims_product_data->save();

            $lims_product_variant_data = ProductVariant::where([
                ['product_id', $product_id],
                ['variant_id', $variant_id],
            ])->first();
            if ($lims_product_variant_data) {
                $lims_product_variant_data->qty += $quantity;
                $lims_product_variant_data->save();
            }
            $lims_product_warehouse_data = Product_Warehouse::FindProductWithVariant(
                $product_id,
                $variant_id,
                $warehouse_id
            )->first();
        } elseif ($batch_id) {
            $lims_product_data->qty += $quantity;
            $lims_product_data->save();

            $lims_product_batch_data = ProductBatch::find($batch_id);
            if ($lims_product_batch_data) {
                $lims_product_batch_data->qty += $quantity;
                $lims_product_batch_data->save();
            }
            $lims_product_warehouse_data = Product_Warehouse::where([
                ['product_batch_id', $batch_id],
                ['warehouse_id', $warehouse_id]
            ])->first();
        } else {
            $lims_product_data->qty += $quantity;
            $lims_product_data->save();

            $lims_product_warehouse_data = Product_Warehouse::FindProductWithoutVariant(
                $product_id,
                $warehouse_id
            )->first();
        }

        if ($lims_product_warehouse_data) {
            $lims_product_warehouse_data->qty += $quantity;

            if (!empty($imei_number[$index]) && !str_contains($imei_number[$index], "null")) {
                if ($lims_product_warehouse_data->imei_number) {
                    $lims_product_warehouse_data->imei_number .= ',' . $imei_number[$index];
                } else {
                    $lims_product_warehouse_data->imei_number = $imei_number[$index];
                }
            }
            $lims_product_warehouse_data->save();
        }

        $netUnitPrice = $this->sanitizeDecimal($net_unit_price[$index] ?? 0);
        $discountVal = $this->sanitizeDecimal($discount[$index] ?? 0);
        $taxRateVal = $this->sanitizeDecimal($tax_rate[$index] ?? 0);
        $taxVal = $this->sanitizeDecimal($tax[$index] ?? 0);
        $totalVal = $this->sanitizeDecimal($total[$index] ?? 0);

        ProductExchange::create([
            'exchange_id' => $exchange_id,
            'product_id' => $product_id,
            'qty' => $quantity,
            'sale_unit_id' => $sale_unit_id,
            'net_unit_price' => $netUnitPrice,
            'discount' => $discountVal,
            'tax_rate' => $taxRateVal,
            'tax' => $taxVal,
            'total' => $totalVal,
            'type' => 'returned',
        ]);
    }

    private function moveComboComponents(Product $comboProduct, int $warehouseId, float $comboQty, int $direction): void
    {
        $productList = explode(',', $comboProduct->product_list ?? '');
        $variantList = $comboProduct->variant_list ? explode(',', $comboProduct->variant_list) : [];
        $qtyList = explode(',', $comboProduct->qty_list ?? '');
        $comboUnitIds = $comboProduct->combo_unit_id ? explode(',', $comboProduct->combo_unit_id) : [];

        foreach ($productList as $index => $childId) {
            if (!$childId) {
                continue;
            }

            $child = Product::find($childId);
            if (!$child) {
                continue;
            }

            $required = (float) ($qtyList[$index] ?? 1);
            if (isset($comboUnitIds[$index]) && $comboUnitIds[$index] && $comboUnitIds[$index] != $child->unit_id) {
                $unit = Unit::find($comboUnitIds[$index]);
                if ($unit) {
                    if ($unit->operator == '*') {
                        $required *= $unit->operation_value;
                    } elseif ($unit->operator == '/') {
                        $required /= $unit->operation_value;
                    }
                }
            }

            $componentQty = $comboQty * $required * $direction;
            $variantId = $variantList[$index] ?? null;

            if ($variantId) {
                $childVariant = ProductVariant::where([
                    ['product_id', $childId],
                    ['variant_id', $variantId],
                ])->first();

                if ($childVariant) {
                    $childVariant->qty += $componentQty;
                    $childVariant->save();
                }

                $warehouseStock = Product_Warehouse::where([
                    ['product_id', $childId],
                    ['variant_id', $variantId],
                    ['warehouse_id', $warehouseId],
                ])->first();
            } else {
                $warehouseStock = Product_Warehouse::where([
                    ['product_id', $childId],
                    ['warehouse_id', $warehouseId],
                ])->first();
            }

            $child->qty += $componentQty;
            $child->save();

            if ($warehouseStock) {
                $warehouseStock->qty += $componentQty;
                $warehouseStock->save();
            }
        }
    }

    public function searchByReference(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);

        if (!$role->hasPermissionTo('exchange-add')) {
            return response()->json([
                'status' => false,
                'message' => __('db.Sorry! You are not allowed')
            ]);
        }

        if ($request->ajax()) {
            $lims_sale_data = Sale::where('reference_no', $request->reference)->first();

            if (!$lims_sale_data) {
                return response()->json([
                    'status' => false,
                    'message' => 'Reference number not found'
                ]);
            }

            $lims_product_sale_data = Product_Sale::where('sale_id', $lims_sale_data->id)->get();
            $general_setting = cache()->get('general_setting');

            $html = view(
                'backend.sale-exchange.partials.sale-products',
                compact('lims_product_sale_data', 'general_setting')
            )->render();

            return response()->json([
                'status' => true,
                'html' => $html
            ]);
        }
    }

    public function getCustomerGroup($id)
    {
        $lims_customer_data = Customer::find($id);
        $lims_customer_group_data = CustomerGroup::find($lims_customer_data->customer_group_id);
        return $lims_customer_group_data->percentage;
    }
}
