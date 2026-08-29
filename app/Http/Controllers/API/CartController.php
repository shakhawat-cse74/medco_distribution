<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Variant;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CartController extends Controller
{
    use ApiResponse;

    /**
     * Resolve Authenticated User (Supports Regular & Guest-Login Users)
     */
    protected function resolveUser(Request $request)
    {
        return Auth::guard('api')->user() ?? $request->user();
    }

    /**
     * Get Current Cart with Line Items and Pricing Breakdown
     */
    public function index(Request $request)
    {
        try {
            $user = $this->resolveUser($request);
            if (!$user) {
                return $this->unauthorized('Please log in or initialize guest session');
            }

            $couponCode = $request->input('coupon_code');
            $cartData = $this->buildCartSummary($user->id, $couponCode);

            return $this->success($cartData, 'Cart retrieved successfully');
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve cart: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Parse and normalize input from Request (JSON, Form-Data, Query, Raw Body)
     */
    protected function getRequestData(Request $request): array
    {
        $data = [];

        // 1. Check Laravel's parsed inputs
        $all = $request->all();
        if (is_array($all) && !empty($all)) {
            $data = array_merge($data, $all);
        }

        // 2. Check native PHP superglobals
        if (!empty($_POST) && is_array($_POST)) {
            $data = array_merge($data, $_POST);
        }
        if (!empty($_GET) && is_array($_GET)) {
            $data = array_merge($data, $_GET);
        }
        if (!empty($_REQUEST) && is_array($_REQUEST)) {
            $data = array_merge($data, $_REQUEST);
        }

        // 3. Check JSON bag
        $json = $request->json() ? $request->json()->all() : [];
        if (is_array($json) && !empty($json)) {
            $data = array_merge($data, $json);
        }

        // 4. Check raw body stream
        $raw = $request->getContent();
        if (empty($raw)) {
            $raw = @file_get_contents('php://input');
        }

        if (!empty($raw) && is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && !empty($decoded)) {
                $data = array_merge($data, $decoded);
            } elseif (str_contains($raw, 'name=')) {
                // Universal Multipart Boundary Parser
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

        // 5. Query parameters
        $query = $request->query();
        if (is_array($query) && !empty($query)) {
            $data = array_merge($data, $query);
        }

        return $data;
    }

    /**
     * Add Item to Cart
     */
    public function addToCart(Request $request)
    {
        $data = $this->getRequestData($request);
        $cleanData = [];
        foreach ($data as $k => $v) {
            $cleanKey = strtolower(trim($k));
            if (in_array($cleanKey, ['product_id', 'product', 'id', 'productid', 'item_id'])) {
                $cleanData['product_id'] = is_numeric($v) ? (int)$v : trim($v);
            } elseif (in_array($cleanKey, ['qty', 'quantity', 'product_qty', 'count', 'amount'])) {
                $cleanData['qty'] = (float)$v;
            } elseif (in_array($cleanKey, ['variant_id', 'variant', 'variantid'])) {
                $cleanData['variant_id'] = is_numeric($v) ? (int)$v : trim($v);
            }
        }
        $request->merge($cleanData);

        if (!$request->has('qty')) {
            $request->merge(['qty' => 1]);
        }

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
            'qty'        => 'nullable|numeric|min:1',
            'variant_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray(), 'Validation failed', 422);
        }

        try {
            $user = $this->resolveUser($request);
            if (!$user) {
                return $this->unauthorized('Please log in or initialize guest session');
            }

            $productId = (int) $request->product_id;
            $qty = (float) ($request->qty ?? 1);
            $variantId = $request->filled('variant_id') && (int)$request->variant_id > 0 ? (int)$request->variant_id : null;

            $product = Product::where('id', $productId)->where('is_active', 1)->first();
            if (!$product) {
                return $this->error('Product not found or unavailable', 404);
            }

            // Check stock if sell_without_stock is disabled
            $ecommerceSetting = DB::table('ecommerce_settings')->first();
            $sellWithoutStock = $ecommerceSetting ? (bool)$ecommerceSetting->sell_without_stock : false;

            // Find existing item in cart
            $existing = Cart::where('user_id', $user->id)
                ->where('product_id', $productId)
                ->where(function ($q) use ($variantId) {
                    if ($variantId) {
                        $q->where('variant_id', $variantId);
                    } else {
                        $q->whereNull('variant_id')->orWhere('variant_id', 0);
                    }
                })
                ->first();

            $newQty = $existing ? ($existing->qty + $qty) : $qty;

            if (!$sellWithoutStock && (float)$product->qty < $newQty && (float)$product->qty > 0) {
                return $this->badRequest('Cannot add more than available stock (' . (float)$product->qty . ' available)');
            }

            if ($existing) {
                $existing->qty = $newQty;
                $existing->save();
                $cartItem = $existing;
            } else {
                $cartItem = Cart::create([
                    'user_id'    => $user->id,
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'qty'        => $qty,
                ]);
            }

            $cartSummary = $this->buildCartSummary($user->id);

            return $this->success(
                array_merge([
                    'item' => $this->formatCartItem($cartItem),
                ], $cartSummary),
                'Item added to cart successfully',
                201
            );
        } catch (\Throwable $e) {
            return $this->error('Failed to add item to cart: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update Cart Item Quantity by product_id & qty
     */
    public function updateCart(Request $request)
    {
        $data = $this->getRequestData($request);
        $cleanData = [];
        foreach ($data as $k => $v) {
            $cleanKey = strtolower(trim($k));
            if (in_array($cleanKey, ['product_id', 'product', 'id', 'productid', 'item_id'])) {
                $cleanData['product_id'] = is_numeric($v) ? (int)$v : trim($v);
            } elseif (in_array($cleanKey, ['qty', 'quantity', 'product_qty', 'count', 'amount'])) {
                $cleanData['qty'] = (float)$v;
            } elseif (in_array($cleanKey, ['variant_id', 'variant', 'variantid'])) {
                $cleanData['variant_id'] = is_numeric($v) ? (int)$v : trim($v);
            } elseif (in_array($cleanKey, ['cart_id', 'cartid'])) {
                $cleanData['cart_id'] = is_numeric($v) ? (int)$v : trim($v);
            }
        }
        $request->merge($cleanData);

        $validator = Validator::make($request->all(), [
            'product_id' => 'required_without:cart_id|integer',
            'cart_id'    => 'nullable|integer',
            'variant_id' => 'nullable|integer',
            'qty'        => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray(), 'Validation failed', 422);
        }

        try {
            $user = $this->resolveUser($request);
            if (!$user) {
                return $this->unauthorized('Please log in or initialize guest session');
            }

            $productId = $request->input('product_id');
            $cartId = $request->input('cart_id');
            $variantId = $request->input('variant_id');
            $qty = (float) $request->qty;

            $query = Cart::where('user_id', $user->id);

            if ($productId) {
                $cartItem = $query->where('product_id', $productId)
                    ->where(function ($q) use ($variantId) {
                        if ($variantId) {
                            $q->where('variant_id', $variantId);
                        } else {
                            $q->whereNull('variant_id')->orWhere('variant_id', 0);
                        }
                    })
                    ->first();
            } elseif ($cartId) {
                $cartItem = $query->where('id', $cartId)->first();
            } else {
                return $this->badRequest('Please provide product_id and qty in request body');
            }

            if (!$cartItem) {
                return $this->error('Item not found in your cart', 404);
            }

            if ($qty <= 0) {
                $cartItem->delete();
                $message = 'Item removed from cart';
            } else {
                // Check stock
                $product = Product::find($cartItem->product_id);
                $ecommerceSetting = DB::table('ecommerce_settings')->first();
                $sellWithoutStock = $ecommerceSetting ? (bool)$ecommerceSetting->sell_without_stock : false;

                if ($product && !$sellWithoutStock && (float)$product->qty < $qty) {
                    return $this->badRequest('Only ' . (float)$product->qty . ' items available in stock');
                }

                $cartItem->qty = $qty;
                $cartItem->save();
                $message = 'Cart updated successfully';
            }

            $cartSummary = $this->buildCartSummary($user->id);

            return $this->success($cartSummary, $message);
        } catch (\Throwable $e) {
            return $this->error('Failed to update cart: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove an Item from Cart
     */
    public function removeFromCart(Request $request, $id = null)
    {
        try {
            $user = $this->resolveUser($request);
            if (!$user) {
                return $this->unauthorized('Please log in or initialize guest session');
            }

            $data = $this->getRequestData($request);

            // 1. Case-insensitive & trimmed key resolution
            $productId = null;
            foreach ($data as $k => $v) {
                $cleanKey = strtolower(trim($k));
                if (in_array($cleanKey, ['product_id', 'product', 'id', 'productid', 'cart_id', 'cartid', 'item_id', 'itemid'])) {
                    $productId = is_numeric($v) ? (int)$v : trim($v);
                    break;
                }
            }

            // 2. Route or Query parameter fallback
            if (!$productId) {
                $productId = $id 
                    ?? $request->route('id') 
                    ?? $request->query('product_id') 
                    ?? $request->query('product') 
                    ?? $request->query('id') 
                    ?? $request->query('cart_id');
            }

            // 3. If only a single numeric value exists in payload, accept it as product_id
            if (!$productId && !empty($data)) {
                foreach ($data as $val) {
                    if (is_numeric($val) && (int)$val > 0) {
                        $productId = (int)$val;
                        break;
                    }
                }
            }

            if (!$productId) {
                return $this->badRequest('Please provide product_id in request body (e.g. {"product_id": 1})');
            }

            // 4. Delete matching item
            $cartQuery = Cart::where('user_id', $user->id);
            $item = (clone $cartQuery)->where('product_id', $productId)->first();
            if (!$item) {
                $item = (clone $cartQuery)->where('id', $productId)->first();
            }

            if ($item) {
                $item->delete();
            }

            $cartSummary = $this->buildCartSummary($user->id);

            return $this->success($cartSummary, 'Item removed from cart');
        } catch (\Throwable $e) {
            return $this->error('Failed to remove item: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Clear all items in Cart
     */
    public function clearCart(Request $request)
    {
        try {
            $user = $this->resolveUser($request);
            if (!$user) {
                return $this->unauthorized('Please log in or initialize guest session');
            }

            Cart::where('user_id', $user->id)->delete();

            $cartSummary = $this->buildCartSummary($user->id);

            return $this->success($cartSummary, 'Cart cleared successfully');
        } catch (\Throwable $e) {
            return $this->error('Failed to clear cart: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Sync Offline items array into Cart
     */
    public function syncCart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items'              => 'required|array',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.qty'        => 'required|numeric|min:1',
            'items.*.variant_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray(), 'Validation failed', 422);
        }

        try {
            $user = $this->resolveUser($request);
            if (!$user) {
                return $this->unauthorized('Please log in or initialize guest session');
            }

            DB::beginTransaction();

            foreach ($request->items as $item) {
                $pId = (int) $item['product_id'];
                $vId = isset($item['variant_id']) && (int)$item['variant_id'] > 0 ? (int)$item['variant_id'] : null;
                $qty = (float) ($item['qty'] ?? 1);

                $existing = Cart::where('user_id', $user->id)
                    ->where('product_id', $pId)
                    ->where('variant_id', $vId)
                    ->first();

                if ($existing) {
                    $existing->qty += $qty;
                    $existing->save();
                } else {
                    Cart::create([
                        'user_id'    => $user->id,
                        'product_id' => $pId,
                        'variant_id' => $vId,
                        'qty'        => $qty,
                    ]);
                }
            }

            DB::commit();

            $cartSummary = $this->buildCartSummary($user->id);

            return $this->success($cartSummary, 'Cart synchronized successfully');
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->error('Failed to sync cart: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Apply Coupon to Cart & Return Calculated Discount
     */
    public function applyCoupon(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'coupon_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray(), 'Validation failed', 422);
        }

        try {
            $user = $this->resolveUser($request);
            if (!$user) {
                return $this->unauthorized('Please log in or initialize guest session');
            }

            $code = trim($request->coupon_code);

            $coupon = Coupon::where('code', $code)->first();
            if (!$coupon) {
                return $this->badRequest('Invalid coupon code.');
            }

            $cartSummary = $this->buildCartSummary($user->id, $code);

            if (empty($cartSummary['items'])) {
                return $this->badRequest('Your cart is empty. Add products to apply coupon.');
            }

            if (!$coupon->isValidForSubtotal($cartSummary['summary']['subtotal'], $errorMsg)) {
                return $this->badRequest($errorMsg ?? 'Coupon is not applicable.');
            }

            return $this->success($cartSummary, 'Coupon applied successfully');
        } catch (\Throwable $e) {
            return $this->error('Failed to apply coupon: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove Coupon from Cart
     */
    public function removeCoupon(Request $request)
    {
        try {
            $user = $this->resolveUser($request);
            if (!$user) {
                return $this->unauthorized('Please log in or initialize guest session');
            }

            $cartSummary = $this->buildCartSummary($user->id, null);

            return $this->success($cartSummary, 'Coupon removed');
        } catch (\Throwable $e) {
            return $this->error('Failed to remove coupon: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Build Cart Summary with Line Items, Pricing, and Totals
     */
    public function buildCartSummary(int $userId, ?string $couponCode = null): array
    {
        $cartRows = Cart::where('user_id', $userId)
            ->with(['product.unit', 'product.brand', 'product.category', 'variant'])
            ->get();

        $items = [];
        $totalQty = 0;
        $subtotal = 0.0;
        $originalSubtotal = 0.0;
        $totalSavings = 0.0;

        foreach ($cartRows as $row) {
            $product = $row->product;
            if (!$product || !$product->is_active) {
                continue;
            }

            $qty = (float) $row->qty;
            $regularPrice = (float) $product->price;
            $isPromo = (bool) $product->promotion && $product->promotion_price > 0;
            $unitPrice = $isPromo ? (float)$product->promotion_price : $regularPrice;

            $variantInfo = null;
            if ($row->variant_id && $product->is_variant) {
                $pv = DB::table('product_variants')
                    ->where('product_id', $product->id)
                    ->where('variant_id', $row->variant_id)
                    ->first();

                $variantObj = $row->variant ?? Variant::find($row->variant_id);
                if ($pv && $variantObj) {
                    $additionalPrice = (float) ($pv->additional_price ?? 0);
                    $unitPrice += $additionalPrice;
                    $regularPrice += $additionalPrice;
                    $variantInfo = [
                        'id'               => $variantObj->id,
                        'name'             => $variantObj->name,
                        'item_code'        => $pv->item_code ?? null,
                        'additional_price' => $additionalPrice,
                    ];
                }
            }

            $itemTotal = $unitPrice * $qty;
            $itemOriginalTotal = $regularPrice * $qty;
            $itemSavings = max(0, $itemOriginalTotal - $itemTotal);

            $images = $this->parseImages($product->image);

            $items[] = [
                'cart_id'           => $row->id,
                'product_id'        => $product->id,
                'name'              => $product->name,
                'slug'              => $product->slug,
                'code'              => $product->code,
                'image'             => $images[0] ?? null,
                'images'            => $images,
                'unit'              => $product->unit ? $product->unit->unit_name : null,
                'qty'               => $qty,
                'regular_price'     => $regularPrice,
                'unit_price'        => $unitPrice,
                'is_promotion'      => $isPromo,
                'total_price'       => round($itemTotal, 2),
                'savings'           => round($itemSavings, 2),
                'variant'           => $variantInfo,
                'in_stock'          => (bool) ($product->in_stock ?? ($product->qty > 0)),
                'available_qty'     => (float) $product->qty,
                'is_available'      => (float) $product->qty >= $qty,
                'category'          => $product->category ? [
                    'id'   => $product->category->id,
                    'name' => $product->category->name,
                ] : null,
            ];

            $totalQty += $qty;
            $subtotal += $itemTotal;
            $originalSubtotal += $itemOriginalTotal;
            $totalSavings += $itemSavings;
        }

        // Coupon calculation
        $couponDiscount = 0.0;
        $couponData = null;

        if ($couponCode && $subtotal > 0) {
            $coupon = Coupon::where('code', $couponCode)->first();
            if ($coupon && $coupon->isValidForSubtotal($subtotal)) {
                $couponDiscount = $coupon->calculateDiscount($subtotal);
                $couponData = [
                    'id'          => $coupon->id,
                    'code'        => $coupon->code,
                    'type'        => $coupon->type,
                    'amount'      => (float) $coupon->amount,
                    'discount'    => round($couponDiscount, 2),
                ];
            }
        }

        // Store / Ecommerce Settings
        $ecomSetting = DB::table('ecommerce_settings')->first();
        $freeShippingThreshold = $ecomSetting ? (float)$ecomSetting->free_shipping_from : 0.0;
        $flatShipping = $ecomSetting ? (float)$ecomSetting->flat_rate_shipping : 0.0;

        $isFreeShippingEligible = $freeShippingThreshold > 0 && $subtotal >= $freeShippingThreshold;
        $minNeededForFreeShipping = max(0, $freeShippingThreshold - $subtotal);

        $grandTotal = max(0, $subtotal - $couponDiscount);

        return [
            'items'                     => $items,
            'summary'                   => [
                'total_items'               => count($items),
                'total_qty'                 => $totalQty,
                'original_subtotal'         => round($originalSubtotal, 2),
                'subtotal'                  => round($subtotal, 2),
                'product_savings'           => round($totalSavings, 2),
                'coupon_discount'           => round($couponDiscount, 2),
                'grand_total'               => round($grandTotal, 2),
                'free_shipping_threshold'   => $freeShippingThreshold,
                'is_free_shipping_eligible' => $isFreeShippingEligible,
                'min_needed_for_free_ship'  => round($minNeededForFreeShipping, 2),
                'default_shipping_cost'     => $isFreeShippingEligible ? 0.0 : $flatShipping,
            ],
            'applied_coupon'            => $couponData,
        ];
    }

    /**
     * Format Single Cart Item
     */
    protected function formatCartItem(Cart $cartItem): array
    {
        $product = $cartItem->product ?? Product::find($cartItem->product_id);
        $images = $product ? $this->parseImages($product->image) : [];

        $unitPrice = (float) ($product->promotion && $product->promotion_price > 0 ? $product->promotion_price : ($product->price ?? 0));

        return [
            'cart_id'     => $cartItem->id,
            'product_id'  => $cartItem->product_id,
            'name'        => $product->name ?? '',
            'slug'        => $product->slug ?? '',
            'qty'         => (float) $cartItem->qty,
            'unit_price'  => $unitPrice,
            'total_price' => round($unitPrice * (float) $cartItem->qty, 2),
            'image_url'   => $images[0] ?? null,
            'variant_id'  => $cartItem->variant_id,
        ];
    }

    /**
     * Helper to parse product images
     */
    protected function parseImages(?string $rawImages): array
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
