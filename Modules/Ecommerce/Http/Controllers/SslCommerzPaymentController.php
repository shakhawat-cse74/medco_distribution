<?php

namespace Modules\Ecommerce\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Library\SslCommerz\SslCommerzNotification;

class SslCommerzPaymentController extends Controller
{

    public function exampleEasyCheckout()
    {
        return view('exampleEasycheckout');
    }

    public function exampleHostedCheckout()
    {
        return view('exampleHosted');
    }

    public function index(Request $request)
    {
        # Here you have to receive all the order data to initate the payment.
        # Let's say, your oder transaction informations are saving in a table called "sales"
        # In "sales" table, order unique identity is "reference_no". "status" field contain status of the transaction, "amount" is the order amount to be paid and "currency" is for storing Site Currency which will be checked with paid currency.

        $post_data = array();
        $post_data['total_amount'] = '10'; # You cant not pay less than 10
        $post_data['currency'] = "BDT";
        $post_data['reference_no'] = uniqid(); // reference_no must be unique

        # CUSTOMER INFORMATION
        $post_data['cus_name'] = 'Customer Name';
        $post_data['cus_email'] = 'customer@mail.com';
        $post_data['cus_add1'] = 'Customer Address';
        $post_data['cus_add2'] = "";
        $post_data['cus_city'] = "";
        $post_data['cus_state'] = "";
        $post_data['cus_postcode'] = "";
        $post_data['cus_country'] = "Bangladesh";
        $post_data['cus_phone'] = '8801XXXXXXXXX';
        $post_data['cus_fax'] = "";

        # SHIPMENT INFORMATION
        $post_data['ship_name'] = "Store Test";
        $post_data['ship_add1'] = "Dhaka";
        $post_data['ship_add2'] = "Dhaka";
        $post_data['ship_city'] = "Dhaka";
        $post_data['ship_state'] = "Dhaka";
        $post_data['ship_postcode'] = "1000";
        $post_data['ship_phone'] = "";
        $post_data['ship_country'] = "Bangladesh";

        $post_data['shipping_method'] = "NO";
        $post_data['product_name'] = "Computer";
        $post_data['product_category'] = "Goods";
        $post_data['product_profile'] = "physical-goods";

        # OPTIONAL PARAMETERS
        $post_data['value_a'] = "ref001";
        $post_data['value_b'] = "ref002";
        $post_data['value_c'] = "ref003";
        $post_data['value_d'] = "ref004";

        #Before  going to initiate the payment order status need to insert or update as Pending.
        $update_product = DB::table('sales')
            ->where('reference_no', $post_data['reference_no'])
            ->updateOrInsert([
                'name' => $post_data['cus_name'],
                'email' => $post_data['cus_email'],
                'phone' => $post_data['cus_phone'],
                'grand_total' => $post_data['total_amount'],
                'payment_status' => 'Pending',
                'address' => $post_data['cus_add1'],
                'reference_no' => $post_data['reference_no'],
                'currency' => $post_data['currency']
            ]);

        $sslc = new SslCommerzNotification();
        # initiate(Transaction Data , false: Redirect to SSLCOMMERZ gateway/ true: Show all the Payement gateway here )
        $payment_options = $sslc->makePayment($post_data, 'hosted');

        if (!is_array($payment_options)) {
            print_r($payment_options);
            $payment_options = array();
        }

    }

