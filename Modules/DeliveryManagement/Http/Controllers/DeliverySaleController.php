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
use NumberToWords\NumberToWords;
use App\Models\Payment;
use App\Models\Product_Sale;
use App\Models\Unit;
use App\Services\InvoiceService;
use App\Services\CustomerCreditService;
use App\Http\Controllers\InstallmentPlanController;
use App\Models\CashRegister;
use App\Models\Coupon;
use App\Models\GiftCard;
use App\Models\PaymentWithCheque;
use App\Models\PaymentWithGiftCard;
use App\Models\PaymentWithCreditCard;
use App\Models\RewardPoint;
use App\Models\MailSetting;
use App\Mail\SaleDetails;
use Mail;
use App\Models\SmsTemplate;
use App\Models\ExternalService;
use App\Models\Variant;
use App\ViewModels\ISmsModel;
use App\Models\Currency;
use App\Models\InvoiceSetting;
use App\Models\InstallmentPlan;
use App\Models\Installment;
use App\Models\Printer;
use App\Services\PrinterService;
use App\Services\AccountingService;
use App\Exceptions\AccountingException;
use App\Models\Product_Warehouse;
use Illuminate\Support\Facades\Validator;
use App\Enums\CustomerTypeEnum;

class DeliverySaleController extends Controller
{
    private InvoiceService $invoiceService;
    private ISmsModel $_smsModel;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
        $this->_smsModel = app(ISmsModel::class);
    }

    private function restaurantModifiersAvailable(): bool
    {
        $modules = explode(',', (string) optional(gen_setting())->modules);

        return in_array('restaurant', $modules, true)
            && class_exists(\Modules\Restaurant\Entities\ProductSaleModifier::class)
            && \Illuminate\Support\Facades\Schema::hasTable('product_sale_modifiers');
    }

    protected function getTenantId(): string
    {
        if (config('database.connections.saleprosaas_landlord')) {
            return session()->get('tenant_id', '');
        }
        return '';
    }

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

        $lims_delivery_man_list = DeliveryMan::active()->with('routes')->get()->map(function ($dm) {
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
            return redirect()->back()->with('not_permitted', __('db Sorry! You are not allowed to access this module'));
        }

        $this->validate($request, [
            'warehouse_id'    => 'required|exists:warehouses,id',
            'customer_id'     => 'required|exists:customers,id',
            'payment_status'  => 'required|in:1,2,3,4',
            'sale_status'     => 'required|in:1,2,3',
        ]);

        $data = $request->all();
        $lims_pos_setting_data = PosSetting::latest()->first();

        if (isset($request->reference_no)) {
            $this->validate($request, [
                'reference_no' => [
                    'max:191',
                    'required',
                    'unique:sales'
                ],
            ]);
        }

        $data['user_id'] = Auth::id();
        $cash_register_data = CashRegister::where([
            ['user_id', $data['user_id']],
            ['warehouse_id', $data['warehouse_id']],
            ['status', true]
        ])->first();

        if ($cash_register_data)
            $data['cash_register_id'] = $cash_register_data->id;

        if (isset($data['created_at'])) {
            $data['created_at'] = normalize_to_sql_datetime($data['created_at']);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        $new_data['paid_amount'] = $data['paid_amount'] ?? 0;

        if (is_array($data['paid_amount'])) {
            $data['paid_amount'] = array_sum($data['paid_amount']);
        }

        if (isset($data['pos'])) {
            if (!isset($data['reference_no']))
                $data['reference_no'] = $this->invoiceService->generateInvoiceName('posr-');

            $balance = round(floatval($data['grand_total']) - floatval($data['paid_amount']), 2);

            if (!empty($data['draft']) || (isset($data['sale_status']) && $data['sale_status'] == 3)) {
                $data['payment_status'] = 1;
            } elseif ($balance <= 0 && floatval($data['grand_total']) > 0) {
                $data['payment_status'] = 4;
            } elseif (floatval($data['paid_amount']) > 0 && $balance > 0) {
                $data['payment_status'] = 2;
            } else {
                $data['payment_status'] = 2;
            }

            if (!empty($data['draft']) && !empty($data['sale_id'])) {
                $lims_sale_data = Sale::find($data['sale_id']);
                if ($lims_sale_data) {
                    $lims_product_sale_data = Product_Sale::where('sale_id', $data['sale_id'])->get();
                    foreach ($lims_product_sale_data as $product_sale_data) {
                        $product_sale_data->delete();
                    }
                    $lims_sale_data->delete();
                }
            }
        } else {
            if (!isset($data['reference_no']))
                $data['reference_no'] = $this->invoiceService->generateInvoiceName('dsr-');
        }

        $document = $request->document;
        if ($document) {
            $v = Validator::make(
                [
                    'extension' => strtolower($request->document->getClientOriginalExtension()),
                ],
                [
                    'extension' => 'in:jpg,jpeg,png,gif,pdf,csv,docx,xlsx,txt',
                ]
            );
            if ($v->fails())
                return redirect()->back()->withErrors($v->errors());

            $ext = pathinfo($document->getClientOriginalName(), PATHINFO_EXTENSION);
            $documentName = date("Ymdhis");
            if (!config('database.connections.saleprosaas_landlord')) {
                $documentName = $documentName . '.' . $ext;
                $document->move(public_path('documents/sale'), $documentName);
            } else {
                $documentName = $this->getTenantId() . '_' . $documentName . '.' . $ext;
                $document->move(public_path('documents/sale'), $documentName);
            }
            $data['document'] = $documentName;
        }

        if (isset($data['table_id'])) {
            $latest_sale = Sale::whereNotNull('table_id')
                ->whereNull('deleted_at')
                ->where(function ($q) {
                    $q->where('sale_type', '!=', 'opening balance')
                        ->orWhereNull('sale_type');
                })
                ->whereDate('created_at', date('Y-m-d'))
                ->where('warehouse_id', $data['warehouse_id'])
                ->select('queue')->orderBy('id', 'desc')
                ->first();
            if ($latest_sale)
                $data['queue'] = $latest_sale->queue + 1;
            else
                $data['queue'] = 1;
        }

        if (isset($data['pay_term_no']) && $data['pay_term_no']) {
            $sale_date = isset($data['created_at']) ? $data['created_at'] : date('Y-m-d');
            if ($data['pay_term_period'] == 'days') {
                $data['due_date'] = date('Y-m-d', strtotime($sale_date . ' +' . $data['pay_term_no'] . ' days'));
            } elseif ($data['pay_term_period'] == 'months') {
                $data['due_date'] = date('Y-m-d', strtotime($sale_date . ' +' . $data['pay_term_no'] . ' months'));
            }
        }

        try {
            DB::beginTransaction();

            $creditService = app(CustomerCreditService::class);
            $isDraft = (isset($data['sale_status']) && $data['sale_status'] == 3);
            $validation = $creditService->validateCreditLimit(
                $data['customer_id'],
                floatval($data['grand_total']),
                array_sum((array)$new_data['paid_amount']),
                null,
                $isDraft
            );

            if (!$validation['allowed']) {
                DB::rollBack();
                if (request()->wantsJson() || request()->isXmlHttpRequest()) {
                    return response()->json(['error' => $validation['message']], 422);
                }
                return redirect()->back()->with('not_permitted', $validation['message'])->withInput();
            }

            if (!empty($data['coupon_active']) && empty($data['draft']) && !empty($data['coupon_id'])) {
                $lims_coupon_data = Coupon::find($data['coupon_id']);
                if ($lims_coupon_data) {
                    $lims_coupon_data->used += 1;
                    $lims_coupon_data->save();
                }
            }

            $lims_sale_data = Sale::create($data);

            $data['paid_amount'] = $new_data['paid_amount'];

            $custom_field_data = [];
            $custom_fields = CustomField::where('belongs_to', 'sale')->select('name', 'type')->get();
            foreach ($custom_fields as $type => $custom_field) {
                $field_name = str_replace(' ', '_', strtolower($custom_field->name));
                if (isset($data[$field_name])) {
                    if ($custom_field->type == 'checkbox' || $custom_field->type == 'multi_select')
                        $custom_field_data[$field_name] = implode(",", $data[$field_name]);
                    else
                        $custom_field_data[$field_name] = $data[$field_name];
                }
            }
            if (count($custom_field_data))
                DB::table('sales')->where('id', $lims_sale_data->id)->update($custom_field_data);

            $lims_customer_data = Customer::find($data['customer_id']);

            $lims_reward_point_setting_data = RewardPointSetting::latest()->first();
            if (
                $lims_reward_point_setting_data
                && $lims_reward_point_setting_data->is_active
                && !request()->has('redeem_point')
                && $data['grand_total'] >= $lims_reward_point_setting_data->minimum_amount
            ) {

                if ($lims_customer_data->type == CustomerTypeEnum::REGULAR->value) {

                    $isDraft = isset($data['draft']) && $data['draft'] == '0';
                    $isNotPaidBy7 = !in_array('7', $data['paid_by_id'] ?? []);

                    if ($isDraft && $isNotPaidBy7) {
                        $point = (int)($data['grand_total'] / $lims_reward_point_setting_data->per_point_amount);

                        $lims_customer_data->points += $point;
                        $lims_customer_data->save();

                        $expiredAt = null;
                        if ($lims_reward_point_setting_data->duration && $lims_reward_point_setting_data->type) {
                            switch ($lims_reward_point_setting_data->type) {
                                case 'days':
                                    $expiredAt = now()->addDays($lims_reward_point_setting_data->duration);
                                    break;
                                case 'months':
                                    $expiredAt = now()->addMonths($lims_reward_point_setting_data->duration);
                                    break;
                                case 'years':
                                    $expiredAt = now()->addYears($lims_reward_point_setting_data->duration);
                                    break;
                            }
                        }

                        RewardPoint::create([
                            'points' => $point,
                            'customer_id' => $lims_customer_data->id,
                            'note' => 'Earn Point for sale #' . $lims_sale_data->id,
                            'sale_id' => $lims_sale_data->id,
                            'expired_at' => $expiredAt,
                        ]);
                    }
                }
            }

            $mail_data['email'] = $lims_customer_data->email;
            $mail_data['reference_no'] = $lims_sale_data->reference_no;
            $mail_data['sale_status'] = $lims_sale_data->sale_status;
            $mail_data['payment_status'] = $lims_sale_data->payment_status;
            $mail_data['total_qty'] = $lims_sale_data->total_qty;
            $mail_data['total_price'] = $lims_sale_data->total_price;
            $mail_data['order_tax'] = $lims_sale_data->order_tax;
            $mail_data['order_tax_rate'] = $lims_sale_data->order_tax_rate;
            $mail_data['order_discount'] = $lims_sale_data->order_discount;
            $mail_data['shipping_cost'] = $lims_sale_data->shipping_cost;
            $mail_data['grand_total'] = $lims_sale_data->grand_total;
            $mail_data['paid_amount'] = $lims_sale_data->paid_amount;

            $product_id = $data['product_id'];
            $product_batch_id = $data['product_batch_id'];
            $imei_number = $data['imei_number'];
            $product_code = $data['product_code'];
            $qty = $data['qty'];
            $sale_unit = $data['sale_unit'];
            $net_unit_price = $data['net_unit_price'];
            $discount = $data['discount'];
            $tax_rate = $data['tax_rate'];
            $tax = $data['tax'];
            $total = $data['subtotal'];
            $product_sale = [];
            $log_data['item_description'] = '';

            foreach ($product_id as $i => $id) {
                $lims_product_data = Product::where('id', $id)->first();
                $product_sale['variant_id'] = null;
                $product_sale['product_batch_id'] = null;

                if ($lims_product_data->type == 'combo' && $data['sale_status'] == 1) {
                    $total_request_combo_qty = $qty[$i];
                    $product_list = explode(",", $lims_product_data->product_list);
                    $variant_list = $lims_product_data->variant_list
                        ? explode(",", $lims_product_data->variant_list)
                        : [];
                    $qty_list = explode(",", $lims_product_data->qty_list);
                    $price_list = explode(",", $lims_product_data->price_list);
                    $combo_unit_ids = $lims_product_data->combo_unit_id
                        ? explode(",", $lims_product_data->combo_unit_id)
                        : [];

                    $effective_combo_qty = $total_request_combo_qty;
                    if ($sale_unit[$i] != 'n/a') {
                        $lims_sale_unit_data_combo = Unit::where('unit_name', $sale_unit[$i])->first();
                        if ($lims_sale_unit_data_combo && $lims_sale_unit_data_combo->id != $lims_product_data->unit_id) {
                            if ($lims_sale_unit_data_combo->operator == '*')
                                $effective_combo_qty = $total_request_combo_qty * $lims_sale_unit_data_combo->operation_value;
                            elseif ($lims_sale_unit_data_combo->operator == '/')
                                $effective_combo_qty = $total_request_combo_qty / $lims_sale_unit_data_combo->operation_value;
                        }
                    }

                    foreach ($product_list as $key => $child_id) {
                        $child_data = Product::find($child_id);

                        if (!$child_data) {
                            continue;
                        }

                        $required = (float) $qty_list[$key];
                        if (isset($combo_unit_ids[$key]) && $combo_unit_ids[$key] != $child_data->unit_id) {
                            $unit = Unit::find($combo_unit_ids[$key]);
                            if ($unit) {
                                if ($unit->operator == '*') {
                                    $required = $required * $unit->operation_value;
                                } elseif ($unit->operator == '/') {
                                    $required = $required / $unit->operation_value;
                                }
                            }
                        }
                        $deduct_qty = $effective_combo_qty * $required;

                        if (count($variant_list) && isset($variant_list[$key]) && $variant_list[$key]) {
                            $child_product_variant_data = ProductVariant::where([
                                ['product_id', $child_id],
                                ['variant_id', $variant_list[$key]]
                            ])->first();

                            $child_warehouse_data = Product_Warehouse::where([
                                ['product_id', $child_id],
                                ['variant_id', $variant_list[$key]],
                                ['warehouse_id', $data['warehouse_id']],
                            ])->first();

                            if ($child_product_variant_data) {
                                $child_product_variant_data->qty -= $deduct_qty;
                                $child_product_variant_data->save();
                            }
                        } else {
                            $child_warehouse_data = Product_Warehouse::where([
                                ['product_id', $child_id],
                                ['warehouse_id', $data['warehouse_id']],
                            ])->first();
                        }

                        $child_data->qty -= $deduct_qty;
                        $child_data->save();

                        if ($child_warehouse_data) {
                            $child_warehouse_data->qty -= $deduct_qty;
                            $child_warehouse_data->save();
                        }
                    }
                }

                if ($sale_unit[$i] != 'n/a' && $lims_product_data->type != 'combo') {
                    $lims_sale_unit_data  = Unit::where('unit_name', $sale_unit[$i])->first();
                    $sale_unit_id = $lims_sale_unit_data->id;
                    if ($lims_product_data->is_variant) {
                        $lims_product_variant_data = ProductVariant::select('id', 'variant_id', 'qty')->FindExactProductWithCode($id, $product_code[$i])->first();
                        $product_sale['variant_id'] = $lims_product_variant_data->variant_id;
                    }
                    if ($lims_product_data->is_batch && $product_batch_id[$i]) {
                        $product_sale['product_batch_id'] = $product_batch_id[$i];
                    }

                    if ($data['sale_status'] == 1) {
                        if ($lims_sale_unit_data->operator == '*')
                            $quantity = $qty[$i] * $lims_sale_unit_data->operation_value;
                        elseif ($lims_sale_unit_data->operator == '/')
                            $quantity = $qty[$i] / $lims_sale_unit_data->operation_value;
                        $lims_product_data->qty = $lims_product_data->qty - $quantity;
                        $lims_product_data->save();

                        if ($lims_product_data->is_variant) {
                            $lims_product_variant_data->qty -= $quantity;
                            $lims_product_variant_data->save();
                            $lims_product_warehouse_data = Product_Warehouse::FindProductWithVariant($id, $lims_product_variant_data->variant_id, $data['warehouse_id'])->first();
                        } elseif ($product_batch_id[$i]) {
                            $lims_product_warehouse_data = Product_Warehouse::where([
                                ['product_batch_id', $product_batch_id[$i]],
                                ['warehouse_id', $data['warehouse_id']]
                            ])->first();
                            $lims_product_batch_data = ProductBatch::find($product_batch_id[$i]);
                            $lims_product_batch_data->qty -= $quantity;
                            $lims_product_batch_data->save();
                        } else {
                            $lims_product_warehouse_data = Product_Warehouse::FindProductWithoutVariant($id, $data['warehouse_id'])->first();
                        }
                        if ($lims_product_warehouse_data) {
                            $lims_product_warehouse_data->qty -= $quantity;
                            $lims_product_warehouse_data->save();
                        }
                    }
                } else
                    $sale_unit_id = 0;

                if ($product_sale['variant_id']) {
                    $variant_data = Variant::select('name')->find($product_sale['variant_id']);
                    $mail_data['products'][$i] = $lims_product_data->name . ' [' . $variant_data->name . ']';
                } else
                    $mail_data['products'][$i] = $lims_product_data->name;
                if ($imei_number[$i] && !str_contains($imei_number[$i], "null") && $data['sale_status'] == 1) {
                    $imei_numbers = explode(",", $imei_number[$i]);
                    $all_imei_numbers = explode(",", $lims_product_warehouse_data->imei_number);
                    foreach ($imei_numbers as $number) {
                        if (($j = array_search($number, $all_imei_numbers)) !== false) {
                            unset($all_imei_numbers[$j]);
                        }
                    }

                    $lims_product_warehouse_data->imei_number = implode(",", $all_imei_numbers);
                    $lims_product_warehouse_data->save();
                }
                if ($lims_product_data->type == 'digital')
                    $mail_data['file'][$i] = url('/product/files') . '/' . $lims_product_data->file;
                else
                    $mail_data['file'][$i] = '';


                if ($sale_unit_id) {
                    $log_data['item_description'] .= $lims_product_data->name . '-' . $qty[$i] . ' ' . $lims_sale_unit_data->unit_code . '<br>';
                    $mail_data['unit'][$i] = $lims_sale_unit_data->unit_code;
                } else {
                    $log_data['item_description'] .= $lims_product_data->name . '-' . $qty[$i] . '<br>';
                    $mail_data['unit'][$i] = '';
                }

                $product_sale['sale_id'] = $lims_sale_data->id;
                $product_sale['product_id'] = $id;
                if ($imei_number[$i] && !str_contains($imei_number[$i], "null")) {
                    $product_sale['imei_number'] = $imei_number[$i];
                } else {
                    $product_sale['imei_number'] = null;
                }
                $product_sale['qty'] = $mail_data['qty'][$i] = $qty[$i];
                $product_sale['sale_unit_id'] = $sale_unit_id;
                $product_sale['net_unit_price'] = $net_unit_price[$i];
                $product_sale['discount'] = $discount[$i];
                $product_sale['tax_rate'] = $tax_rate[$i];
                $product_sale['tax'] = $tax[$i];
                $product_sale['total'] = $mail_data['total'][$i] = $total[$i];

                if ($this->restaurantModifiersAvailable()) {
                    $product_sale['topping_id'] = null;
                    if (!empty($data['topping_product'][$i])) {
                        $product_sale['topping_id'] = $data['topping_product'][$i];
                    }
                }

                $created_product_sale = Product_Sale::create($product_sale);

                if ($this->restaurantModifiersAvailable()) {
                        $modifierPayload = $data['topping_product'][$i] ?? null;
                        $modifiers = app(\Modules\Restaurant\Services\ModifierSelectionService::class)
                            ->resolve((int) $id, $modifierPayload);
                        if (is_array($modifiers)) {
                            foreach ($modifiers as $modifierData) {
                                \DB::table('product_sale_modifiers')->insert([
                                    'product_sale_id' => $created_product_sale->id,
                                    'modifier_group_id' => $modifierData['modifier_group_id'],
                                    'modifier_id' => $modifierData['modifier_id'],
                                    'modifier_group_name' => $modifierData['modifier_group_name'],
                                    'modifier_name' => $modifierData['modifier_name'],
                                    'price_adjustment' => $modifierData['price_adjustment'],
                                    'product_list' => $modifierData['product_list'],
                                    'qty_list' => $modifierData['qty_list'],
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ]);

                                $productList = $modifierData['product_list'];
                                $qtyList = $modifierData['qty_list'];

                                if ($data['sale_status'] == 1) {

                                    if (!empty($productList)) {
                                        $mod_product_ids = explode(',', $productList);
                                        $mod_qtys = explode(',', $qtyList);

                                        foreach ($mod_product_ids as $k => $mod_product_id) {
                                            $mod_qty = (float)($mod_qtys[$k] ?? 1) * (float)($modifierData['qty'] ?? 1) * $quantity;

                                            $mod_product = \App\Models\Product::find($mod_product_id);
                                            if ($mod_product && $mod_product->type == 'standard') {
                                                $mod_product->qty -= $mod_qty;
                                                $mod_product->save();

                                                $mod_warehouse = \App\Models\Product_Warehouse::where([
                                                    ['product_id', $mod_product_id],
                                                    ['warehouse_id', $data['warehouse_id']]
                                                ])->first();
                                                if ($mod_warehouse) {
                                                    $mod_warehouse->qty -= $mod_qty;
                                                    $mod_warehouse->save();
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                }
            }
            if ($data['sale_status'] == 3)
                $message = 'Sale successfully added to draft';
            else
                $message = ' Sale created successfully';

            $log_data['action'] = 'Sale Created';
            $log_data['user_id'] = Auth::id();
            $log_data['reference_no'] = $lims_sale_data->reference_no;
            $log_data['date'] = $lims_sale_data->created_at->toDateString();
            $log_data['admin_message'] = Auth::user()->name . ' has created a sale. Reference No: ' . $lims_sale_data->reference_no;
            $log_data['user_email'] = Auth::user()->email;
            $log_data['user_name'] = Auth::user()->name;
            $log_data['user_message'] = 'You just created a sale. Reference No: ' . $lims_sale_data->reference_no;
            $this->createActivityLog($log_data);


        if ($request->enable_installment) {
            $installment_plan_data = $request->installment_plan;
            $installment_plan_data['reference_id'] = $lims_sale_data->id;
            (new InstallmentPlanController)->store($installment_plan_data);
        }



        $paidByIds = isset($data['paid_by_id']) ? (array)$data['paid_by_id'] : [];
        if (in_array('razorpay', $paidByIds)) {
            foreach ($paidByIds as $key => $value) {
                if ($value == 'razorpay') {
                    $lims_payment_data = new Payment();
                    $lims_payment_data->user_id = Auth::id();
                    $lims_payment_data->sale_id = $lims_sale_data->id;

                    $lims_payment_data->payment_reference = $this->invoiceService->generateInvoiceName('raz-');
                    $lims_payment_data->amount = is_array($data['paid_amount']) ? ($data['paid_amount'][$key] ?? 0) : $data['paid_amount'];
                    $lims_payment_data->paying_method = 'Razorpay';
                    $lims_payment_data->payment_note = 'Payment via Razorpay. Payment ID: ' . ($data['razorpay_payment_id'] ?? '');
                    $lims_payment_data->currency_id = $lims_sale_data->currency_id;
                    $lims_payment_data->exchange_rate = $lims_sale_data->exchange_rate ?? 1;

                    if ($cash_register_data) {
                        $lims_payment_data->cash_register_id = $cash_register_data->id;
                    }

                    $lims_payment_data->save();

                    $data['payment_id'] = $lims_payment_data->id;

                    $accountingService = app(AccountingService::class);
                    $result = $accountingService->recordPayment($lims_payment_data);
                    if (!$result->success) {
                        \Log::error('Accounting failed for Sale Payment', ['payment_id' => $lims_payment_data->id, 'error' => $result->error]);
                        if (\Schema::hasColumn($lims_payment_data->getTable(), 'accounting_status')) {
                            $lims_payment_data->accounting_status = 'failed';
                            $lims_payment_data->save();
                        }
                    }
                }
            }
        } elseif (!empty($paidByIds) && ($data['payment_status'] == 3 || $data['payment_status'] == 4 || ($data['payment_status'] == 2 && (is_array($data['paid_amount']) ? array_sum($data['paid_amount']) : floatval($data['paid_amount'])) > 0))) {
            foreach ($paidByIds as $key => $value) {
                $pAmount = is_array($data['paid_amount']) ? floatval($data['paid_amount'][$key] ?? 0) : floatval($data['paid_amount']);
                $payingAmt = (isset($data['paying_amount']) && is_array($data['paying_amount'])) ? floatval($data['paying_amount'][$key] ?? $pAmount) : $pAmount;

                if ($pAmount > 0) {
                    $lims_payment_data = new Payment();
                    $lims_payment_data->user_id = Auth::id();
                    $paying_method = '';

                    if ($value == 1 || $value === '1' || $value === 'cash' || $value === 'Cash') {
                        $paying_method = 'Cash';
                    } elseif ($value == 2 || $value === '2' || $value === 'gift_card' || $value === 'Gift Card') {
                        $paying_method = 'Gift Card';
                    } elseif ($value == 3 || $value === '3' || $value === 'card' || $value === 'Credit Card') {
                        $paying_method = 'Credit Card';
                    } elseif ($value == 4 || $value === '4' || $value === 'cheque' || $value === 'Cheque') {
                        $paying_method = 'Cheque';
                    } elseif ($value == 5 || $value === '5' || $value === 'paypal' || $value === 'Paypal') {
                        $paying_method = 'Paypal';
                    } elseif ($value == 6 || $value === '6' || $value === 'deposit' || $value === 'Deposit') {
                        $paying_method = 'Deposit';
                    } elseif ($value == 7 || $value === '7' || $value === 'points' || $value === 'Points') {
                        $paying_method = 'Points';
                        if ($lims_reward_point_setting_data && $lims_reward_point_setting_data->is_active && request()->has('redeem_point')) {
                            $reward_points = RewardPoint::query()->create([
                                'points' => 0,
                                'deducted_points' => $request->redeem_point,
                                'customer_id' => $lims_customer_data->id,
                                'note' => 'Redeemed for sale #' . $lims_sale_data->id,
                                'sale_id' => $lims_sale_data->id,
                                'expired_at' => null,
                            ]);
                            $lims_customer_data->update(['points' => $lims_customer_data->points - $request->redeem_point]);
                        }
                    } elseif ($value == 8 || $value === '8' || $value === 'pesapal' || $value === 'Pesapal') {
                        $paying_method = 'Pesapal';
                    } elseif ($value === 'credit_sale' || $value === 'credit') {
                        $paying_method = 'Credit Sale';
                    } else {
                        $paying_method = ucfirst($value);
                    }

                    if ($cash_register_data) {
                        $lims_payment_data->cash_register_id = $cash_register_data->id;
                    }
                    $lims_account_data = Account::where('is_default', true)->first();
                    if (!empty($data['account_id']) && $data['account_id'] != 0) {
                        $lims_payment_data->account_id = $data['account_id'];
                    } else {
                        $lims_payment_data->account_id = $lims_account_data ? $lims_account_data->id : null;
                    }
                    $lims_payment_data->sale_id = $lims_sale_data->id;
                    $data['payment_reference'] = $this->invoiceService->generateInvoiceName('spr-');
                    $lims_payment_data->payment_reference = $data['payment_reference'];
                    $lims_payment_data->amount = $pAmount;
                    $lims_payment_data->change = max(0, $payingAmt - $pAmount);
                    $lims_payment_data->paying_method = $paying_method;
                    $lims_payment_data->payment_note = $data['payment_note'] ?? null;
                    $lims_payment_data->payment_at = date('Y-m-d H:i:s');

                    if (isset($data['payment_receiver'])) {
                        $lims_payment_data->payment_receiver = $data['payment_receiver'];
                    }
                    $lims_payment_data->currency_id = $lims_sale_data->currency_id;
                    $lims_payment_data->exchange_rate = $lims_sale_data->exchange_rate ?? 1;

                    $lims_payment_data->save();

                    $accountingService = app(AccountingService::class);
                    $result = $accountingService->recordPayment($lims_payment_data);
                    if (!$result->success) {
                        \Log::error('Accounting failed for Sale Payment', ['payment_id' => $lims_payment_data->id, 'error' => $result->error]);
                        if (\Schema::hasColumn($lims_payment_data->getTable(), 'accounting_status')) {
                            $lims_payment_data->accounting_status = 'failed';
                            $lims_payment_data->save();
                        }
                    }

                    $data['payment_id'] = $lims_payment_data->id;
                    if ($paying_method == 'Credit Card') {
                        if (!empty($data['card_number'])) {
                            $cardDetails = [];
                            $cardDetails['card_number'] = $data['card_number'];
                            $cardDetails['card_holder_name'] = $data['card_holder_name'] ?? '';
                            $cardDetails['card_type'] = $data['card_type'] ?? '';
                            $data['charge_id'] = $data['charge_id'] ?? '12345';
                            $data['data'] = json_encode($cardDetails);

                            PaymentWithCreditCard::create($data);
                        }
                    } elseif ($paying_method == 'Gift Card') {
                        if (!empty($data['gift_card_id'])) {
                            $lims_gift_card_data = GiftCard::find($data['gift_card_id']);
                            if ($lims_gift_card_data) {
                                $lims_gift_card_data->expense += $pAmount;
                                $lims_gift_card_data->save();
                                PaymentWithGiftCard::create($data);
                            }
                        }
                    } elseif ($paying_method == 'Cheque') {
                        if (!empty($data['cheque_no'])) {
                            PaymentWithCheque::create($data);
                        }
                    } elseif ($paying_method == 'Deposit') {
                        if ($lims_customer_data) {
                            $lims_customer_data->deposit -= $pAmount;
                            $lims_customer_data->save();
                        }
                    } elseif ($paying_method == 'Points') {
                        if (!isset($data['draft']) && isset($data['used_points'])) {
                            $lims_customer_data->points -= $data['used_points'];
                            $lims_customer_data->save();
                        }
                    } elseif ($paying_method == 'Pesapal') {
                        $redirectUrl = $this->submitOrderRequest($lims_customer_data, $pAmount);
                        $lims_customer_data->save();

                        DB::commit();

                        $mail_setting = MailSetting::latest()->first();
                        if (!empty($mail_data['email']) && $data['sale_status'] == 1 && $mail_setting) {
                            $this->setMailInfo($mail_setting);
                            try {
                                Mail::to($mail_data['email'])->send(new SaleDetails($mail_data));
                            } catch (\Exception $e) {
                            }
                        }

                        return response()->json([
                            'payment_method' => 'pesapal',
                            'redirect_url' => $redirectUrl,
                        ]);
                    }
                }
            }
        }

        DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Sale creation failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => Auth::id(),
            ]);

            if (request()->ajax()) {
                return response()->json(['error' => 'Sale creation failed: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('not_permitted', 'Sale creation failed: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
        }

        try {
            $accountingService = app(AccountingService::class);
            $res = $accountingService->recordSale($lims_sale_data, 'sale_created');
            if (!$res->success) {
                throw new AccountingException($res->error);
            }
            if (\Schema::hasColumn($lims_sale_data->getTable(), 'accounting_status')) {
                $lims_sale_data->accounting_status = 'posted';
                $lims_sale_data->save();
            }
        } catch (AccountingException $e) {
            \Log::error('Accounting error on Sale Store: ' . $e->getMessage());
            if (\Schema::hasColumn($lims_sale_data->getTable(), 'accounting_status')) {
                $lims_sale_data->accounting_status = 'failed';
                $lims_sale_data->save();
            }
        }

        $mail_setting = MailSetting::latest()->first();
        if (isset($mail_data['email']) && $mail_data['email'] && $data['sale_status'] == 1 && $mail_setting) {
            $this->setMailInfo($mail_setting);
            try {
                Mail::to($mail_data['email'])->send(new SaleDetails($mail_data));
            } catch (\Exception $e) {
                $message = ' Sale created successfully. Please setup your <a href="setting/mail_setting">mail setting</a> to send mail.';
            }
        }

        $smsData = [];

        $smsTemplate = SmsTemplate::where('is_default', 1)->latest()->first();
        $smsProvider = ExternalService::where('active', true)->where('type', 'sms')->first();
        if ($smsProvider && $smsTemplate && $lims_pos_setting_data['send_sms'] == 1) {
            $smsData['type'] = 'onsite';
            $smsData['template_id'] = $smsTemplate['id'];
            $smsData['sale_status'] = $data['sale_status'];
            $smsData['payment_status'] = $data['payment_status'];
            $smsData['customer_id'] = $data['customer_id'];
            $smsData['reference_no'] = $data['reference_no'];
            $this->_smsModel->initialize($smsData);
        }

        if (request()->ajax()) {

            if ($lims_sale_data->sale_status == '1') {
                return response()->json($lims_sale_data->id);
            } elseif (
                in_array('restaurant', explode(',', gen_setting()->modules))
                && $lims_sale_data->sale_status == '5'
            ) {
                return response()->json($lims_sale_data->id);
            } elseif ($data['pos']) {
                return response()->json(['redirect' => url('pos')]);
            } else {
                return response()->json(['redirect' => route('delivery-sale.show', $lims_sale_data->id)]);
            }
        } else {

            if ($lims_sale_data->sale_status == '1' || (in_array('restaurant', explode(',', gen_setting()->modules)) && $lims_sale_data->sale_status == '5')) {
                return redirect(route('delivery-sale.invoice', $lims_sale_data->id));
            }

            if ($data['pos']) {
                return redirect('pos')->with('message', $message);
            }

            return redirect(route('delivery-sale.show', $lims_sale_data->id))->with('message', $message);
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

    public function invoice($id)
    {
        $is_print = filter_var(request()->query('is_print'), FILTER_VALIDATE_BOOLEAN);

        try {
            $lims_sale_data = Sale::with(['currency', 'user', 'customer', 'warehouse', 'biller', 'deliveryMan', 'route'])->find($id);

            if (!$lims_sale_data) {
                return redirect()->back()->with('not_permitted', 'Sale not found');
            }

            $lims_biller_data = Biller::find($lims_sale_data->biller_id);
            $lims_warehouse_data = Warehouse::find($lims_sale_data->warehouse_id);
            $lims_customer_data = Customer::find($lims_sale_data->customer_id);

            $lims_product_sale_data = Product_Sale::where('sale_id', $id)->get();

            $lims_payment_data = Payment::where('sale_id', $id)->get();

            $paid_by_info = $lims_payment_data->map(function ($payment) {
                return $payment->paying_method ?? 'Cash';
            })->unique()->implode(', ');

            $numberTransformer = (new NumberToWords())->getNumberTransformer('en');
            $numberInWords = $numberTransformer->toWords($lims_sale_data->grand_total);

            $currency = \App\Models\Currency::find($lims_sale_data->currency_id);
            $currency_code = $currency ? $currency->code : 'USD';

            $productIds = $lims_product_sale_data->pluck('product_id')->unique();
            $productMap = Product::whereIn('id', $productIds)->get()->keyBy('id');

            $variantIds = $lims_product_sale_data->pluck('variant_id')->filter()->unique();
            $variantMap = $variantIds->isNotEmpty()
                ? ProductVariant::select('id', 'name')->whereIn('id', $variantIds)->get()->keyBy('id')
                : collect();

            $batchIds = $lims_product_sale_data->pluck('product_batch_id')->filter()->unique();
            $batchMap = $batchIds->isNotEmpty()
                ? ProductBatch::select('id', 'batch_no')->whereIn('id', $batchIds)->get()->keyBy('id')
                : collect();

            $line_items = [];
            foreach ($lims_product_sale_data as $key => $psd) {
                $product = $productMap->get($psd->product_id);
                $item = new \stdClass();
                $item->product_name = $product ? $product->name : 'Unknown Product';
                if ($psd->variant_id && $variantMap->has($psd->variant_id)) {
                    $item->product_name .= ' [' . $variantMap->get($psd->variant_id)->name . ']';
                }
                $item->variant_name = ($psd->variant_id && $variantMap->has($psd->variant_id)) ? $variantMap->get($psd->variant_id)->name : '';
                $item->imei_number = $psd->imei_number;
                $item->net_unit_price = $psd->net_unit_price;
                $item->discount = $psd->discount;
                $item->qty = $psd->qty;
                $item->total = $psd->total;
                $item->tax = $psd->tax;
                $item->unit_code = '';
                $line_items[] = $item;
            }

            // QR text
            $qrText = $lims_sale_data->reference_no;

            // Customer financials
            $returned_amount = DB::table('sales')
                ->join('returns', 'sales.id', '=', 'returns.sale_id')
                ->where('sales.customer_id', $lims_customer_data->id)
                ->where('sales.payment_status', '!=', 4)
                ->whereNull('sales.deleted_at')
                ->sum('returns.grand_total');

            $saleData = DB::table('sales')
                ->where('customer_id', $lims_customer_data->id)
                ->where('payment_status', '!=', 4)
                ->whereNull('sales.deleted_at')
                ->selectRaw('SUM(grand_total) as grand_total, SUM(paid_amount) as paid_amount')
                ->first();

            $totalDue = $saleData->grand_total - $returned_amount - $saleData->paid_amount;
            $prevDue = $totalDue - ($lims_sale_data->grand_total - $lims_sale_data->paid_amount);
            $change_amount = 0;

            // Custom fields
            $allCustomFields = CustomField::where('is_invoice', true)
                ->whereIn('belongs_to', ['sale', 'customer', 'product'])
                ->get()
                ->groupBy('belongs_to');

            $sale_custom_fields = $allCustomFields->get('sale', collect())->pluck('name');
            $customer_custom_fields = $allCustomFields->get('customer', collect())->pluck('name');
            $product_custom_fields = $allCustomFields->get('product', collect())->pluck('name');

            // Biller info
            $lims_bill_by = $lims_sale_data->user->only(['name', 'email']);

            // Installment info
            $lims_installment_plan_data = DB::table('installment_plans')->where([
                ['reference_type', 'sale'],
                ['reference_id', $lims_sale_data->id]
            ])->first();

            $installment_info = null;
            if ($lims_installment_plan_data) {
                $inst_all = DB::table('installments')->where('installment_plan_id', $lims_installment_plan_data->id)->get();
                $installment_info = new \stdClass();
                $installment_info->plan = $lims_installment_plan_data;
                $installment_info->total = $inst_all->count();
                $installment_info->paid = $inst_all->where('status', 'completed')->count();
                $installment_info->next = $inst_all->where('status', 'pending')->sortBy('payment_date')->first();
            }

            $invoice_settings = \App\Models\InvoiceSetting::active_setting();

            $viewData = compact(
                'invoice_settings',
                'lims_sale_data',
                'currency_code',
                'lims_product_sale_data',
                'lims_biller_data',
                'lims_warehouse_data',
                'lims_customer_data',
                'lims_payment_data',
                'numberInWords',
                'paid_by_info',
                'line_items',
                'qrText',
                'prevDue',
                'totalDue',
                'change_amount',
                'lims_bill_by',
                'sale_custom_fields',
                'customer_custom_fields',
                'product_custom_fields',
                'installment_info'
            );

            $viewData['back_url'] = route('delivery-sale.show', $id);

            if ($invoice_settings && in_array($invoice_settings->size, ['58mm', '80mm'])) {
                return view('backend.setting.invoice_setting.' . $invoice_settings->size, $viewData);
            }

            return view('backend.setting.invoice_setting.a4', $viewData);

        } catch (\Exception $e) {
            Log::error('Delivery Invoice error: ' . $e->getMessage() . ' on line ' . $e->getLine() . ' in ' . $e->getFile());
            return redirect()->back()->with('not_permitted', 'Invoice Error: ' . $e->getMessage());
        }
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
                'warehouse_id',
                'customer_id',
                'biller_id',
                'route_id',
                'delivery_man_id',
                'sale_date',
                'due_date',
                'payment_status',
                'sale_status',
                'paid_amount',
                'order_tax_rate',
                'order_discount_value',
                'shipping_cost',
                'sale_note'
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
                              <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' . __('db.action') . '
                                <span class="caret"></span>
                                <span class="sr-only">Toggle Dropdown</span>
                              </button>
                               <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                                   <li>
                                       <a href="' . route("delivery-sale.show", $sale->id) . '" class="btn btn-link"><i class="ti ti-eye"></i> ' . __("db.View") . '</a>
                                   </li>
                                   <li>
                                       <a href="' . route("delivery-sale.edit", $sale->id) . '" class="btn btn-link"><i class="ti ti-edit"></i> ' . __("db.edit") . '</a>
                                   </li>
                                   <li class="divider"></li>
                                   <li>
                                       <button type="button" class="toggle-status btn btn-link" data-id="' . $sale->id . '"><i class="ti ti-toggle-left"></i> ' . __("db.Toggle Status") . '</button>
                                   </li>
                                   <li class="divider"></li>
                                   <form action="' . route("delivery-sale.delete", $sale->id) . '" method="POST">' . csrf_field() . '' . method_field("POST") . '
                                   <li>
                                     <button type="submit" class="btn btn-link confirm-delete-btn" data-id="' . $sale->id . '" data-name="' . $sale->reference_no . '"><i class="ti ti-trash"></i> ' . __("db.delete") . '</button>
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
