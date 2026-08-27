<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Ecommerce\Http\Controllers\FrontController;

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

Route::middleware('auth:sanctum')->get('/ecommerce', function (Request $request) {
    return $request->user();
});

if (class_exists(FrontController::class)) {
    Route::middleware(['ecommerce', 'api'])->group(function () {
        Route::get('/home', [FrontController::class, 'index']);
        Route::get('shop/{category}', [FrontController::class, 'shop']);
        Route::get('product/{product_name}/{product_id}', [FrontController::class, 'productDetails']);
        Route::get('brand/{brand}', [FrontController::class, 'brandProducts']);
    });
}