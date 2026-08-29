<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Product_Sale;
use App\Models\Product_Warehouse;
use App\Models\Sale;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Biller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    use ApiResponse;

    /**
     * Parse and normalize input from Request (JSON, Form-Data, Query, Raw Body)
     */
    protected function getRequestData(Request $request): array
    {
        $data = [];

        $all = $request->all();
        if (is_array($all) && !empty($all)) {
            $data = array_merge($data, $all);
        }

        if (!empty($_POST) && is_array($_POST)) {
            $data = array_merge($data, $_POST);
        }
        if (!empty($_GET) && is_array($_GET)) {
            $data = array_merge($data, $_GET);
        }
        if (!empty($_REQUEST) && is_array($_REQUEST)) {
            $data = array_merge($data, $_REQUEST);
        }

        $json = $request->json() ? $request->json()->all() : [];
        if (is_array($json) && !empty($json)) {
            $data = array_merge($data, $json);
        }

        $raw = $request->getContent();
        if (empty($raw)) {
            $raw = @file_get_contents('php://input');
        }

        if (!empty($raw) && is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && !empty($decoded)) {
                $data = array_merge($data, $decoded);
            } elseif (str_contains($raw, 'name=')) {
                if (preg_match_all('/name=["\']?([^"\';\r\n]+)["\']?.*?(?:\r?\n\r?\n|\n\n)(.*?)(?:\r?\n--|\n--|$)/s', $raw, $matches, PREG_SET_ORDER)) {
                    foreach ($matches as $m) {
                        $key = trim($m[1]);
                        $val = trim($m[2]);
                        $data[$key] = $val;
                    }
                }
            } else {
                parse_str($raw, $parsed);
                if (is_array($parsed) && !empty($parsed)) {
                    $data = array_merge($data, $parsed);
                }
            }
        }

        $query = $request->query();
        if (is_array($query) && !empty($query)) {
            $data = array_merge($data, $query);
        }

        return $data;
    }

    /**
     * Place a New Order
     */
    public function placeOrder(Request $request)
    {
        $data = $this->getRequestData($request);
        $request->merge($data);

        $validator = Validator::make($request->all(), [
            // Customer & Contact (Required if not using a saved address_id)
            'address_id'       => 'nullable|integer',
            'name'             => 'required_without:address_id|nullable|string|max:191',
            'phone'            => 'required_without:address_id|nullable|string|max:191',
            'email'            => 'nullable|email|max:191',

            // Shipping Address (Required if not using a saved address_id)
            'shipping_address' => 'required_without:address_id|nullable|string|max:255',
            'shipping_city'    => 'required_without:address_id|nullable|string|max:191',
            'shipping_state'   => 'nullable|string|max:191',
            'shipping_country' => 'nullable|string|max:191',
            'shipping_zip'     => 'nullable|string|max:50',

            // Billing Address (optional, defaults to shipping)
            'billing_address'  => 'nullable|string|max:255',
            'billing_city'     => 'nullable|string|max:191',
            'billing_state'    => 'nullable|string|max:191',
            'billing_country'  => 'nullable|string|max:191',
            'billing_zip'      => 'nullable|string|max:50',

            // Order Parameters
            'delivery_area_id' => 'nullable|integer',
            'coupon_code'      => 'nullable|string',
            'payment_mode'     => 'required|string', // 'Cash on Delivery', 'cod', 'qr_code', 'stripe', 'sslcommerz', etc.
            'sale_note'        => 'nullable|string|max:1000',

            // Optional direct items array
            'items'            => 'nullable|array',
            'items.*.product_id' => 'required_with:items|integer|exists:products,id',
            'items.*.qty'        => 'required_with:items|numeric|min:1',
            'items.*.variant_id' => 'nullable|integer',

            // Payment proof file for manual / QR payment
            'payment_proof'    => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:5120',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray(), 'Validation failed', 422);
        }

        $user = Auth::guard('api')->user() ?? $request->user();
        if (!$user) {
            return $this->unauthorized('Please log in or initialize guest session to place an order');
        }

        DB::beginTransaction();
        try {
            $userId = $user->id;

            // 1. Resolve Customer Record
            $customer = Customer::where('user_id', $userId)->first();
            if (!$customer) {
                $customer = Customer::firstOrCreate(
                    ['email' => $user->email],
                    [
                        'user_id'           => $userId,
                        'customer_group_id' => 1,
                        'name'              => trim($request->input('name', $user->name)),
                        'phone_number'      => trim($request->input('phone', $user->phone ?? '0000000000')),
                        'address'           => trim((string)$request->input('shipping_address', 'Default Address')),
                        'city'              => trim((string)$request->input('shipping_city', 'Default City')),
                        'is_active'         => 1,
                    ]
                );
            }

            // 2. Resolve Shipping & Contact Info (from saved address_id OR request body)
            $addressId = $request->input('address_id');
            $savedAddress = null;

            if ($addressId) {
                $savedAddress = CustomerAddress::where('customer_id', $customer->id)->where('id', $addressId)->first();
            }

            $shippingName    = $savedAddress ? $savedAddress->name : trim((string)$request->input('name', $customer->name));
            $shippingPhone   = $savedAddress ? $savedAddress->phone : trim((string)$request->input('phone', $customer->phone_number));
            $shippingEmail   = $savedAddress ? ($savedAddress->email ?? $customer->email) : ($request->input('email') ? trim($request->input('email')) : $customer->email);
            $shippingAddress = $savedAddress ? $savedAddress->address : trim((string)$request->input('shipping_address'));
            $shippingCity    = $savedAddress ? $savedAddress->city : trim((string)$request->input('shipping_city'));
            $shippingState   = $savedAddress ? $savedAddress->state : ($request->input('shipping_state') ? trim($request->input('shipping_state')) : null);
            $shippingCountry = $savedAddress ? ($savedAddress->country ?? 'Bangladesh') : ($request->input('shipping_country') ? trim($request->input('shipping_country')) : 'Bangladesh');
            $shippingZip     = $savedAddress ? $savedAddress->zip : ($request->input('shipping_zip') ? trim($request->input('shipping_zip')) : null);

            // Save new address if explicit address provided and not using saved address
            if (!$addressId && $request->filled('shipping_address')) {
                $existingAddr = CustomerAddress::where('customer_id', $customer->id)
                    ->where('address', $shippingAddress)
                    ->where('city', $shippingCity)
                    ->first();

                if (!$existingAddr) {
                    CustomerAddress::create([
                        'customer_id' => $customer->id,
                        'name'        => $shippingName,
                        'phone'       => $shippingPhone,
                        'email'       => $shippingEmail,
                        'address'     => $shippingAddress,
                        'city'        => $shippingCity,
                        'state'       => $shippingState,
                        'country'     => $shippingCountry,
                        'zip'         => $shippingZip,
                        'default'     => 0,
                    ]);
                }
            }

            // Billing address
            $billingName    = $request->filled('billing_name') ? trim($request->billing_name) : $shippingName;
            $billingPhone   = $request->filled('billing_phone') ? trim($request->billing_phone) : $shippingPhone;
            $billingEmail   = $request->filled('billing_email') ? trim($request->billing_email) : $shippingEmail;
            $billingAddress = $request->filled('billing_address') ? trim($request->billing_address) : $shippingAddress;
            $billingCity    = $request->filled('billing_city') ? trim($request->billing_city) : $shippingCity;
            $billingState   = $request->filled('billing_state') ? trim($request->billing_state) : $shippingState;
            $billingCountry = $request->filled('billing_country') ? trim($request->billing_country) : $shippingCountry;
            $billingZip     = $request->filled('billing_zip') ? trim($request->billing_zip) : $shippingZip;

            // 3. Resolve Store Defaults
            $ecomSetting = DB::table('ecommerce_settings')->first();
            $warehouseId = $ecomSetting && $ecomSetting->warehouse_id ? (int)$ecomSetting->warehouse_id : (Warehouse::first()->id ?? 1);
            $billerId = $ecomSetting && $ecomSetting->biller_id ? (int)$ecomSetting->biller_id : (Biller::first()->id ?? 1);
            $sellWithoutStock = $ecomSetting ? (bool)$ecomSetting->sell_without_stock : false;

            // 4. Compute Totals & Items
            $checkoutCtrl = app(CheckoutController::class);
            $calculation = $checkoutCtrl->computeTotals(
                $userId,
                $request->input('items'),
                $request->input('delivery_area_id'),
                null,
                $request->input('coupon_code')
            );

            $items = $calculation['items'];
            $summary = $calculation['summary'];

            if (empty($items)) {
                return $this->badRequest('Your order cannot be placed because the cart is empty.');
            }

            // 5. Validate Stock for Each Item
            foreach ($items as $item) {
                $product = Product::find($item['product_id']);
                if (!$product || !$product->is_active) {
                    return $this->badRequest('Product "' . ($product->name ?? 'Unknown') . '" is unavailable.');
                }

                if (!$sellWithoutStock && (float)$product->qty < (float)$item['qty']) {
                    return $this->badRequest('Insufficient stock for "' . $product->name . '". Only ' . (float)$product->qty . ' available.');
                }
            }

            // 6. Normalized Payment Mode
            $rawPaymentMode = trim((string)$request->payment_mode);
            $paymentMode = $this->normalizePaymentMode($rawPaymentMode);

            // 7. Generate Unique Order Reference Number
            $referenceNo = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            $couponId = isset($calculation['applied_coupon']['id']) ? $calculation['applied_coupon']['id'] : null;
            $couponDiscount = $summary['coupon_discount'];

            // 8. Create Sale Record
            $sale = Sale::create([
                'reference_no'     => $referenceNo,
                'user_id'          => $userId,
                'customer_id'      => $customer->id,
                'warehouse_id'     => $warehouseId,
                'biller_id'        => $billerId,
                'item'             => $summary['total_items'],
                'total_qty'        => $summary['total_qty'],
                'total_discount'   => $summary['product_savings'],
                'total_tax'        => $summary['tax'],
                'total_price'      => $summary['subtotal'],
                'grand_total'      => $summary['grand_total'],
                'coupon_id'        => $couponId,
                'coupon_discount'  => $couponDiscount,
                'shipping_cost'    => $summary['shipping_cost'],
                'sale_status'      => 2, // 2 = Pending
                'payment_status'   => 1, // 1 = Pending
                'payment_mode'     => $paymentMode,
                'sale_type'        => 'online',
                'sale_note'        => $request->filled('sale_note') ? trim($request->sale_note) : null,
                'billing_name'     => $billingName,
                'billing_phone'    => $billingPhone,
                'billing_email'    => $billingEmail,
                'billing_address'  => $billingAddress,
                'billing_city'     => $billingCity,
                'billing_state'    => $billingState,
                'billing_country'  => $billingCountry,
                'billing_zip'      => $billingZip,
                'shipping_name'    => $shippingName,
                'shipping_phone'   => $shippingPhone,
                'shipping_email'   => $shippingEmail,
                'shipping_address' => $shippingAddress,
                'shipping_city'    => $shippingCity,
                'shipping_state'   => $shippingState,
                'shipping_country' => $shippingCountry,
                'shipping_zip'     => $shippingZip,
                'created_at'       => now(),
            ]);

            // 9. Create Product_Sale rows & Deduct Inventory Stock
            foreach ($items as $item) {
                $variantId = isset($item['variant']['id']) ? (int)$item['variant']['id'] : null;

                Product_Sale::create([
                    'sale_id'        => $sale->id,
                    'product_id'     => $item['product_id'],
                    'variant_id'     => $variantId,
                    'qty'            => $item['qty'],
                    'sale_unit_id'   => $item['sale_unit_id'] ?? 1,
                    'net_unit_price' => $item['unit_price'],
                    'discount'       => $item['savings'] ?? 0,
                    'tax_rate'       => 0,
                    'tax'            => 0,
                    'total'          => $item['total_price'],
                    'is_delivered'   => 0,
                    'is_packing'     => 0,
                ]);

                // Deduct Product Master Stock
                $product = Product::find($item['product_id']);
                if ($product) {
                    $product->qty = max(0, $product->qty - $item['qty']);
                    if ($product->qty == 0) {
                        $product->in_stock = 0;
                    }
                    $product->save();
                }

                // Deduct Warehouse Stock
                $pw = Product_Warehouse::where('product_id', $item['product_id'])
                    ->where('warehouse_id', $warehouseId)
                    ->first();

                if ($pw) {
                    $pw->qty = max(0, $pw->qty - $item['qty']);
                    $pw->save();
                }
            }

            // 10. Update Coupon Usage if applied
            if ($couponId) {
                $coupon = Coupon::find($couponId);
                if ($coupon) {
                    $coupon->used = ($coupon->used ?? 0) + 1;
                    $coupon->save();
                }
            }

            // 11. Handle Payment Proof for QR / Manual Payment
            $paymentProofName = null;
            if ($request->hasFile('payment_proof')) {
                $file = $request->file('payment_proof');
                if ($file->isValid()) {
                    $ext = $file->getClientOriginalExtension();
                    $paymentProofName = 'proof_' . date('YmdHis') . '_' . Str::random(6) . '.' . $ext;
                    $uploadPath = public_path('images/payment-proof');
                    if (!file_exists($uploadPath)) {
                        mkdir($uploadPath, 0755, true);
                    }
                    $file->move($uploadPath, $paymentProofName);
                }
            }

            // If QR code or Paid, create Payment record
            if ($paymentMode === 'QR Code' || $paymentMode === 'qr_code' || $paymentProofName) {
                $account = Account::where('is_default', 1)->first() ?? Account::first();
                Payment::create([
                    'payment_reference' => 'spr-' . date('Ymd-His'),
                    'user_id'           => $userId,
                    'sale_id'           => $sale->id,
                    'account_id'        => $account ? $account->id : 1,
                    'amount'            => $summary['grand_total'],
                    'change'            => 0,
                    'paying_method'     => 'QR Code',
                    'document'          => $paymentProofName,
                ]);
            }

            // 12. Clear the Cart for this User
            Cart::where('user_id', $userId)->delete();

            DB::commit();

            return $this->success([
                'order' => $this->formatOrderDetails($sale),
            ], 'Order placed successfully! Reference: ' . $referenceNo, 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->error('Failed to place order: ' . $e->getMessage(), 500);
        }
    }

    /**
     * List Authenticated User's Orders
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::guard('api')->user() ?? $request->user();
            if (!$user) {
                return $this->unauthorized('Please log in to view your orders');
            }

            $customer = Customer::where('user_id', $user->id)->first();
            $customerId = $customer ? $customer->id : 0;

            $query = Sale::where(function ($q) use ($user, $customerId) {
                $q->where('user_id', $user->id);
                if ($customerId > 0) {
                    $q->orWhere('customer_id', $customerId);
                }
            })
            ->with(['productSales.sale'])
            ->orderBy('id', 'desc');

            // Status Filter
            if ($request->filled('status')) {
                $status = strtolower($request->status);
                if ($status === 'pending') {
                    $query->where('sale_status', 2);
                } elseif ($status === 'completed') {
                    $query->where('sale_status', 1);
                } elseif ($status === 'cancelled') {
                    $query->where('sale_status', 4);
                }
            }

            $perPage = (int) $request->input('per_page', 15);
            $orders = $query->paginate($perPage);

            $formatted = collect($orders->items())->map(fn($o) => $this->formatOrderListItem($o));

            return $this->success([
                'orders'     => $formatted,
                'pagination' => [
                    'total'        => $orders->total(),
                    'per_page'     => $orders->perPage(),
                    'current_page' => $orders->currentPage(),
                    'last_page'    => $orders->lastPage(),
                ],
            ], 'Orders retrieved successfully');
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve orders: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Single Order Details by ID or Reference Number
     */
    public function show(Request $request, $referenceOrId)
    {
        try {
            $user = Auth::guard('api')->user() ?? $request->user();

            $sale = Sale::where(function ($q) use ($referenceOrId) {
                if (is_numeric($referenceOrId)) {
                    $q->where('id', $referenceOrId)->orWhere('reference_no', $referenceOrId);
                } else {
                    $q->where('reference_no', $referenceOrId);
                }
            })
            ->with(['productSales', 'payments', 'customer'])
            ->first();

            if (!$sale) {
                return $this->error('Order not found', 404);
            }

            // Security check: if user is logged in, ensure order belongs to them (unless admin)
            if ($user && $user->role_id != 1 && $sale->user_id != $user->id) {
                $customer = Customer::where('user_id', $user->id)->first();
                if (!$customer || $sale->customer_id != $customer->id) {
                    return $this->unauthorized('You do not have permission to view this order.');
                }
            }

            return $this->success([
                'order' => $this->formatOrderDetails($sale),
            ], 'Order details retrieved successfully');
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve order details: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Cancel an Order (if still Pending)
     */
    public function cancel(Request $request, $referenceOrId)
    {
        try {
            $user = Auth::guard('api')->user() ?? $request->user();
            if (!$user) {
                return $this->unauthorized('Please log in to cancel an order');
            }

            $sale = Sale::where(function ($q) use ($referenceOrId) {
                if (is_numeric($referenceOrId)) {
                    $q->where('id', $referenceOrId)->orWhere('reference_no', $referenceOrId);
                } else {
                    $q->where('reference_no', $referenceOrId);
                }
            })
            ->with('productSales')
            ->first();

            if (!$sale) {
                return $this->error('Order not found', 404);
            }

            if ($user->role_id != 1 && $sale->user_id != $user->id) {
                return $this->unauthorized('You do not have permission to cancel this order.');
            }

            if ($sale->sale_status != 2) {
                return $this->badRequest('Order cannot be cancelled because it is already ' . $this->saleStatusText($sale->sale_status));
            }

            DB::beginTransaction();

            // Set sale_status to 4 (Cancelled)
            $sale->sale_status = 4;
            $sale->save();

            // Restore Stock
            foreach ($sale->productSales as $ps) {
                $product = Product::find($ps->product_id);
                if ($product) {
                    $product->qty += $ps->qty;
                    $product->in_stock = 1;
                    $product->save();
                }

                $pw = Product_Warehouse::where('product_id', $ps->product_id)
                    ->where('warehouse_id', $sale->warehouse_id)
                    ->first();

                if ($pw) {
                    $pw->qty += $ps->qty;
                    $pw->save();
                }
            }

            DB::commit();

            return $this->success([
                'order' => $this->formatOrderDetails($sale),
            ], 'Order has been successfully cancelled and stock restored.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->error('Failed to cancel order: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Public Order Tracking Endpoint by Reference Number & Phone
     */
    public function track(Request $request, $referenceNo)
    {
        try {
            $ref = trim($referenceNo);

            $query = Sale::where('reference_no', $ref)
                ->with(['productSales']);

            if ($request->filled('phone')) {
                $phone = trim($request->phone);
                $query->where(function ($q) use ($phone) {
                    $q->where('shipping_phone', 'LIKE', "%{$phone}%")
                      ->orWhere('billing_phone', 'LIKE', "%{$phone}%");
                });
            }

            $sale = $query->first();

            if (!$sale) {
                return $this->error('No order found matching the provided reference number.', 404);
            }

            return $this->success([
                'reference_no'     => $sale->reference_no,
                'created_at'       => $sale->created_at ? $sale->created_at->toIso8601String() : null,
                'sale_status'      => $this->saleStatusText($sale->sale_status),
                'payment_status'   => $this->paymentStatusText($sale->payment_status),
                'payment_mode'     => $sale->payment_mode ?? 'Cash on Delivery',
                'grand_total'      => (float) $sale->grand_total,
                'shipping_name'    => $sale->shipping_name,
                'shipping_address' => $sale->shipping_address . ', ' . $sale->shipping_city,
                'items_count'      => (int) $sale->item,
                'total_qty'        => (float) $sale->total_qty,
            ], 'Order status retrieved successfully');
        } catch (\Throwable $e) {
            return $this->error('Failed to track order: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Format Order for List Views
     */
    protected function formatOrderListItem(Sale $sale): array
    {
        return [
            'id'             => $sale->id,
            'reference_no'   => $sale->reference_no,
            'sale_status'    => $this->saleStatusText($sale->sale_status),
            'sale_status_id' => (int) $sale->sale_status,
            'payment_status' => $this->paymentStatusText($sale->payment_status),
            'payment_mode'   => $sale->payment_mode ?? 'Cash on Delivery',
            'total_items'    => (int) $sale->item,
            'total_qty'      => (float) $sale->total_qty,
            'subtotal'       => (float) $sale->total_price,
            'coupon_discount'=> (float) ($sale->coupon_discount ?? 0),
            'shipping_cost'  => (float) ($sale->shipping_cost ?? 0),
            'grand_total'    => (float) $sale->grand_total,
            'created_at'     => $sale->created_at ? $sale->created_at->toIso8601String() : null,
        ];
    }

    /**
     * Format Comprehensive Order Details
     */
    protected function formatOrderDetails(Sale $sale): array
    {
        $base = $this->formatOrderListItem($sale);

        // Format items
        $productSales = Product_Sale::where('sale_id', $sale->id)->get();
        $items = $productSales->map(function ($ps) {
            $product = Product::find($ps->product_id);
            $images = $product ? $this->parseProductImages($product->image) : [];

            return [
                'id'             => $ps->id,
                'product_id'     => $ps->product_id,
                'name'           => $product->name ?? 'Product #' . $ps->product_id,
                'slug'           => $product->slug ?? '',
                'code'           => $product->code ?? '',
                'image_url'      => $images[0] ?? null,
                'qty'            => (float) $ps->qty,
                'unit_price'     => (float) $ps->net_unit_price,
                'discount'       => (float) $ps->discount,
                'total'          => (float) $ps->total,
                'variant_id'     => $ps->variant_id,
            ];
        });

        return array_merge($base, [
            'items'            => $items,
            'customer'         => [
                'name'  => $sale->shipping_name ?? $sale->billing_name,
                'phone' => $sale->shipping_phone ?? $sale->billing_phone,
                'email' => $sale->shipping_email ?? $sale->billing_email,
            ],
            'shipping_address' => [
                'name'    => $sale->shipping_name,
                'phone'   => $sale->shipping_phone,
                'email'   => $sale->shipping_email,
                'address' => $sale->shipping_address,
                'city'    => $sale->shipping_city,
                'state'   => $sale->shipping_state,
                'country' => $sale->shipping_country,
                'zip'     => $sale->shipping_zip,
            ],
            'billing_address'  => [
                'name'    => $sale->billing_name,
                'phone'   => $sale->billing_phone,
                'email'   => $sale->billing_email,
                'address' => $sale->billing_address,
                'city'    => $sale->billing_city,
                'state'   => $sale->billing_state,
                'country' => $sale->billing_country,
                'zip'     => $sale->billing_zip,
            ],
            'sale_note'        => $sale->sale_note,
        ]);
    }

    /**
     * Map sale_status integer to Human-Readable Label
     */
    protected function saleStatusText($status): string
    {
        return match ((int)$status) {
            1 => 'Completed',
            2 => 'Pending',
            3 => 'Draft',
            4 => 'Cancelled',
            default => 'Pending',
        };
    }

    /**
     * Map payment_status integer to Human-Readable Label
     */
    protected function paymentStatusText($status): string
    {
        return match ((int)$status) {
            1 => 'Pending',
            2 => 'Due',
            3 => 'Partial',
            4 => 'Paid',
            default => 'Pending',
        };
    }

    /**
     * Normalize Payment Mode String
     */
    protected function normalizePaymentMode(string $mode): string
    {
        $m = strtolower(str_replace(['_', '-'], ' ', $mode));

        if (str_contains($m, 'cod') || str_contains($m, 'cash on delivery')) {
            return 'Cash on Delivery';
        }
        if (str_contains($m, 'qr') || str_contains($m, 'bkash') || str_contains($m, 'nagad')) {
            return 'QR Code';
        }
        if (str_contains($m, 'stripe')) {
            return 'Stripe';
        }
        if (str_contains($m, 'sslcommerz') || str_contains($m, 'ssl')) {
            return 'SSLCommerz';
        }
        if (str_contains($m, 'whatsapp')) {
            return 'whatsapp_order';
        }

        return ucwords($mode);
    }

    /**
     * Helper to parse product images
     */
    protected function parseProductImages(?string $rawImages): array
    {
        if (empty($rawImages)) {
            return [];
        }

        $list = explode(',', $rawImages);
        $result = [];

        foreach ($list as $img) {
            $img = trim($img);
            if (empty($img)) continue;

            if (Str::startsWith($img, ['http://', 'https://'])) {
                $result[] = $img;
            } elseif (file_exists(public_path('images/product/' . $img))) {
                $result[] = asset('images/product/' . $img);
            } elseif (file_exists(public_path('frontend/images/product/' . $img))) {
                $result[] = asset('frontend/images/product/' . $img);
            } else {
                $result[] = asset('images/product/' . $img);
            }
        }

        return $result;
    }
}
