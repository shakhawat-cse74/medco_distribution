<?php

namespace Modules\Restaurant\Http\Controllers;

use Modules\Restaurant\Entities\Kitchens;
use App\Models\User;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Session;
use DB; 
use Auth;

class kitchenController extends Controller
{

    public function index()
    {
        $kitchens = Kitchens::all();
        $users = User::where('role_id','>',2)->where('is_active',1)->where('is_deleted',0)->get();

        return view('restaurant::backend.kitchen.index', compact('kitchens','users'));
    }

    public function store(Request $request)
    {
        if(!config('app.user_verified')){
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');
        }
        $data = $request->all();

        if (Kitchens::create($data)) {
            $newdata = Kitchens::orderby('id', 'DESC')->first();
            Session::flash('message', 'Kitchen saved successfully.');
            Session::flash('type', 'success');
            return redirect()->back();
        } else {
            Session::flash('message', 'Failed to save kitchen.');
            Session::flash('type', 'danger');
            return redirect()->back();
        }
    }

    public function edit($id)
    {
        $kitchen = Kitchens::find($id);
        return $kitchen;
    }

    public function update(Request $request)
    {
        if(!config('app.user_verified')){
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');
        }
        $kitchen = Kitchens::findOrFail($request->kitchenid);

        $prev_user = User::find($kitchen->user_id);

        if(!empty($prev_user)){
            if(!empty($prev_user->kitchen_id)){
                $prev_kitchen = explode(',', $prev_user->kitchen_id);
                $del_kitchen = explode(',', $kitchen->id);
                $prev_user->kitchen_id = implode(',', array_diff($prev_kitchen, $del_kitchen));

                $prev_user->save();
            }
        }

        $kitchen->name = $request->name;
        $kitchen->user_id = $request->user_id;

        $kitchen->save();

        $user = User::find($request->user_id);
        if(!empty($user->kitchen_id)){
            if(!in_array($kitchen->id, explode(',', $user->kitchen_id))){
                $user->kitchen_id = $user->kitchen_id . ',' . $kitchen->id;
            }
        }else{
            $user->kitchen_id = $kitchen->id;
        }

        $user->save();

        Session::flash('message', 'Kitchen saved successfully.');
        Session::flash('type', 'success');
        return redirect()->back();
    }

    public function destroy(Request $request)
    {
        if(!config('app.user_verified')){
            return redirect()->back()->with('not_permitted', 'This feature is disable for demo!');
        }

        Kitchens::findOrFail($request->id)->delete();

        Session::flash('message', 'Kitchen deleted successfully.');
        Session::flash('type', 'success');
        return redirect()->back();
    }

