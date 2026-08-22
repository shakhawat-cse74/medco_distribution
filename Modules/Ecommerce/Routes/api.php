<?php
use Modules\Ecommerce\Http\Controllers\Api;
use Illuminate\Http\Request;
use Modules\Ecommerce\Http\Controllers\Api\HomePageController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/ecommerce', function (Request $request) {
    return $request->user();
});

	Route::middleware(['ecommerce','web'])->group(function () {
        Route::get('/home', [HomePageController::class, 'homePage']);
        Route::get('shop/{category}', [HomePageController::class, 'category']);
        Route::get('product/{product_name}/{product_id}', [HomePageController::class, 'productDetails']);
        Route::get('brand/{brand}', [HomePageController::class, 'brandProducts']);
	});