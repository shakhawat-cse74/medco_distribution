<?php

namespace Modules\Ecommerce\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\Product_Sale;
use App\Models\Product;
use App\Models\Product_Warehouse;
use App\Models\Payment;
use App\Models\PaymentWithCreditCard;
use App\Models\PaymentWithGiftCard;
use App\Models\PaymentWithPaypal;
use App\Models\Account;
use App\Models\Currency;
use App\Models\ExternalService;
use App\Models\GiftCard;
use App\Models\PosSetting;
use App\Models\SmsTemplate;
use App\ViewModels\ISmsModel;
use Stripe\Stripe;
use Xendit\Xendit;
use Xendit\Configuration;
use Xendit\Invoice\CreateInvoiceRequest;
use Xendit\Invoice\InvoiceApi;
use Xendit\Invoice\InvoiceItem;
use Razorpay\Api\Api;
use DB;
use Session;
use Illuminate\Support\Facades\Auth;
use App\Models\MailSetting;
use Mail;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;

class OrdersController extends Controller
{
    private $_smsModel;

    public function __construct(ISmsModel $smsModel)
    {
        $this->middleware('checkSmsBalance')->only('store');
        $this->_smsModel = $smsModel;
    }

    public function create(Request $request)
    {

        if(auth()->user() && auth()->user()->role_id != 5) {
            auth()->logout();
        }
        $post_data = array();
        $post_data['reference_no'] = uniqid(); // reference_no must be unique
        $post_data['currency'] = $request->input('currency');
        $currency = Currency::where('id', $request->input('currency'))->first();

        # BILLING INFORMATION
        $post_data['billing_name'] = trim(htmlspecialchars($request->input('billing_name')));
        $post_data['billing_email'] = trim(htmlspecialchars($request->input('billing_email')));
        $post_data['billing_phone'] = trim(htmlspecialchars($request->input('billing_phone')));
        $post_data['billing_address'] = trim(htmlspecialchars($request->input('billing_address')));
        $post_data['billing_city'] = trim(htmlspecialchars($request->input('billing_city')));
        $post_data['billing_state'] = trim(htmlspecialchars($request->input('billing_state')));
        $post_data['billing_zip'] = trim(htmlspecialchars($request->input('billing_zip')));
        $post_data['billing_country'] = trim(htmlspecialchars($request->input('billing_country')));

        # SHIPMENT INFORMATION
        $post_data['shipping_name'] = trim(htmlspecialchars($request->input('shipping_name')));
        $post_data['shipping_email'] = trim(htmlspecialchars($request->input('shipping_email')));
        $post_data['shipping_phone'] = trim(htmlspecialchars($request->input('shipping_phone')));
        $post_data['shipping_address'] = trim(htmlspecialchars($request->input('shipping_address')));
        $post_data['shipping_city'] = trim(htmlspecialchars($request->input('shipping_city')));
        $post_data['shipping_state'] = trim(htmlspecialchars($request->input('shipping_state')));
        $post_data['shipping_zip'] = trim(htmlspecialchars($request->input('shipping_zip')));
        $post_data['shipping_country'] = trim(htmlspecialchars($request->input('shipping_country')));

        $post_data['sale_note'] = trim(htmlspecialchars($request->input('sale_note')));

        $post_data['warehouse_id'] = $request->input('warehouse_id');
        $post_data['biller_id'] = $request->input('biller_id');

        $post_data['payment_mode'] = trim(htmlspecialchars($request->input('payment_mode')));

        if ($post_data['payment_mode'] == 'Stripe') {
            $post_data['stripe_token'] = $request->input('stripeToken');
        }

        if (auth()->user()) {
            $post_data['user_id'] = auth()->user()->id;
            $customer = Customer::select('id')->where('user_id', $post_data['user_id'])->first();

            $post_data['customer_id'] = $customer->id;
        } else {
            if(isset($request->register_guest) && ($request->register_guest == 0)){
                $user = User::firstOrCreate(
                    ['email' =>  'guest@customer.com'],
                    ['name' => 'Guest', 'password' => '12345678', 'phone' => '12345678', 'role_id' => 5, 'is_active' => 1, 'is_deleted' => 0]
                );

                $customer = Customer::firstOrCreate(
                    ['email' =>  $post_data['shipping_email']],
                    ['name' => $post_data['shipping_name'], 'phone_number' => $post_data['shipping_phone'], 'address' => $post_data['shipping_address'], 'city' => $post_data['shipping_city'], 'customer_group_id' => 1, 'is_active' => 1]
                );
            }else{
                $user = User::firstOrCreate(
                    ['email' =>  $post_data['billing_email']],
                    ['name' => $post_data['billing_name'], 'password' => trim(bcrypt($request->input('password'))), 'phone' => $post_data['billing_phone'], 'role_id' => 5, 'is_active' => 1, 'is_deleted' => 0]
                );

                $customer = Customer::firstOrCreate(
                    ['email' =>  $post_data['billing_email']],
                    ['name' => $post_data['billing_name'], 'phone_number' => $post_data['billing_phone'], 'address' => $post_data['billing_address'], 'city' => $post_data['billing_city'], 'customer_group_id' => 1, 'is_active' => 1]
                );
            }

            $post_data['user_id'] = $user->id;
            $post_data['customer_id'] = $customer->id; // walk-in-customer / guest customer
        }

        # OPTIONAL PARAMETERS
        $post_data['item'] = trim(htmlspecialchars($request->input('item')));
        $post_data['total_qty'] = trim(htmlspecialchars($request->input('total_qty')));
        $post_data['total_discount'] = 0;
        $post_data['total_tax'] = 0;
        $post_data['sub_total'] = trim(htmlspecialchars($request->input('sub_total')));
        $post_data['grand_total'] = trim(htmlspecialchars($request->input('grand_total')));

        if (!$request->input('coupon_id')) {
            $post_data['coupon_id'] = NULL;
            $post_data['coupon_discount'] = NULL;
        } else {
            $post_data['coupon_id'] = trim(htmlspecialchars($request->input('coupon_id')));
            $post_data['coupon_discount'] = trim(htmlspecialchars($request->input('coupon_discount')));
        }

        $post_data['shipping_cost'] = trim(htmlspecialchars($request->input('shipping_cost')));

        #Before  going to initiate the payment order status need to update as Pending.
        $create_sale = Sale::create([
            'reference_no'         => $post_data['reference_no'],
            'user_id'              => $post_data['user_id'],
            'customer_id'          => $post_data['customer_id'],
            'billing_name'         => $post_data['billing_name'],
            'billing_email'        => $post_data['billing_email'],
            'billing_phone'        => $post_data['billing_phone'],
            'billing_address'      => $post_data['billing_address'],
            'billing_city'         => $post_data['billing_city'],
            'billing_state'        => $post_data['billing_state'],
            'billing_zip'          => $post_data['billing_zip'],
            'billing_country'      => $post_data['billing_country'],
            'shipping_name'        => $post_data['shipping_name'],
            'shipping_email'       => $post_data['shipping_email'],
            'shipping_phone'       => $post_data['shipping_phone'],
            'shipping_address'     => $post_data['shipping_address'],
            'shipping_city'        => $post_data['shipping_city'],
            'shipping_state'       => $post_data['shipping_state'],
            'shipping_zip'         => $post_data['shipping_zip'],
            'shipping_country'     => $post_data['shipping_country'],
            'warehouse_id'         => $post_data['warehouse_id'],
            'biller_id'            => $post_data['biller_id'],
            'item'                 => $post_data['item'],
            'total_qty'            => $post_data['total_qty'],
            'total_discount'       => $post_data['total_discount'],
            'total_tax'            => $post_data['total_tax'],
            'total_price'          => $post_data['sub_total'],
            'grand_total'          => $post_data['grand_total'],
            'coupon_id'            => $post_data['coupon_id'],
            'coupon_discount'      => $post_data['coupon_discount'],
            'shipping_cost'        => $post_data['shipping_cost'],
            'sale_status'          => 2,
            'payment_status'       => 1,
            'sale_note'            => $post_data['sale_note'],
            'sale_type'            => 'online',
            'payment_mode'         => $post_data['payment_mode'],
            'created_at'           => date('Y-m-d H:i:s'),
            'currency_id'          => $request->input('currency'),
            'exchange_rate'        => $currency->exchange_rate ?? 1
        ]);

        $cart = session()->has('cart') ? session()->get('cart') : [];
        foreach ($cart as $key => $product) {
            if ($product['variant'] != 0) {
                $product_variant = implode('/', $product['variant']);
                $variant = DB::table('variants')->where('name', $product_variant)->first();
                $variant_id = $variant->id;
            } else {
                $variant_id = 0;
            }
            Product_Sale::create([
                'sale_id' => $create_sale->id,
                'product_id' => $product['id'],
                'variant_id' => $variant_id,
                'qty' => $product['qty'],
                'net_unit_price' => $product['unit_price'],
                'sale_unit_id' => $product['sale_unit_id'],
                'discount' => 0,
                'tax_rate' => 0,
                'tax' => 0,
                'total' => $product['total_price'],
            ]);
        }

        if (auth()->user()) {
            $user_id = auth()->user()->id;
        } else {
            $user = User::where('role_id', 1)->where('is_active', 1)->first();
            $user_id = $user->id;
        }

        // if(!empty($post_data['billing_email']) && filter_var($post_data['billing_email'], FILTER_VALIDATE_EMAIL)) {

        //     $mail_setting = MailSetting::latest()->first();
        //     if($mail_setting) {
        //         $this->setMailInfo($mail_setting);
        //         Mail::to($post_data['billing_email'])->send(new OrderConfirmation($create_sale));
        //     }
        // }

        $account = Account::where('is_default', 1)->first();

        if ($post_data['payment_mode'] == 'whatsapp_order') {
            session(['cart' => [], 'total_qty' => 0, 'subTotal' => 0]);
            return response()->json(['success' => true, 'reference' => $create_sale->reference_no]);
        }

        if ($post_data['payment_mode'] == 'qr_code') {

            $folder = public_path('frontend/images/').'payment-proof';

            if (!file_exists($folder)) {
                mkdir($folder, 0777, true); // 0777 = full permissions, true = recursive creation
            }

            if ($request->hasFile('payment_proof')) {
                $payment_proof = $request->file('payment_proof');

                if ($payment_proof->isValid()) {
                    $ext = $payment_proof->getClientOriginalExtension();
                    $imageName = date("YmdHis") . '.' . $ext;

                    $uploadPath = public_path('frontend/images/payment-proof');
                    if (!file_exists($uploadPath)) {
                        mkdir($uploadPath, 0755, true);
                    }

                    $payment_proof->move($uploadPath, $imageName);

                    $manager = new ImageManager(new GdDriver());
                    $image = $manager->read($uploadPath . '/' . $imageName);

                    $originalWidth = $image->width();
                    $originalHeight = $image->height();

                    if ($originalWidth > 1000) {
                        $newWidth = 1000;
                        $newHeight = intval(($originalHeight / $originalWidth) * $newWidth);
                        $image->resize($newWidth, $newHeight);
                    }

                    $image->save(public_path('frontend/images/payment-proof') . $imageName, quality: 100);

                    $payment_proof = $imageName;
                }
            }


            if ($post_data['grand_total'] > 0) {
                $payment = Payment::create([
                    'payment_reference' => 'spr-' . date('Ymd-His'),
                    'user_id' => $user_id,
                    'sale_id' => $create_sale->id,
                    'account_id' => $account->id,
                    'amount' => $post_data['grand_total'],
                    'change' => 0,
                    'paying_method' => 'QR Code',
                    'payment_proof' => $payment_proof ?? null
                ]);
            }

            session(['cart' => [], 'total_qty' => 0, 'subTotal' => 0]);
            return redirect()->route('order.success', $create_sale->reference_no);
        }

        if (strlen($request->input('gift_card_id')) > 0 && strlen($request->input('gift_card_amount')) > 0) {
            $post_data['gift_card_id'] = trim(htmlspecialchars($request->input('gift_card_id')));
            $post_data['gift_card_amount'] = trim(htmlspecialchars($request->input('gift_card_amount')));

            $payment = Payment::create([
                'payment_reference' => 'spr-' . date('Ymd-His'),
                'user_id' => $user_id,
                'sale_id' => $create_sale->id,
                'account_id' => $account->id,
                'amount' => $post_data['gift_card_amount'],
                'change' => 0,
                'paying_method' => 'Gift Card'
            ]);

            PaymentWithGiftCard::create([
                'gift_card_id' => $post_data['gift_card_id'],
                'payment_id' => $payment->id,
            ]);

            $gift_card = GiftCard::where('id', $post_data['gift_card_id'])->first();
            if($post_data['gift_card_amount'] > $post_data['grand_total']) {
                $gift_card->amount = ($gift_card->amount - $post_data['grand_total']);

                $sale_update = Sale::find($create_sale->id);
                $sale_update->payment_status = 4;
                $sale_update->save();
            } else {
                $gift_card->amount = ($gift_card->amount - $post_data['gift_card_amount']);
            }
            $gift_card->save();

            $post_data['grand_total'] = ($post_data['grand_total'] - $post_data['gift_card_amount']);
        }



        if ($post_data['payment_mode'] != 'Cash on Delivery') {
            session(['grand_total' => $post_data['grand_total'], 'sale_id' => $create_sale->id, 'mode' => $post_data['payment_mode'], 'user_id' => $user_id, 'account_id' => $account->id]);
            return redirect()->route('online.payment');
        } else {
            // if ($post_data['grand_total'] > 0) {
            //     $payment = Payment::create([
            //         'payment_reference' => 'spr-' . date('Ymd-His'),
            //         'user_id' => $user_id,
            //         'sale_id' => $create_sale->id,
            //         'account_id' => $account->id,
            //         'amount' => $post_data['grand_total'],
            //         'change' => 0,
            //         'paying_method' => 'Cash'
            //     ]);
            // }

            session(['cart' => [], 'total_qty' => 0, 'subTotal' => 0]);
            return redirect()->route('order.success', $create_sale->reference_no);
        }

        //sms send start
        $smsTemplate = SmsTemplate::where('is_default_ecommerce',1)->latest()->first();
        $smsProvider = ExternalService::where('active',true)->where('type','sms')->first();
        $pos_setting_data = PosSetting::latest()->first();

        if($smsProvider && $smsTemplate && $pos_setting_data['send_sms'] == 1)
        {
            $smsData['type'] = 'online';
            $customerData['billing_name'] = $post_data['billing_name'];
            $customerData['billing_phone'] = $post_data['billing_phone'];
            $smsData['customer_id'] = $customerData;
            $smsData['template_id'] = $smsTemplate['id'];
            $smsData['sale_status'] = 2;
            $smsData['payment_status'] = 1;
            $smsData['reference_no'] = $post_data['reference_no'];
            $this->_smsModel->initialize($smsData);
        }
        //sms send end
    }

