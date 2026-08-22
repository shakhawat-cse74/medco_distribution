<?php

namespace Modules\Ecommerce\Http\Controllers;

use App\Models\Currency;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class EcommerceController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('ecommerce::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('ecommerce::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('ecommerce::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('ecommerce::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }

    public function changeCurrency(Request $request){
        $currency = Currency::where('code', $request->code)->first();
        $subTotal = session()->has('subTotal') ? session()->get('subTotal') : 0;
        if ($currency) {
            session([
                'currency_code' => $currency->code,
                'currency_rate' => $currency->rate,
                'currency_symbol' => $currency->symbol,
                'subTotal' => $subTotal,
            ]);
        }
        return response()->json(
            [
                'success' => true,
                'code' => $currency->symbol ?? $currency->code,
                'exchange_rate' => $currency->exchange_rate,
                'subTotal' => $subTotal,
                'message' => 'Currency changed successfully'
        ]);

    }
}