    public function dashboard()
    {
        $user = Auth::user();

        if ($user->kitchen_id) {
            $kitchens = DB::table('kitchens')
            ->whereIn('id', explode(',', Auth::user()->kitchen_id));

            $kitchen_ids = $kitchens->pluck('id');
            $kitchen_list = $kitchens->get();

            $salesData = DB::table('sales')
                ->join('product_sales', 'sales.id', '=', 'product_sales.sale_id')
                ->join('products', 'product_sales.product_id', '=', 'products.id')
                ->join('customers', 'sales.customer_id', '=', 'customers.id')
                ->join('billers', 'sales.biller_id', '=', 'billers.id')
                ->join('users', 'sales.user_id', '=', 'users.id')
                ->join('warehouses', 'sales.warehouse_id', '=', 'warehouses.id')
                ->leftJoin('tables', 'sales.table_id', '=', 'tables.id')
                ->select(
                    'products.kitchen_id',
                    'products.name as product_name',
                    'sales.id as sale_id',
                    'sales.created_at as sale_date',
                    'sales.sale_status',
                    'sales.reference_no',
                    'sales.total_tax',
                    'sales.total_discount',
                    'sales.total_price',
                    'sales.order_tax',
                    'sales.order_tax_rate',
                    'sales.order_discount',
                    'sales.shipping_cost',
                    'sales.grand_total',
                    'sales.paid_amount',
                    'sales.sale_note',
                    'sales.staff_note',
                    'sales.coupon_discount',
                    'sales.document',
                    'sales.exchange_rate',
                    'sales.coupon_id',
                    'sales.currency_id',
                    'users.name as user_name',
                    'users.email as user_email',
                    'warehouses.name as warehouse',
                    'customers.name as customer_name',
                    'customers.phone_number as customer_phone_number',
                    'customers.address as customer_address',
                    'customers.city as customer_city',
                    'billers.name as biller_name',
                    'billers.company_name as biller_company_name',
                    'billers.email as biller_email',
                    'billers.phone_number as biller_phone_number',
                    'billers.address as biller_address',
                    'billers.city as biller_city',
                    'tables.name as table_name',
                    'product_sales.id as product_sale_id',
                    'product_sales.qty as total_quantity',
                    DB::raw('(SELECT GROUP_CONCAT(CONCAT(modifiers.name, IF(psm.qty > 1, CONCAT(" (x", psm.qty, ")"), "")) SEPARATOR ", ") 
                              FROM product_sale_modifiers psm 
                              JOIN modifiers ON psm.modifier_id = modifiers.id 
                              WHERE psm.product_sale_id = product_sales.id) as modifier_labels')
                )
                ->whereIn('products.kitchen_id', $kitchen_ids) // Filter by specific kitchen IDs
                ->where('sales.sale_status', 5) 
                ->where('sales.created_at', '>=', now()->subDay()) // Last 24 hours
                ->groupBy(
                    'products.kitchen_id',
                    'products.id',
                    'sales.id',
                    'customers.id',
                    'table_name',
                    'sales.sale_status',
                    'product_sales.id',
                    'product_sales.qty'
                )
                ->orderBy('products.kitchen_id')
                ->get();
            
            $dateFormat = DB::table('general_settings')->value('date_format');

            return view('restaurant::backend.kitchen.dashboard', compact('kitchen_list', 'salesData', 'dateFormat'));
        } else {
            $salesData = DB::table('sales')
                ->join('product_sales', 'sales.id', '=', 'product_sales.sale_id')
                ->join('products', 'product_sales.product_id', '=', 'products.id')
                ->join('customers', 'sales.customer_id', '=', 'customers.id')
                ->join('billers', 'sales.biller_id', '=', 'billers.id')
                ->join('users', 'sales.user_id', '=', 'users.id')
                ->join('warehouses', 'sales.warehouse_id', '=', 'warehouses.id')
                ->leftJoin('tables', 'sales.table_id', '=', 'tables.id')
                ->select(
                    'products.kitchen_id',
                    'products.name as product_name',
                    'sales.id as sale_id',
                    'sales.created_at as sale_date',
                    'sales.sale_status',
                    'sales.reference_no',
                    'sales.total_tax',
                    'sales.total_discount',
                    'sales.total_price',
                    'sales.order_tax',
                    'sales.order_tax_rate',
                    'sales.order_discount',
                    'sales.shipping_cost',
                    'sales.grand_total',
                    'sales.paid_amount',
                    'sales.sale_note',
                    'sales.staff_note',
                    'sales.coupon_discount',
                    'sales.document',
                    'sales.exchange_rate',
                    'sales.coupon_id',
                    'sales.currency_id',
                    'users.name as user_name',
                    'users.email as user_email',
                    'warehouses.name as warehouse',
                    'customers.name as customer_name',
                    'customers.phone_number as customer_phone_number',
                    'customers.address as customer_address',
                    'customers.city as customer_city',
                    'billers.name as biller_name',
                    'billers.company_name as biller_company_name',
                    'billers.email as biller_email',
                    'billers.phone_number as biller_phone_number',
                    'billers.address as biller_address',
                    'billers.city as biller_city',
                    'tables.name as table_name',
                    'product_sales.id as product_sale_id',
                    'product_sales.qty as total_quantity',
                    DB::raw('(SELECT GROUP_CONCAT(CONCAT(modifiers.name, IF(psm.qty > 1, CONCAT(" (x", psm.qty, ")"), "")) SEPARATOR ", ") 
                              FROM product_sale_modifiers psm 
                              JOIN modifiers ON psm.modifier_id = modifiers.id 
                              WHERE psm.product_sale_id = product_sales.id) as modifier_labels')
                )
                ->where('sales.sale_status', 6) 
                ->where('sales.created_at', '>=', now()->subDay()) // Last 24 hours
                ->groupBy(
                    'products.kitchen_id',
                    'products.id',
                    'sales.id',
                    'customers.id',
                    'table_name',
                    'sales.sale_status',
                    'product_sales.id',
                    'product_sales.qty'
                )
                ->orderBy('products.kitchen_id');
                

            if($user->service_staff){
                $salesData = $salesData->where('sales.waiter_id', $user->id);
            }

            $salesData = $salesData->get();
            
            $dateFormat = DB::table('general_settings')->value('date_format');

            return view('restaurant::backend.kitchen.dashboard', compact('salesData', 'dateFormat'));
        }
        
    }

    public function markCooked($id)
    {
        $sale = DB::table('sales')->where('id',$id)->first();
        if($sale->sale_status == 5){
            DB::table('sales')->where('id',$id)->update(['sale_status' => 6]);
        }

        return response()->json(['status' => 'success', 'message' => 'Sale status updated successfully.']);
    }

    public function markServed($id)
    {
        $sale = DB::table('sales')->where('id',$id)->first();
        if($sale->sale_status == 6){
            DB::table('sales')->where('id',$id)->update(['sale_status' => 1]);
        }

        return response()->json(['status' => 'success', 'message' => 'Sale status updated successfully.']);
    }


}
