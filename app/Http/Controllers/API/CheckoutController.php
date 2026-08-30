<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\DeliveryArea;
use App\Models\Product;
use App\Models\Variant;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CheckoutController extends Controller
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
     * Resolve Authenticated User & Customer Record
     */
    protected function resolveUserContext(Request $request): array
    {
        $user = Auth::guard('api')->user() ?? $request->user();

        if (!$user) {
            $bearer = $request->bearerToken();
            if ($bearer && substr_count($bearer, '.') === 2) {
                [$header, $payload, $signature] = explode('.', $bearer);
                $decoded = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);
                if (isset($decoded['sub'])) {
                    $user = \App\Models\User::find($decoded['sub']);
                }
            }
        }

        if (!$user) {
            return ['user' => null, 'customer' => null];
        }

        $customer = Customer::where('user_id', $user->id)->first();
        if (!$customer) {
            $customer = Customer::firstOrCreate(
                ['email' => $user->email],
                [
                    'user_id'           => $user->id,
                    'customer_group_id' => 1,
                    'name'              => $user->name,
                    'phone_number'      => $user->phone ?? '0000000000',
                    'is_active'         => 1,
                ]
            );
        }

        return ['user' => $user, 'customer' => $customer];
    }

    /**
     * Initialize Checkout Data
     * Provides saved addresses, delivery areas, payment methods, and current cart
     */
    public function init(Request $request)
    {
        try {
            $ctx = $this->resolveUserContext($request);
            $user = $ctx['user'];
            $customer = $ctx['customer'];

            if (!$user) {
                return $this->unauthorized('Please log in or initialize guest session');
            }

            $couponCode = $request->input('coupon_code');

            // 1. Customer Addresses
            $addresses = [];
            $defaultAddress = null;
            if ($customer) {
                $addressList = CustomerAddress::where('customer_id', $customer->id)
                    ->orderBy('default', 'desc')
                    ->get();

                $addresses = $addressList->map(function ($addr) {
                    return [
                        'id'          => $addr->id,
                        'name'        => $addr->name,
                        'phone'       => $addr->phone,
                        'email'       => $addr->email,
                        'address'     => $addr->address,
                        'city'        => $addr->city,
                        'state'       => $addr->state,
                        'country'     => $addr->country,
                        'zip'         => $addr->zip,
                        'is_default'  => (bool) $addr->default,
                    ];
                });

                $def = $addressList->firstWhere('default', 1);
                $defaultAddress = $def ? [
                    'id'          => $def->id,
                    'name'        => $def->name,
                    'phone'       => $def->phone,
                    'email'       => $def->email,
                    'address'     => $def->address,
                    'city'        => $def->city,
                    'state'       => $def->state,
                    'country'     => $def->country,
                    'zip'         => $def->zip,
                ] : null;
            }

            // 2. Delivery Areas
            $deliveryAreas = DeliveryArea::active()->orderBy('name')->get()->map(function ($area) {
                return [
                    'id'              => $area->id,
                    'name'            => $area->name,
                    'city'            => $area->city,
                    'zone'            => $area->zone,
                    'delivery_charge' => (float) $area->delivery_charge,
                    'estimated_days'  => (int) $area->estimated_days,
                ];
            });

            // 3. Ecommerce Settings
            $ecomSetting = DB::table('ecommerce_settings')->first();
            $freeShippingFrom = $ecomSetting ? (float)$ecomSetting->free_shipping_from : 0.0;
            $flatRateShipping = $ecomSetting ? (float)$ecomSetting->flat_rate_shipping : 0.0;
            $isCodActive = $ecomSetting ? (bool)($ecomSetting->cash_on_delivery ?? true) : true;
            $isWhatsappActive = $ecomSetting ? (bool)($ecomSetting->active_whatsapp_order ?? false) : false;

            // 4. Payment Gateways
            $gateways = DB::table('external_services')
                ->where('type', 'payment')
                ->where('active', 1)
                ->get()
                ->map(function ($g) {
                    return [
                        'name' => $g->name,
                        'type' => $g->type,
                    ];
                });

            $paymentMethods = [];
            if ($isCodActive) {
                $paymentMethods[] = [
                    'code'        => 'cod',
                    'name'        => 'Cash on Delivery (COD)',
                    'description' => 'Pay cash when your order is delivered to your doorstep.',
                    'is_online'   => false,
                ];
            }

            // Check QR Code payment
            $qrCount = DB::table('qr_codes')->where('is_active', 1)->count();
            if ($qrCount > 0) {
                $paymentMethods[] = [
                    'code'        => 'qr_code',
                    'name'        => 'Mobile / QR Payment (bKash / Nagad / Bank)',
                    'description' => 'Pay via QR code or mobile wallet and upload screenshot.',
                    'is_online'   => false,
                    'requires_proof' => true,
                ];
            }

            foreach ($gateways as $gw) {
                $code = strtolower(str_replace(' ', '_', $gw['name']));
                $paymentMethods[] = [
                    'code'        => $code,
                    'name'        => $gw['name'],
                    'description' => 'Pay securely online using card or mobile banking.',
                    'is_online'   => true,
                ];
            }

            if ($isWhatsappActive) {
                $paymentMethods[] = [
                    'code'        => 'whatsapp_order',
                    'name'        => 'Order via WhatsApp',
                    'description' => 'Place your order and confirm details over WhatsApp.',
                    'is_online'   => false,
                ];
            }

            // 5. Live Cart Calculation
            $cartCtrl = app(CartController::class);
            $cartSummary = $cartCtrl->buildCartSummary($user->id, $couponCode);

            return $this->success([
                'user'             => [
                    'id'       => $user->id,
                    'name'     => $user->name,
                    'email'    => $user->email,
                    'phone'    => $user->phone,
                    'is_guest' => (bool)$user->is_guest,
                ],
                'addresses'        => $addresses,
                'default_address'  => $defaultAddress,
                'delivery_areas'   => $deliveryAreas,
                'payment_methods'  => $paymentMethods,
                'shipping_policy'  => [
                    'free_shipping_from' => $freeShippingFrom,
                    'flat_rate_shipping' => $flatRateShipping,
                ],
                'cart'             => $cartSummary,
            ], 'Checkout initialized successfully');
        } catch (\Throwable $e) {
            return $this->error('Failed to initialize checkout: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Calculate Complete Checkout Totals & Discounts Dynamically
     */
    public function calculate(Request $request)
    {
        $data = $this->getRequestData($request);
        $request->merge($data);

        $validator = Validator::make($request->all(), [
            'delivery_area_id'   => 'nullable|integer',
            'shipping_cost'      => 'nullable|numeric|min:0',
            'coupon_code'        => 'nullable|string',
            'items'              => 'nullable|array',
            'items.*.product_id' => 'required_with:items|integer|exists:products,id',
            'items.*.qty'        => 'required_with:items|numeric|min:1',
            'items.*.variant_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray(), 'Validation failed', 422);
        }

        try {
            $user = Auth::guard('api')->user() ?? $request->user();
            $userId = $user ? $user->id : 0;

            $couponCode = $request->filled('coupon_code') ? trim($request->coupon_code) : null;
            $deliveryAreaId = $request->input('delivery_area_id');
            $customShippingCost = $request->input('shipping_cost');

            $calculation = $this->computeTotals(
                $userId,
                $request->input('items'),
                $deliveryAreaId,
                $customShippingCost,
                $couponCode
            );

            return $this->success($calculation, 'Calculation completed successfully');
        } catch (\Throwable $e) {
            return $this->error('Calculation failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Core Calculation Logic for Checkout & Order Placement
     */
    public function computeTotals(
        int $userId,
        ?array $customItems = null,
        ?int $deliveryAreaId = null,
        $customShippingCost = null,
        ?string $couponCode = null
    ): array {
        $ecomSetting = DB::table('ecommerce_settings')->first();
        $freeShippingFrom = $ecomSetting ? (float)$ecomSetting->free_shipping_from : 0.0;
        $flatRateShipping = $ecomSetting ? (float)$ecomSetting->flat_rate_shipping : 0.0;

        $items = [];
        $totalQty = 0;
        $subtotal = 0.0;
        $originalSubtotal = 0.0;
        $productSavings = 0.0;

        // 1. Build Item List (from payload or Cart table)
        if ($customItems !== null && !empty($customItems)) {
            foreach ($customItems as $cItem) {
                $pId = (int) $cItem['product_id'];
                $vId = isset($cItem['variant_id']) && (int)$cItem['variant_id'] > 0 ? (int)$cItem['variant_id'] : null;
                $qty = (float) ($cItem['qty'] ?? 1);

                $product = Product::with(['unit', 'category', 'brand'])->where('id', $pId)->where('is_active', 1)->first();
                if (!$product) continue;

                $regularPrice = (float) $product->price;
                $isPromo = (bool) $product->promotion && $product->promotion_price > 0;
                $unitPrice = $isPromo ? (float)$product->promotion_price : $regularPrice;

                $variantInfo = null;
                if ($vId && $product->is_variant) {
                    $pv = DB::table('product_variants')->where('product_id', $product->id)->where('variant_id', $vId)->first();
                    $variantObj = Variant::find($vId);
                    if ($pv && $variantObj) {
                        $addPrice = (float) ($pv->additional_price ?? 0);
                        $unitPrice += $addPrice;
                        $regularPrice += $addPrice;
                        $variantInfo = [
                            'id'               => $variantObj->id,
                            'name'             => $variantObj->name,
                            'item_code'        => $pv->item_code ?? null,
                            'additional_price' => $addPrice,
                        ];
                    }
                }

                $itemTotal = $unitPrice * $qty;
                $itemOriginalTotal = $regularPrice * $qty;
                $itemSavings = max(0, $itemOriginalTotal - $itemTotal);

                $items[] = [
                    'product_id'    => $product->id,
                    'name'          => $product->name,
                    'slug'          => $product->slug,
                    'code'          => $product->code,
                    'unit'          => $product->unit ? $product->unit->unit_name : null,
                    'sale_unit_id'  => $product->sale_unit_id ?? 1,
                    'qty'           => $qty,
                    'regular_price' => $regularPrice,
                    'unit_price'    => $unitPrice,
                    'is_promotion'  => $isPromo,
                    'total_price'   => round($itemTotal, 2),
                    'savings'       => round($itemSavings, 2),
                    'variant'       => $variantInfo,
                    'in_stock'      => (bool) ($product->in_stock ?? ($product->qty > 0)),
                    'available_qty' => (float) $product->qty,
                ];

                $totalQty += $qty;
                $subtotal += $itemTotal;
                $originalSubtotal += $itemOriginalTotal;
                $productSavings += $itemSavings;
            }
        } elseif ($userId > 0) {
            // Read from DB Cart
            $cartCtrl = app(CartController::class);
            $cartSummary = $cartCtrl->buildCartSummary($userId, null);

            $items = $cartSummary['items'];
            $totalQty = $cartSummary['summary']['total_qty'];
            $subtotal = $cartSummary['summary']['subtotal'];
            $originalSubtotal = $cartSummary['summary']['original_subtotal'];
            $productSavings = $cartSummary['summary']['product_savings'];
        }

        // 2. Shipping Calculation
        $isFreeShipping = $freeShippingFrom > 0 && $subtotal >= $freeShippingFrom;
        $shippingCost = 0.0;
        $deliveryAreaInfo = null;

        if ($isFreeShipping) {
            $shippingCost = 0.0;
        } elseif ($deliveryAreaId) {
            $area = DeliveryArea::active()->where('id', $deliveryAreaId)->first();
            if ($area) {
                $shippingCost = (float) $area->delivery_charge;
                $deliveryAreaInfo = [
                    'id'              => $area->id,
                    'name'            => $area->name,
                    'delivery_charge' => (float) $area->delivery_charge,
                    'estimated_days'  => (int) $area->estimated_days,
                ];
            } else {
                $shippingCost = $flatRateShipping;
            }
        } elseif ($customShippingCost !== null) {
            $shippingCost = (float) $customShippingCost;
        } else {
            $shippingCost = $flatRateShipping;
        }

        // 3. Coupon Discount Calculation
        $couponDiscount = 0.0;
        $appliedCoupon = null;
        $couponMessage = null;

        if ($couponCode && $subtotal > 0) {
            $coupon = Coupon::where('code', $couponCode)->first();
            if ($coupon) {
                if ($coupon->isValidForSubtotal($subtotal, $errorMsg)) {
                    $couponDiscount = $coupon->calculateDiscount($subtotal);
                    $appliedCoupon = [
                        'id'          => $coupon->id,
                        'code'        => $coupon->code,
                        'type'        => $coupon->type,
                        'amount'      => (float) $coupon->amount,
                        'discount'    => round($couponDiscount, 2),
                    ];
                } else {
                    $couponMessage = $errorMsg;
                }
            } else {
                $couponMessage = 'Coupon not found.';
            }
        }

        // 4. Tax Calculation (if applicable)
        $taxAmount = 0.0;

        // 5. Grand Total Calculation
        $grandTotal = max(0, round(($subtotal - $couponDiscount) + $shippingCost + $taxAmount, 2));
        $minNeededForFreeShipping = max(0, $freeShippingFrom - $subtotal);

        return [
            'items'                     => $items,
            'summary'                   => [
                'total_items'               => count($items),
                'total_qty'                 => $totalQty,
                'original_subtotal'         => round($originalSubtotal, 2),
                'subtotal'                  => round($subtotal, 2),
                'product_savings'           => round($productSavings, 2),
                'coupon_discount'           => round($couponDiscount, 2),
                'shipping_cost'             => round($shippingCost, 2),
                'tax'                       => round($taxAmount, 2),
                'grand_total'               => $grandTotal,
                'is_free_shipping_eligible' => $isFreeShipping,
                'free_shipping_threshold'   => $freeShippingFrom,
                'min_needed_for_free_ship'  => round($minNeededForFreeShipping, 2),
            ],
            'delivery_area'             => $deliveryAreaInfo,
            'applied_coupon'            => $appliedCoupon,
            'coupon_warning'            => $couponMessage,
        ];
    }
}