    public function onlinePayment()
    {
        $grand_total = session()->get('grand_total');
        $sale_id = session()->get('sale_id');
        $mode = session()->get('mode');

        $user_id = session()->get('user_id');
        $account_id = session()->get('account_id');

        if ($mode == 'Stripe') {
            $payment = Payment::create([
                'payment_reference' => 'spr-' . date('Ymd-His'),
                'user_id' => $user_id,
                'sale_id' => $sale_id,
                'account_id' => $account_id,
                'amount' => $grand_total,
                'change' => 0,
                'paying_method' => 'Credit Card'
            ]);

            $payment_id = $payment->id;

            $stripe = DB::table('external_services')->where('name', 'Stripe')->first();
            $stripe_details = $stripe->details;
            $details_array = explode(';', $stripe_details);
            $publishable_key = explode(',', $details_array[1])[0];

            return view('ecommerce::frontend.checkout-online', compact('publishable_key', 'grand_total', 'sale_id', 'mode', 'payment_id'));
        }

        if ($mode == 'PayPal') {
            $payment = Payment::create([
                'payment_reference' => 'spr-' . date('Ymd-His'),
                'user_id' => $user_id,
                'sale_id' => $sale_id,
                'account_id' => $account_id,
                'amount' => $grand_total,
                'change' => 0,
                'paying_method' => 'Paypal'
            ]);

            $payment_id = $payment->id;

            $paypal = DB::table('external_services')->where('name', 'PayPal')->first();
            $paypal_details = $paypal->details;
            $details_array = explode(';', $paypal_details);
            $client_id = explode(',', $details_array[1])[0];

            return view('ecommerce::frontend.checkout-online', compact('client_id', 'grand_total', 'sale_id', 'mode', 'payment_id'));
        }

        if ($mode == 'Mollie') {
            $payment = Payment::create([
                'payment_reference' => 'spr-' . date('Ymd-His'),
                'user_id' => $user_id,
                'sale_id' => $sale_id,
                'account_id' => $account_id,
                'amount' => $grand_total,
                'change' => 0,
                'paying_method' => 'Mollie'
            ]);

            $payment_id = $payment->id;

            return view('ecommerce::frontend.checkout-online', compact('grand_total', 'sale_id', 'mode', 'payment_id'));
        }

        if ($mode == 'Xendit') {
            $payment = Payment::create([
                'payment_reference' => 'spr-' . date('Ymd-His'),
                'user_id' => $user_id,
                'sale_id' => $sale_id,
                'account_id' => $account_id,
                'amount' => $grand_total,
                'change' => 0,
                'paying_method' => 'Xendit'
            ]);

            $payment_id = $payment->id;

            $xendit = DB::table('external_services')->where('name', 'Xendit')->first();
            $xendit_details = $xendit->details;
            $details_array = explode(';', $xendit_details);
            $key = explode(',', $details_array[1])[0];

            Configuration::setXenditKey($key);

            $createInvoice = new CreateInvoiceRequest([
                'external_id' => 'inv-'.$sale_id,
                'amount' => ($grand_total*1000),
                'invoice_duration' => 172800,
                //'items' => array($items)
            ]);

            $apiInstance = new InvoiceApi();
            $generateInvoice = $apiInstance->createInvoice($createInvoice);

            $invoice_url = $generateInvoice['invoice_url'];

            return view('ecommerce::frontend.checkout-online', compact('grand_total', 'sale_id', 'mode', 'payment_id','invoice_url'));
        }

        if ($mode == 'Razorpay') {
            $payment = Payment::create([
                'payment_reference' => 'spr-' . date('Ymd-His'),
                'user_id' => $user_id,
                'sale_id' => $sale_id,
                'account_id' => $account_id,
                'amount' => $grand_total,
                'change' => 0,
                'paying_method' => 'Credit Card'
            ]);

            $payment_id = $payment->id;

            $razorpay = DB::table('external_services')->where('name', 'Razorpay')->first();
            $razorpay_details = $razorpay->details;
            $details_array = explode(';', $razorpay_details);
            $key = explode(',', $details_array[1])[0];

            return view('ecommerce::frontend.checkout-online', compact('key', 'grand_total', 'sale_id', 'mode', 'payment_id'));
        }

        if ($mode == 'Paystack') {
            $payment = Payment::create([
                'payment_reference' => 'spr-' . date('Ymd-His'),
                'user_id' => $user_id,
                'sale_id' => $sale_id,
                'account_id' => $account_id,
                'amount' => $grand_total,
                'change' => 0,
                'paying_method' => 'Credit Card'
            ]);

            $payment_id = $payment->id;

            $paystack = DB::table('external_services')->where('name', 'Paystack')->first();
            $paystack_details = $paystack->details;
            $details_array = explode(';', $paystack_details);
            $public_key = explode(',', $details_array[1])[0];

            return view('ecommerce::frontend.checkout-online', compact('public_key', 'grand_total', 'sale_id', 'mode', 'payment_id'));
        }
    }

