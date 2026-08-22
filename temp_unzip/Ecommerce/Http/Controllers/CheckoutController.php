<?php

namespace Modules\Ecommerce\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Coupon;
use App\Models\GiftCard;
use DB;
use Modules\Ecommerce\Entities\DeliveryArea;

class CheckoutController extends Controller
{
	public function index(Request $request)
	{
		$cart = session()->has('cart') ? session()->get('cart') : [];
		$total_qty = session()->has('total_qty') ? session()->get('total_qty') : 0;
		$subTotal = session()->has('subTotal') ? session()->get('subTotal') : 0;
        $delivery_areas = DeliveryArea::active()->orderBy('name')->get();

		if(cache()->has('ecommerce_setting')){
			$settings = cache('ecommerce_setting');
			if(isset($settings->checkout_pages)){
				$pages = DB::table('pages')->select('id','page_name','slug')->whereIn('id',json_decode($settings->checkout_pages))->get();
			}else{
				$pages = NULL;
			}
		} else {
		    $pages = NULL;
		}

		$gateways = DB::table('external_services')->where('type','payment')->where('active',1)->get();
		foreach ($gateways as $gateway) {
			$gateway->module_status = json_decode($gateway->module_status);
		}

		if(auth()->user() && auth()->user()->role_id == 5) {
			$customer = DB::table('customers')->where('user_id',auth()->user()->id)->first();
			$addresses = DB::table('customer_addresses')->where('customer_id',$customer->id)->get();
			$def_address = $addresses->where('default',1)->first();

			return view('ecommerce::frontend.checkout', compact('cart', 'total_qty', 'subTotal', 'customer', 'addresses', 'def_address', 'pages','gateways','delivery_areas'));
		}

		return view('ecommerce::frontend.checkout', compact('cart', 'total_qty', 'subTotal', 'pages','gateways','delivery_areas'));
	}

	public function applyCoupon(Request $request)
	{
		$code = $request->input('coupon_code');
		$coupons = Coupon::where('code', $code)->where('expired_date', '>=', date('Y-m-d'))->where('is_active', 1)->first();

		if($coupons) {
			return response()->json(['status' => 'success', 'coupon_id' => $coupons->id, 'coupon_type' => $coupons->type, 'discount_amount' => $coupons->amount, 'message'=>'Discount applied']);
		} else {
			return response()->json(['status' => 'error', 'message'=>'Discount applied']);
		}

	}

	public function applyGiftCard(Request $request)
	{
		$card_no = $request->input('gift_card_no');
		$gift_card = GiftCard::where('card_no', $card_no)->where('expired_date', '>', date('Y-m-d'))->where('is_active', 1)->first();

		if($gift_card) {
			return response()->json(['status' => 'success', 'gift_card_id' => $gift_card->id, 'gift_card_amount' => $gift_card->amount, 'message'=>'Discount applied']);
		} else {
			return response()->json(['status' => 'error', 'message'=>'Gift card number doesn\'t exist or is expired !']);
		}

	}
}