    public function payViaAjax(Request $request)
    {

        # Here you have to receive all the order data to initate the payment.
        # Lets your oder trnsaction informations are saving in a table called "sales"
        # In sales table order uniq identity is "reference_no","status" field contain status of the transaction, "amount" is the order amount to be paid and "currency" is for storing Site Currency which will be checked with paid currency.

        $post_data = array();
        
        $post_data['reference_no'] = uniqid(); // reference_no must be unique
        $post_data['total_amount'] = '10'; # You cant not pay less than 10
        $post_data['currency'] = "BDT";

        # CUSTOMER INFORMATION
        $post_data['user_id'] = auth()->user()->id;
        $post_data['customer_id'] = auth()->user()->id;
        $post_data['cus_name'] = trim(htmlspecialchars($request->input('cus_name')));
        $post_data['cus_email'] = trim(htmlspecialchars($request->input('cus_email')));
        $post_data['cus_phone'] = trim(htmlspecialchars($request->input('cus_phone')));
        $post_data['cus_add1'] = trim(htmlspecialchars($request->input('cus_addr1')));
        $post_data['cus_city'] = trim(htmlspecialchars($request->input('cus_city')));
        $post_data['cus_postcode'] = trim(htmlspecialchars($request->input('cus_postcode')));
        

        # SHIPMENT INFORMATION
        $post_data['ship_add1'] = trim(htmlspecialchars($request->input('ship_addr1')));
        $post_data['ship_city'] = trim(htmlspecialchars($request->input('ship_city')));
        $post_data['ship_postcode'] = trim(htmlspecialchars($request->input('ship_postcode')));

        $post_data['sale_note'] = trim(htmlspecialchars($request->input('sale_note')));


        # OPTIONAL PARAMETERS
        $post_data['item'] = trim(htmlspecialchars($request->input('item')));
        $post_data['total_qty'] = trim(htmlspecialchars($request->input('total_qty')));
        $post_data['total_discount'] = 0; 
        $post_data['total_tax'] = 0;  
        $post_data['sub_total'] = trim(htmlspecialchars($request->input('sub_total')));             
        $post_data['grand_total'] = trim(htmlspecialchars($request->input('grand_total')));
        $post_data['coupon_id'] = trim(htmlspecialchars($request->input('coupon_id')));
        $post_data['coupon_discount'] = trim(htmlspecialchars($request->input('coupon_discount')));
        $post_data['shipping_cost'] = trim(htmlspecialchars($request->input('shipping_cost')));
        $post_data['sale_type'] = 'online';


        #Before  going to initiate the payment order status need to update as Pending.
        $update_product = DB::table('sales')
            ->where('reference_no', $post_data['reference_no'])
            ->updateOrInsert([
                'reference_no'         => $post_data['reference_no'],
                'transaction_id'       => $post_data['reference_no'],
                'user_id'              => $post_data['user_id'],
                'customer_id'          => $post_data['customer_id'],
                'name'                 => $post_data['cus_name'],
                'email'                => $post_data['cus_email'],
                'phone'                => $post_data['cus_phone'],
                'billing_address'      => $post_data['cus_add1'],
                'billing_city'         => $post_data['cus_city'],
                'billing_postal_code'  => $post_data['cus_postcode'],
                'ship_address'         => $post_data['ship_add1'],
                'ship_city'            => $post_data['ship_city'],
                'ship_postal_code'     => $post_data['ship_postcode'],
                'warehouse_id'         => 1,
                'item'                 => $post_data['item'],
                'total_qty'            => $post_data['total_qty'],
                'total_discount'       => $post_data['total_discount'], 
                'total_tax'            => $post_data['total_tax'],  
                'total_price'          => $post_data['sub_total'],             
                'grand_total'          => $post_data['grand_total'],
                'amount'               => $post_data['grand_total'],
                'coupon_id'            => $post_data['coupon_id'],
                'coupon_discount'      => $post_data['coupon_discount'],
                'shipping_cost'        => $post_data['shipping_cost'],
                'sale_status'          => 2,
                'payment_status'       => 1,
                'status'               => 'Processing',
                'sale_note'            => $post_data['sale_note'],                
                'currency'             => $post_data['currency'],
                'sale_type'            => $post_data['sale_type'] 
            ]);

        $sslc = new SslCommerzNotification();
        # initiate(Transaction Data , false: Redirect to SSLCOMMERZ gateway/ true: Show all the Payement gateway here )
        $payment_options = $sslc->makePayment($post_data, 'checkout', 'json');

        if (!is_array($payment_options)) {
            print_r($payment_options);
            $payment_options = array();
        }

    }