    protected function processStripePayment($amount, $method_id, $secret_key)
    {
        try {
            // Set your secret API key
            \Stripe\Stripe::setApiKey($secret_key);

            // Step 1: Create or retrieve the customer
            $customer = \Stripe\Customer::create([
                'name' => 'Anonymous Customer',
            ]);

            // Step 2: Attach the Payment Method to the Customer
            $paymentMethod = \Stripe\PaymentMethod::retrieve($method_id);
            $paymentMethod->attach(['customer' => $customer->id]);

            // Step 3: Set the Payment Method as the default for future payments
            \Stripe\Customer::update($customer->id, [
                'invoice_settings' => ['default_payment_method' => $method_id],
            ]);

            // Step 4: Create a PaymentIntent to charge the customer
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $amount * 100, // Amount in cents
                'currency' => 'usd',
                'customer' => $customer->id,
                'payment_method' => $method_id,
                'off_session' => true,
                'confirm' => true, // Automatically confirm the payment
            ]);

            return ['success' => true, 'paymentIntent' => $paymentIntent];
        } catch (\Stripe\Exception\ApiErrorException $e) {
            // Catch Stripe-specific exceptions
            return ['error' => $e->getMessage()];
        }
    }

    public function xenditPayment(Request $request)
    {
        $getToken = $request->headers->get('x-callback-token');

        $xendit = DB::table('external_services')->where('name', 'Xendit')->first();
        $xendit_details = $xendit->details;
        $details_array = explode(';', $xendit_details);
        $callbackToken = explode(',', $details_array[1])[1];

        try {

            if (!$callbackToken) {
                return response()->json([
                    'status' => 'Error',
                    'message' => 'Callback token xendit not exists'
                ], Response::HTTP_NOT_FOUND);
            }

            if ($getToken !== $callbackToken) {
                return response()->json([
                    'status' => 'Error',
                    'message' => 'Token callback invalid'
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            if ($request->status === 'PAID') {
                $sale_id =  str_replace('inv-','',$request->external_id);
                $sale = Sale::find($sale_id);
                $sale->payment_status = 4;
                $sale->save();

            }

        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function xenditSuccess()
    {
        $sale = Sale::find(session()->get('sale_id'));

        if($sale->payment_status != 4){
            return redirect()->back();
        }else{
            session(['cart' => [], 'total_qty' => 0, 'subTotal' => 0, 'grand_total' => 0, 'sale_id' => '', 'mode' => '', 'user_id' => '', 'account_id' => '']);

            return redirect()->route('order.success', ['sale_reference' => $sale->reference_no]);
        }
    }

    public function stripePayment(Request $request)
    {
        $stripe = DB::table('external_services')->where('name', 'Stripe')->first();
        $stripe_details = $stripe->details;
        $details_array = explode(';', $stripe_details);
        $secret_key = explode(',', $details_array[1])[1];

        $this->processStripePayment($request->grand_total, $request->paymentMethodId, $secret_key);

        $sale = Sale::find($request->sale_id);
        if (isset($result['success']) && $result['success']) {
            $sale->payment_status = 4;
            $sale->save();

            session(['cart' => [], 'total_qty' => 0, 'subTotal' => 0, 'grand_total' => 0, 'sale_id' => '', 'mode' => '', 'user_id' => '', 'account_id' => '']);

            return redirect()->route('order.success', ['sale_reference' => $sale->reference_no]);
        } else {
            return redirect()->route('order.cancel')->with('error', $result['error'] ?? 'Payment failed');
        }
    }

    public function paypalPayment(Request $request)
    {
        PaymentWithPaypal::create([
            'payment_id' => $request->payment_id,
            'transaction_id' => $request->transaction_id
        ]);

        $sale = Sale::find(session()->get('sale_id'));
        $sale->payment_status = 4;
        $sale->save();

        session(['cart' => [], 'total_qty' => 0, 'subTotal' => 0, 'grand_total' => 0, 'sale_id' => '', 'mode' => '', 'user_id' => '', 'account_id' => '']);

        return response()->json(["success" => "Success", "sale_reference" => $sale->reference_no]);
    }

    public function molliePayment(Request $request)
    {
        $mollie = DB::table('external_services')->where('name', 'Mollie')->first();
        $mollie_details = $mollie->details;
        $details_array = explode(';', $mollie_details);
        $key = explode(',', $details_array[1])[0];

        $mollie = new \Mollie\Api\MollieApiClient();
        $mollie->setApiKey($key);

        $currency = cache('currency')->code;
        $sale = Sale::find($request->sale_id);

        $payment = $mollie->payments->create([
            "amount" => [
                "currency" => $currency,
                "value" => $request->grand_total.'.00' // You must send the correct number of decimals, thus we enforce the use of strings
            ],
            "description" => "Payment with Mollie",
            "redirectUrl" => route('mollie.success'),
            //"webhookUrl" => route('webhooks.mollie'),
            "metadata" => [
                "order_id" => time(),
            ],
        ]);

        session()->put('paymentId', $payment->id);

        return redirect()->route('order.success', ['sale_reference' => $sale->reference_no]);

    }

    public function mollieSuccess()
    {
        $sale = Sale::find(session()->get('sale_id'));
        $sale->payment_status = 4;
        $sale->save();

        session(['cart' => [], 'total_qty' => 0, 'subTotal' => 0, 'grand_total' => 0, 'sale_id' => '', 'mode' => '', 'user_id' => '', 'account_id' => '']);

        return response()->json(["success" => "Success", "sale_reference" => $sale->reference_no]);
    }

    public function razorpayPayment(Request $request)
    {
        $razorpay = DB::table('external_services')->where('name', 'Razorpay')->first();
        $razorpay_details = $razorpay->details;
        $details_array = explode(';', $razorpay_details);
        $key = explode(',', $details_array[1])[0];
        $secret = explode(',', $details_array[1])[1];

        $input = $request->all();

        $api = new Api($key, $secret);

        $payment = $api->payment->fetch($input['razorpay_payment_id']);

        if (count($input)  && !empty($input['razorpay_payment_id'])) {

            try {
                $response = $api->payment->fetch($input['razorpay_payment_id'])->capture(array('amount' => $payment['amount']));
            } catch (Exception $e) {

                return  $e->getMessage();

                Session::put('error', $e->getMessage());

                return redirect()->back();
            }
        }

        $sale = Sale::find($request->sale_id);
        $sale->payment_status = 4;
        $sale->save();

        session(['cart' => [], 'total_qty' => 0, 'subTotal' => 0, 'grand_total' => 0, 'sale_id' => '', 'mode' => '', 'user_id' => '', 'account_id' => '']);

        return redirect()->route('order.success', ['sale_reference' => $sale->reference_no]);
    }

    public function paystackPayment(Request $request)
    {
        $paystack = DB::table('external_services')->where('name', 'Paystack')->first();
        $paystack_details = $paystack->details;
        $details_array = explode(';', $paystack_details);
        //$public_key = explode(',', $details_array[1])[0];
        $secret_key = explode(',', $details_array[1])[1];

        $reference = $request->reference;
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.paystack.co/transaction/verify/".$reference,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer $secret_key",
                "Cache-Control: no-cache",
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response);

        if($response->data->status == 'success')
        {
            $sale = Sale::find($request->sale_id);
            $sale->payment_status = 4;
            $sale->save();

            session(['cart' => [], 'total_qty' => 0, 'subTotal' => 0, 'grand_total' => 0, 'sale_id' => '', 'mode' => '', 'user_id' => '', 'account_id' => '']);

            return redirect()->route('order.success', ['sale_reference' => $sale->reference_no]);
        } else {
            return redirect()->route('cancel');
        }
    }

    public function success($sale_reference)
    {
        return view('ecommerce::frontend.success', compact('sale_reference'));
    }

    public function cancel(Request $request)
    {
        return view('ecommerce::frontend.cancel');
    }
}