    public function success(Request $request)
    {
        echo "Transaction is Successful";

        $tran_id = $request->input('tran_id');
        $amount = $request->input('amount');
        $currency = $request->input('currency');

        $sslc = new SslCommerzNotification();

        #Check order status in order tabel against the transaction id or order id.
        $order_detials = DB::table('orders')
            ->where('transaction_id', $tran_id)
            ->select('transaction_id', 'status', 'currency', 'amount')->first();

        if ($order_detials->status == 'Pending') {
            $validation = $sslc->orderValidate($tran_id, $amount, $currency, $request->all());

            if ($validation == TRUE) {
                /*
                That means IPN did not work or IPN URL was not set in your merchant panel. Here you need to update order status
                in order table as Processing or Complete.
                Here you can also sent sms or email for successfull transaction to customer
                */
                $update_product = DB::table('orders')
                    ->where('transaction_id', $tran_id)
                    ->update(['status' => 'Processing']);

                echo "<br >Transaction is successfully Completed";
            } else {
                /*
                That means IPN did not work or IPN URL was not set in your merchant panel and Transation validation failed.
                Here you need to update order status as Failed in order table.
                */
                $update_product = DB::table('orders')
                    ->where('transaction_id', $tran_id)
                    ->update(['status' => 'Failed']);
                echo "validation Fail";
            }
        } else if ($order_detials->status == 'Processing' || $order_detials->status == 'Complete') {
            /*
             That means through IPN Order status already updated. Now you can just show the customer that transaction is completed. No need to udate database.
             */
            echo "Transaction is successfully Completed";
        } else {
            #That means something wrong happened. You can redirect customer to your product page.
            echo "Invalid Transaction";
        }


    }

    public function fail(Request $request)
    {
        $tran_id = $request->input('tran_id');

        $order_detials = DB::table('orders')
            ->where('transaction_id', $tran_id)
            ->select('transaction_id', 'status', 'currency', 'amount')->first();

        if ($order_detials->status == 'Pending') {
            $update_product = DB::table('orders')
                ->where('transaction_id', $tran_id)
                ->update(['status' => 'Failed']);
            echo "Transaction is Falied";
        } else if ($order_detials->status == 'Processing' || $order_detials->status == 'Complete') {
            echo "Transaction is already Successful";
        } else {
            echo "Transaction is Invalid";
        }

    }

    public function cancel(Request $request)
    {
        $tran_id = $request->input('tran_id');

        $order_detials = DB::table('orders')
            ->where('transaction_id', $tran_id)
            ->select('transaction_id', 'status', 'currency', 'amount')->first();

        if ($order_detials->status == 'Pending') {
            $update_product = DB::table('orders')
                ->where('transaction_id', $tran_id)
                ->update(['status' => 'Canceled']);
            echo "Transaction is Cancel";
        } else if ($order_detials->status == 'Processing' || $order_detials->status == 'Complete') {
            echo "Transaction is already Successful";
        } else {
            echo "Transaction is Invalid";
        }


    }

    public function ipn(Request $request)
    {
        #Received all the payement information from the gateway
        if ($request->input('tran_id')) #Check transation id is posted or not.
        {

            $tran_id = $request->input('tran_id');

            #Check order status in order tabel against the transaction id or order id.
            $order_details = DB::table('orders')
                ->where('transaction_id', $tran_id)
                ->select('transaction_id', 'status', 'currency', 'amount')->first();

            if ($order_details->status == 'Pending') {
                $sslc = new SslCommerzNotification();
                $validation = $sslc->orderValidate($tran_id, $order_details->amount, $order_details->currency, $request->all());
                if ($validation == TRUE) {
                    /*
                    That means IPN worked. Here you need to update order status
                    in order table as Processing or Complete.
                    Here you can also sent sms or email for successful transaction to customer
                    */
                    $update_product = DB::table('orders')
                        ->where('transaction_id', $tran_id)
                        ->update(['status' => 'Processing']);

                    echo "Transaction is successfully Completed";
                } else {
                    /*
                    That means IPN worked, but Transation validation failed.
                    Here you need to update order status as Failed in order table.
                    */
                    $update_product = DB::table('orders')
                        ->where('transaction_id', $tran_id)
                        ->update(['status' => 'Failed']);

                    echo "validation Fail";
                }

            } else if ($order_details->status == 'Processing' || $order_details->status == 'Complete') {

                #That means Order status already updated. No need to udate database.

                echo "Transaction is already successfully Completed";
            } else {
                #That means something wrong happened. You can redirect customer to your product page.

                echo "Invalid Transaction";
            }
        } else {
            echo "Invalid Data";
        }
    }

}
