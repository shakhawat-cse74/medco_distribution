<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HrmController;
use App\Http\Controllers\TaxController;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\BillerController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\LabelsController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\ChallanController;
use App\Http\Controllers\CourierController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ThemeSettingController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\PrinterController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\AccountsController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\GiftCardController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\OvertimeController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseRequestController;
use App\Http\Controllers\RazorpayController;
use App\Http\Controllers\MpesaController;
use App\Http\Controllers\PaymentGatewayController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\WhatsappController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\SaleAgentController;
use App\Http\Controllers\SteadFastController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\AdjustmentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\StockCountController;
use App\Http\Controllers\CustomFieldController;
use App\Http\Controllers\PackingSlipController;
use App\Http\Controllers\SmsTemplateController;
use App\Http\Controllers\TranslationController;
use App\Http\Controllers\AddonInstallController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CashRegisterController;
use App\Http\Controllers\DiscountPlanController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CustomerGroupController;
use App\Http\Controllers\DamageStockController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\ExchangeController;
use App\Http\Controllers\MoneyTransferController;
use App\Http\Controllers\IncomeCategoryController;
use App\Http\Controllers\InvoiceSettingController;
use App\Http\Controllers\ReturnPurchaseController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\InstallmentPlanController;

Route::get('webview/auth', function (Request $request) {
    // Get token from Authorization header
    $authHeader = $request->header('Authorization');
    if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
        abort(401, 'Missing or invalid Authorization header');
    }
    $token = substr($authHeader, 7);

    $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
    if (!$accessToken) {
        abort(401, 'Invalid token');
    }
    Auth::login($accessToken->tokenable);

    // Optional: allow redirect param as query string
    $redirect = $request->query('redirect', '/');
    $separator = (parse_url($redirect, PHP_URL_QUERY) == NULL) ? '?' : '&';
    return redirect($redirect . $separator . 'app=true');
});

Route::get('/debug/{status}', function ($status) {
    if (!in_array($status, ['true', 'false'])) {
        return response()->json(['message' => 'Invalid status. Use true or false.'], 400);
    }

    $envPath = base_path('.env');
    $envContent = File::get($envPath);

    // Regex to match and replace APP_DEBUG=...
    $pattern = '/^APP_DEBUG=.*/m';
    $replacement = "APP_DEBUG={$status}";

    if (preg_match($pattern, $envContent)) {
        $newContent = preg_replace($pattern, $replacement, $envContent);
    } else {
        $newContent = $envContent . PHP_EOL . $replacement;
    }

    File::put($envPath, $newContent);

    // Clear Laravel's cache so the new debug setting takes effect
    Artisan::call('config:clear');

    return response()->json(['message' => "APP_DEBUG successfully set to {$status}."]);
});

Route::get('migrate', function () {
    Artisan::call('migrate');
    Artisan::call('db:seed');
    dd('migrated');
});

Route::get('clear', function () {
    Artisan::call('optimize:clear');
    cache()->forget('biller_list');
    cache()->forget('brand_list');
    cache()->forget('category_list');
    cache()->forget('coupon_list');
    cache()->forget('customer_list');
    cache()->forget('customer_group_list');
    cache()->forget('product_list');
    cache()->forget('product_list_with_variant');
    cache()->forget('warehouse_list');
    cache()->forget('table_list');
    cache()->forget('tax_list');
    cache()->forget('currency');
    cache()->forget('general_setting');
    cache()->forget('pos_setting');
    cache()->forget('user_role');
    cache()->forget('permissions');
    cache()->forget('role_has_permissions');
    cache()->forget('role_has_permissions_list');
    dd('cleared');
});

Route::get('update-coupon', [CouponController::class, 'updateCoupon']);

Route::controller(InstallController::class)->group(function () {
    Route::get('install/step-1', 'installStep1')->name('install-step-1');
    Route::get('install/step-2', 'installStep2')->name('install-step-2');
    Route::get('install/step-3', 'installStep3')->name('install-step-3');
    Route::post('install/process', 'installProcess')->name('install-process');
    Route::get('install/step-4', 'installStep4')->name('install-step-4');
});

Route::get('delete-account', [\App\Http\Controllers\DeleteAccountRequestController::class, 'show'])->name('delete-account');
Route::post('delete-account', [\App\Http\Controllers\DeleteAccountRequestController::class, 'submit'])->name('delete-account.submit');

Auth::routes();

// ===== Payment Gateway Callbacks (public — auth ছাড়া, Safaricom/MTN/PayHere এর সার্ভার থেকে আসে) =====
Route::post('/payment/{gateway}/callback', [PaymentGatewayController::class, 'callback'])
    ->name('payment.callback')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// Legacy M-Pesa callback (backward compat)
Route::post('/mpesa/callback', [PaymentGatewayController::class, 'callback'])
    ->defaults('gateway', 'mpesa')
    ->name('mpesa.callback')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

Route::group(['middleware' => 'auth'], function () {
    Route::controller(HomeController::class)->group(function () {
        Route::get('home', 'home');
    });
});

Route::group(['middleware' => ['common', 'auth', 'active']], function () {

    Route::get('/languages', [LanguageController::class, 'index'])->name('languages');
    Route::post('/languages/create', [LanguageController::class, 'store']);
    Route::post('/languages/{id}/set-default', [LanguageController::class, 'setDefault']);
    Route::put('/languages/{id}', [LanguageController::class, 'update']);
    Route::delete('/languages/{id}', [LanguageController::class, 'destroy']);

    Route::get('/translations', [TranslationController::class, 'index'])->name('translations');
    Route::get('/translations/{locale}', [TranslationController::class, 'fetchByLanguage']);
    Route::post('/translations', [TranslationController::class, 'store']);
    Route::put('/translations/{id}', [TranslationController::class, 'update']);
    Route::delete('/translations/{id}', [TranslationController::class, 'destroy']);

    Route::controller(HomeController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/dashboard', 'dashboard');

        Route::get('new-release', 'newVersionReleasePage')->name('new-release');
        Route::post('version-upgrade', 'versionUpgrade')->name('version-upgrade');

        Route::get('/yearly-best-selling-price', 'yearlyBestSellingPrice');
        Route::get('/yearly-best-selling-qty', 'yearlyBestSellingQty');
        Route::get('/monthly-best-selling-qty', 'monthlyBestSellingQty');
        Route::get('/recent-sale', 'recentSale');
        Route::get('/recent-purchase', 'recentPurchase');
        Route::get('/recent-quotation', 'recentQuotation');
        Route::get('/recent-payment', 'recentPayment');
        Route::get('switch-theme/{theme}', 'switchTheme')->name('switchTheme');
        Route::post('theme-setting/update', 'updateThemeSettings')->name('themeSetting.update');
        Route::get('/dashboard-filter/{start_date}/{end_date}/{warehouse_id}', 'dashboardFilter');
        Route::get('addon-list', 'addonList');
        Route::get('my-transactions/{year}/{month}', 'myTransaction');
    });

    // Need to check again
    Route::resource('products', ProductController::class)->except(['show']);
    Route::controller(ProductController::class)->group(function () {
        Route::post('products/product-data', 'productData');
        Route::get('products/gencode', 'generateCode')->name('product.gencode');
        Route::get('products/search', 'search');
        Route::get('products/saleunit/{id}', 'saleUnit')->name('product-saleunit');
        Route::get('products/getdata/{id}/{variant_id}', 'getData')->name('products.getdata');
        Route::get('products/product_warehouse/{id}', 'productWarehouseData')->name('product.warehouse');
        Route::get('products/print_barcode', 'printBarcode')->name('product.printBarcode');
        Route::get('products/lims_product_search', 'limsProductSearch')->name('product.search');
        Route::post('products/deletebyselection', 'deleteBySelection')->name('products.deletebyselection');
        Route::post('products/update', 'updateProduct');
        Route::get('products/variant-data/{id}', 'variantData');
        Route::get('products/history', 'history')->name('products.history');
        Route::post('products/sale-history-data', 'saleHistoryData');
        Route::post('products/purchase-history-data', 'purchaseHistoryData');
        Route::post('products/sale-return-history-data', 'saleReturnHistoryData');
        Route::post('products/purchase-return-history-data', 'purchaseReturnHistoryData');
        Route::post('products/adjustment-history-data', 'adjustmentHistoryData');
        Route::post('products/transfer-history-data', 'transferHistoryData');

        Route::post('importproduct', 'importProduct')->name('product.import');
        Route::post('exportproduct', 'exportProduct')->name('product.export');
        Route::get('products/all-product-in-stock', 'allProductInStock')->name('product.allProductInStock');
        Route::get('products/show-all-product-online', 'showAllProductOnline')->name('product.showAllProductOnline');
        Route::get('check-batch-availability/{product_id}/{batch_no}/{warehouse_id}', 'checkBatchAvailability');
        Route::get('product-price/{id}', 'getProductPrice');
    });


    Route::get('language_switch/{id}', [LanguageController::class, 'switchLanguage']);

    Route::resource('role', RoleController::class);
    Route::controller(RoleController::class)->group(function () {
        Route::get('role/permission/{id}', 'permission')->name('role.permission');
        Route::post('role/set_permission', 'setPermission')->name('role.setPermission');
    });

    //Sms Template
    Route::resource('smstemplates', SmsTemplateController::class);
    Route::resource('unit', UnitController::class);
    Route::controller(UnitController::class)->group(function () {
        Route::post('importunit', 'importUnit')->name('unit.import');
        Route::post('unit/deletebyselection', 'deleteBySelection');
        Route::get('unit/lims_unit_search', 'limsUnitSearch')->name('unit.search');
    });

    Route::controller(CategoryController::class)->group(function () {
        Route::post('category/import', 'import')->name('category.import');
        Route::post('category/deletebyselection', 'deleteBySelection');
        Route::post('category/category-data', 'categoryData');
    });
    Route::resource('category', CategoryController::class);


    Route::controller(BrandController::class)->group(function () {
        Route::post('importbrand', 'importBrand')->name('brand.import');
        Route::post('brand/deletebyselection', 'deleteBySelection');
        Route::get('brand/lims_brand_search', 'limsBrandSearch')->name('brand.search');
    });
    Route::resource('brand', BrandController::class);


    Route::controller(SupplierController::class)->group(function () {
        Route::post('importsupplier', 'importSupplier')->name('supplier.import');
        Route::post('supplier/deletebyselection', 'deleteBySelection');
        Route::post('suppliers/clear-due', 'clearDue')->name('supplier.clearDue');
        Route::get('suppliers/all', 'suppliersAll')->name('supplier.all');
        Route::get('suppliers/ledger/{id}', 'ledger')->name('suppliers.ledger');
        Route::get('supplier-due/{id}', 'supplierDue')->name('supplier.due');
        Route::get('suppliers/{supplier_id}', 'supplierPayments')->name('suppliers.payments');
    });
    Route::resource('supplier', SupplierController::class);


    Route::controller(WarehouseController::class)->group(function () {
        Route::get('warehouse-data', [WarehouseController::class, 'warehouseData'])->name('warehouse.data');
        Route::post('importwarehouse', 'importWarehouse')->name('warehouse.import');
        Route::post('warehouse/deletebyselection', 'deleteBySelection');
        Route::get('warehouse/lims_warehouse_search', 'limsWarehouseSearch')->name('warehouse.search');
        Route::get('warehouse/all', 'warehouseAll')->name('warehouse.all');
    });
    Route::resource('warehouse', WarehouseController::class);

    Route::resource('printers', PrinterController::class);

    Route::resource('tables', TableController::class);


    Route::controller(TaxController::class)->group(function () {
        Route::post('importtax', 'importTax')->name('tax.import');
        Route::post('tax/deletebyselection', 'deleteBySelection');
        Route::get('tax/lims_tax_search', 'limsTaxSearch')->name('tax.search');
    });
    Route::resource('tax', TaxController::class);


    Route::controller(CustomerGroupController::class)->group(function () {
        Route::post('importcustomer_group', 'importCustomerGroup')->name('customer_group.import');
        Route::post('customer_group/deletebyselection', 'deleteBySelection');
        Route::get('customer_group/lims_customer_group_search', 'limsCustomerGroupSearch')->name('customer_group.search');
        Route::get('customer_group/all', 'customerGroupAll')->name('customer_group.all');
    });
    Route::resource('customer_group', CustomerGroupController::class);


    Route::resource('discount-plans', DiscountPlanController::class);
    Route::resource('discounts', DiscountController::class);
    Route::get('discounts/product-search/{code}', [DiscountController::class, 'productSearch']);


    Route::controller(CustomerController::class)->group(function () {
        Route::post('importcustomer', 'importCustomer')->name('customer.import');
        Route::post('customer/deletebyselection', 'deleteBySelection');
        Route::get('customer/lims_customer_search', 'limsCustomerSearch')->name('customer.search');
        Route::post('customers/clear-due', 'clearDue')->name('customer.clearDue');
        Route::post('customers/customer-data', 'customerData');
        Route::get('customers/all', 'customersAll')->name('customer.all');

        // customer deposit route
        Route::get('customer/getDeposit/{id}', 'getDeposit');
        Route::post('customer/add_deposit', 'addDeposit')->name('customer.addDeposit');
        Route::post('customer/update_deposit', 'updateDeposit')->name('customer.updateDeposit');
        Route::post('customer/deleteDeposit', 'deleteDeposit')->name('customer.deleteDeposit');

        //customer points route
        Route::post('customer/deletePoints', 'deletePoints')->name('customer.deletePoints');
        Route::post('customer/add-point', 'addPoint')->name('customer.addPoint');
        Route::get('customer/getPoints/{id}', 'getPoints');
        Route::post('customer/update_point', 'updatePoint')->name('customer.updatePoint');
        Route::get('customers/{customer_id}', 'customerPayments')->name('customers.payments');
        Route::get('customers/ledger/{id}', 'ledger')->name('customers.ledger');
        Route::get('customers/installments/{id}', 'installments')->name('customers.installments');
        Route::get('/customer/{id}/due','getCustomerDue');
    });

    Route::resource('customer', CustomerController::class)->where(['customer' => '[0-9]+']);


    Route::controller(BillerController::class)->group(function () {
        Route::post('importbiller', 'importBiller')->name('biller.import');
        Route::post('biller/deletebyselection', 'deleteBySelection');
        Route::get('biller/lims_biller_search', 'limsBillerSearch')->name('biller.search');
    });
    Route::resource('biller', BillerController::class);


    Route::controller(SaleController::class)->group(function () {
        Route::post('sales/sale-data', 'saleData');
        Route::post('sales/sendmail', 'sendMail')->name('sale.sendmail');
        Route::get('sales/sale_by_csv', 'saleByCsv')->middleware('permission:sales-import');
        Route::get('sales/deleted_data', 'showDeletedSales')
            ->middleware('hasPermanentDeletePermission');
        Route::delete('sales/force-delete-selected', 'forceDeleteSelected')
            ->name('sales.forceDeleteSelected')
            ->middleware('hasPermanentDeletePermission');
        Route::get('sales/product_sale/{id}', 'productSaleData');
        Route::get('sales/get-sale/{id}', 'getSale');
        Route::post('importsale', 'importSale')->name('sale.import');
        Route::get('pos/{id?}', 'posSale')->name('sale.pos');
        Route::get('sales/recent-sale', 'recentSale');
        Route::get('sales/recent-draft', 'recentDraft');
        Route::get('sales/lims_sale_search', 'limsSaleSearch')->name('sale.search');
        Route::get('sales/lims_product_search', 'limsProductSearch')->name('product_sale.search');
        Route::get('sales/offline_products/{warehouse_id}', 'offlineProductsData')->name('product_sale.offline_products');
        Route::get('sales/getcustomergroup/{id}', 'getCustomerGroup')->name('sale.getcustomergroup');
        Route::get('sales/customer-discounts/{id}', 'getCustomerDiscounts')->name('sale.customer-discounts');

        Route::get('sales/getproduct/{id}', 'getProduct')->name('sale.getproduct');

        Route::get('sales/getproducts/{warehouse_id}/{key}/{value}', 'getProducts');

        Route::get('sales/search', 'search');

        Route::get('sales/get_gift_card', 'getGiftCard');
        Route::get('sales/paypalSuccess', 'paypalSuccess');
        Route::get('sales/paypalPaymentSuccess/{id}', 'paypalPaymentSuccess');
        Route::get('sales/gen_invoice/{id}', 'genInvoice')->name('sale.invoice');
        Route::post('sales/add_payment', 'addPayment')->name('sale.add-payment');
        Route::get('sales/getpayment/{id}', 'getPayment')->name('sale.get-payment');
        Route::get('sales/payment-receipt/{id}', 'paymentReceipt')->name('sale.payment-receipt');
        Route::post('sales/updatepayment', 'updatePayment')->name('sale.update-payment');
        Route::post('sales/deletepayment', 'deletePayment')->name('sale.delete-payment');
        Route::get('sales/{id}/create', 'createSale')->name('sale.draft');
        Route::post('sales/deletebyselection', 'deleteBySelection');
        Route::get('customer-display', 'customerDisplay')->name('sales.customerDisplay');
        Route::get('sales/print-last-reciept', 'printLastReciept')->name('sales.printLastReciept');
        Route::get('sales/today-sale', 'todaySale');
        Route::get('sales/today-profit/{warehouse_id}', 'todayProfit');
        Route::get('sales/check-discount', 'checkDiscount');
        Route::get('sales/get-sold-items/{id}', 'getSoldItem');
        Route::post('sales/sendsms', 'sendSMS')->name('sale.sendsms');
        Route::post('sales/whatsapp-notification', 'whatsappNotificationSend')->name('sale.wappnotification');
        Route::get('customer-sales/{customer_id}', 'customerSales')->name('sales.customer');

        Route::post('sales/set-price-type', 'setPriceType')->name('set.price.type');
    });
    Route::resource('sales', SaleController::class)->except('show');

    Route::controller(InstallmentPlanController::class)->group(function () {
        Route::get('/installmentplan', 'index')->name('installmentplan.index');
        Route::get('/installmentplan/{id}', 'show')->name('installmentplan.show');
        Route::get('/report/installment', 'report')->name('report.installment');
    });

    Route::post('/razorpay/pay', [RazorpayController::class, 'createOrder']);
    Route::post('/razorpay/verify', [RazorpayController::class, 'verifyPayment']);

    // M-Pesa Dynamic QR Code (called from POS — requires auth)
    Route::post('/payment/mpesa/generate-qr', [PaymentGatewayController::class, 'generateQr'])->name('mpesa.generateQr');

    // MTN MoMo USSD QR Code (called from POS — requires auth)
    Route::post('/payment/mtnmomo/generate-qr', [PaymentGatewayController::class, 'generateMtnQr'])->name('mtnmomo.generateQr');

    // (Payment Gateways moved to public routes at the bottom)
    Route::controller(PackingSlipController::class)->group(function () {
        Route::prefix('packing-slips')->group(function () {
            Route::get('/', 'index')->name('packingSlip.index');
            Route::post('packing-slip-data', 'packingSlipData');
            Route::post('store', 'store')->name('packingSlip.store');
            Route::post('delete/{id}', 'delete')->name('packingSlip.delete');
            Route::get('invoice/{id}', 'genInvoice')->name('packingSlip.genInvoice');
        });
    });

    Route::controller(ChallanController::class)->group(function () {
        Route::prefix('challans')->group(function () {
            Route::get('/', 'index')->name('challan.index');
            Route::post('challan-data', 'challanData');
            Route::post('create', 'create')->name('challan.create');
            Route::post('store', 'store')->name('challan.store');
            Route::get('invoice/{id}', 'genInvoice')->name('challan.genInvoice');
            Route::get('money-reciept/{id}', 'moneyReciept')->name('challan.moneyReciept');
            Route::get('finalize/{id}', 'finalize')->name('challan.finalize');
            Route::post('update/{id}', 'update')->name('challan.update');
            Route::post('add-payment', 'addPayment')->name('challan.add-payment');
            Route::get('get-packing-slips/{id}', 'getPackingSlips')->name('challan.getPackingSlips');
        });
    });

    Route::controller(DeliveryController::class)->group(function () {
        Route::prefix('delivery')->group(function () {
            Route::get('/', 'index')->name('delivery.index');
            Route::get('delivery_list_data', 'deliveryListData');
            Route::get('product_delivery/{id}', 'productDeliveryData');
            Route::get('create/{id}', 'create');
            Route::post('store', 'store')->name('delivery.store');
            Route::post('sendmail', 'sendMail')->name('delivery.sendMail');
            Route::get('{id}/edit', 'edit');
            Route::post('update', 'update')->name('delivery.update');
            Route::post('deletebyselection', 'deleteBySelection');
            Route::post('delete/{id}', 'delete')->name('delivery.delete');
            Route::get('{id}/track','track')->name('delivery.track');
            Route::post('{id}/send-to-pathao', 'sendToPathao')->name('delivery.sendToPathao');

        });
    });

    Route::controller(SteadFastController::class)->group(function () {
        Route::get('/delivery/steadfast/{sale_id}', 'getSaleForSteadFast');
        Route::post('/steadfast/create-order', 'store')->name('steadfast.create-order');
        Route::get('/steadfast/{sale_id}', 'show')->name('steadfast.track');
    });


    Route::controller(QuotationController::class)->group(function () {
        Route::prefix('quotations')->group(function () {
            Route::post('quotation-data', 'quotationData')->name('quotations.data');
            Route::get('product_quotation/{id}', 'productQuotationData');
            Route::get('lims_product_search', 'limsProductSearch')->name('product_quotation.search');
            Route::get('getcustomergroup/{id}', 'getCustomerGroup')->name('quotation.getcustomergroup');
            Route::get('getproduct/{id}', 'getProduct')->name('quotation.getproduct');
            Route::get('{id}/create_sale', 'createSale')->name('quotation.create_sale');
            Route::get('{id}/create_purchase', 'createPurchase')->name('quotation.create_purchase');
            Route::post('sendmail', 'sendMail')->name('quotation.sendmail');
            Route::post('deletebyselection', 'deleteBySelection');
            Route::get('invoice/{id}','genInvoice')->name('quotation.invoice');

        });
    });
    Route::resource('quotations', QuotationController::class);


    Route::controller(PurchaseController::class)->group(function () {
        Route::prefix('purchases')->group(function () {
            Route::post('purchase-data', 'purchaseData')->name('purchases.data');
            Route::get('product_purchase/{id}', 'productPurchaseData');
            Route::get('lims_product_search', 'limsProductSearch')->name('product_purchase.search');
            Route::post('add_payment', 'addPayment')->name('purchase.add-payment');
            Route::get('getpayment/{id}', 'getPayment')->name('purchase.get-payment');
            Route::post('updatepayment', 'updatePayment')->name('purchase.update-payment');
            Route::post('deletepayment', 'deletePayment')->name('purchase.delete-payment');
            Route::get('purchase_by_csv', 'purchaseByCsv')->middleware('permission:purchases-import');
            Route::get('deleted_data', 'showDeletedPurchases')
                ->middleware('hasPermanentDeletePermission');
            Route::get('duplicate/{id}', 'duplicate')->name('purchase.duplicate');
            Route::post('deletebyselection', 'deleteBySelection');
            Route::delete('force-delete-selected', 'forceDeleteSelected')
                ->name('purchases.forceDeleteSelected')
                ->middleware('hasPermanentDeletePermission');
            Route::get('supplier/{supplier_id}', 'supplierPurchase')->name('purchase.supplier');
        });
        Route::post('importpurchase', 'importPurchase')->name('purchase.import');
    });
    Route::resource('purchases', PurchaseController::class);

    Route::controller(PurchaseRequestController::class)->group(function () {
        Route::prefix('purchase_requests')->group(function () {
            Route::get('gen_invoice/{id}', 'genInvoice')->name('purchase_requests.invoice');
            Route::get('{id}/create_purchase', 'createPurchase')->name('purchase_requests.create_purchase');
        });
    });
    Route::resource('purchase_requests', PurchaseRequestController::class);



    Route::controller(TransferController::class)->group(function () {
        Route::prefix('transfers')->group(function () {
            Route::post('transfer-data', 'transferData')->name('transfers.data');
            Route::get('product_transfer/{id}', 'productTransferData');
            Route::get('transfer_by_csv', 'transferByCsv')->middleware('permission:transfers-import');
            Route::get('getproduct/{id}', 'getProduct')->name('transfers.getproduct');
            Route::put('change-status/{id}', 'changeStatus')->name('transfers.changeStatus');
            Route::get('lims_product_search', 'limsProductSearch')->name('product_transfer.search');
            Route::post('deletebyselection', 'deleteBySelection');
        });
        Route::post('importtransfer', 'importTransfer')->name('transfer.import');
    });
    Route::resource('transfers', TransferController::class);



    Route::controller(AdjustmentController::class)->group(function () {
        Route::get('qty_adjustment/getproduct/{id}', 'getProduct')->name('adjustment.getproduct');
        Route::get('qty_adjustment/lims_product_search', 'limsProductSearch')->name('product_adjustment.search');
        Route::post('qty_adjustment/deletebyselection', 'deleteBySelection');
    });
    Route::resource('qty_adjustment', AdjustmentController::class);


    Route::controller(ReturnController::class)->group(function () {
        Route::prefix('return-sale')->group(function () {
            Route::post('return-data', 'returnData');
            Route::get('getcustomergroup/{id}', 'getCustomerGroup')->name('return-sale.getcustomergroup');
            Route::post('sendmail', 'sendMail')->name('return-sale.sendmail');
            Route::get('getproduct/{id}', 'getProduct')->name('return-sale.getproduct');
            Route::get('lims_product_search', 'limsProductSearch')->name('product_return-sale.search');
            Route::get('product_return/{id}', 'productReturnData');
            Route::post('deletebyselection', 'deleteBySelection');
        });
    });
    Route::resource('return-sale', ReturnController::class);

    // Replace your existing exchange routes with these:

    Route::controller(ExchangeController::class)->prefix('exchange')->group(function () {
        Route::post('exchange-data', 'exchangeData')->name('exchange.data');
        Route::get('getcustomergroup/{id}', 'getCustomerGroup')->name('exchange.getcustomergroup');
        Route::post('sendmail', 'sendMail')->name('exchange.sendmail');
        Route::get('getproduct/{id}', 'getProduct')->name('exchange.getproduct');
        Route::get('lims_product_search', 'limsProductSearch')->name('exchange.lims_product_search');
        // FIXED: Changed from exchangeData to productExchange
        Route::get('product_exchange/{id}', 'productExchange')->name('exchange.product_exchange');
        Route::post('deletebyselection', 'deleteBySelection')->name('exchange.deletebyselection');
    });

    Route::resource('exchange', ExchangeController::class);
    Route::get('/sale-exchange/search', [ExchangeController::class, 'searchByReference'])
        ->name('sale.exchange.search');

    Route::controller(ReturnPurchaseController::class)->group(function () {
        Route::prefix('return-purchase')->group(function () {
            Route::post('return-data', 'returnData');
            Route::get('getcustomergroup/{id}', 'getCustomerGroup')->name('return-purchase.getcustomergroup');
            Route::post('sendmail', 'sendMail')->name('return-purchase.sendmail');
            Route::get('getproduct/{id}', 'getProduct')->name('return-purchase.getproduct');
            Route::get('lims_product_search', 'limsProductSearch')->name('product_return-purchase.search');
            Route::get('product_return/{id}', 'productReturnData');
            Route::post('deletebyselection', 'deleteBySelection');
        });
    });

    Route::resource('return-purchase', ReturnPurchaseController::class);

    Route::controller(ReportController::class)->group(function () {
        Route::prefix('report')->group(function () {
            Route::get('product_quantity_alert', 'productQuantityAlert')->name('report.qtyAlert');
            Route::get('daily-sale-objective', 'dailySaleObjective')->name('report.dailySaleObjective');
            Route::post('daily-sale-objective-data', 'dailySaleObjectiveData');
            Route::get('product-expiry', 'productExpiry')->name('report.productExpiry');
            Route::get('warehouse_stock', 'warehouseStock')->name('report.warehouseStock');
            Route::get('daily_sale/{year}/{month}', 'dailySale');
            Route::post('daily_sale/{year}/{month}', 'dailySaleByWarehouse')->name('report.dailySaleByWarehouse');
            Route::get('monthly_sale/{year}', 'monthlySale');
            Route::post('monthly_sale/{year}', 'monthlySaleByWarehouse')->name('report.monthlySaleByWarehouse');
            Route::get('daily_purchase/{year}/{month}', 'dailyPurchase');
            Route::post('daily_purchase/{year}/{month}', 'dailyPurchaseByWarehouse')->name('report.dailyPurchaseByWarehouse');
            Route::get('monthly_purchase/{year}', 'monthlyPurchase');
            Route::post('monthly_purchase/{year}', 'monthlyPurchaseByWarehouse')->name('report.monthlyPurchaseByWarehouse');
            Route::get('best_seller', 'bestSeller');
            Route::post('best_seller', 'bestSellerByWarehouse')->name('report.bestSellerByWarehouse');
            Route::get('profit-loss', 'summary')->name('report.profitLossSummary');
            Route::post('profit-loss', 'profitLoss')->name('report.profitLoss');
            Route::get('product_report', 'productReport')->name('report.product');
            Route::post('product_report_data', 'productReportData');
            Route::post('purchase', 'purchaseReport')->name('report.purchase');
            Route::post('purchase_report_data', 'purchaseReportData');
            Route::post('sale_report', 'saleReport')->name('report.sale');
            Route::post('sale_report_data', 'saleReportData');
            Route::get('stock', 'stockReport')->name('report.stock');
            Route::post('stock-data', 'stockReportData')->name('report.stock-data');
            Route::get('challan-report', 'challanReport')->name('report.challan');
            Route::post('sale-report-chart', 'saleReportChart')->name('report.saleChart');
            Route::post('payment_report_by_date', 'paymentReportByDate')->name('report.paymentByDate');
            Route::post('warehouse_report', 'warehouseReport')->name('report.warehouse');
            Route::post('warehouse-sale-data', 'warehouseSaleData');
            Route::post('warehouse-purchase-data', 'warehousePurchaseData');
            Route::post('warehouse-expense-data', 'warehouseExpenseData');
            Route::post('warehouse-quotation-data', 'warehouseQuotationData');
            Route::post('warehouse-return-data', 'warehouseReturnData');
            Route::post('user_report', 'userReport')->name('report.user');
            Route::post('user-sale-data', 'userSaleData');
            Route::post('user-purchase-data', 'userPurchaseData');
            Route::post('user-expense-data', 'userExpenseData');
            Route::post('user-quotation-data', 'userQuotationData');
            Route::post('user-payment-data', 'userPaymentData');
            Route::post('user-transfer-data', 'userTransferData');
            Route::post('user-payroll-data', 'userPayrollData');
            Route::post('biller_report', 'billerReport')->name('report.biller');
            Route::post('biller-sale-data', 'billerSaleData');
            Route::post('biller-quotation-data', 'billerQuotationData');
            Route::post('biller-payment-data', 'billerPaymentData');
            Route::post('customer_report', 'customerReport')->name('report.customer');
            Route::post('customer-sale-data', 'customerSaleData');
            Route::post('customer-payment-data', 'customerPaymentData');
            Route::post('customer-quotation-data', 'customerQuotationData');
            Route::post('customer-return-data', 'customerReturnData');
            Route::post('customer-group', 'customerGroupReport')->name('report.customer_group');
            Route::post('customer-group-sale-data', 'customerGroupSaleData');
            Route::post('customer-group-payment-data', 'customerGroupPaymentData');
            Route::post('customer-group-quotation-data', 'customerGroupQuotationData');
            Route::post('customer-group-return-data', 'customerGroupReturnData');
            Route::post('supplier', 'supplierReport')->name('report.supplier');
            Route::post('supplier-purchase-data', 'supplierPurchaseData');
            Route::post('supplier-payment-data', 'supplierPaymentData');
            Route::post('supplier-return-data', 'supplierReturnData');
            Route::post('supplier-quotation-data', 'supplierQuotationData');
            Route::post('customer-due-report', 'customerDueReportByDate')->name('report.customerDueByDate');
            Route::post('customer-due-report-data', 'customerDueReportData');
            Route::post('supplier-due-report', 'supplierDueReportByDate')->name('report.supplierDueByDate');
            Route::post('supplier-due-report-data', 'supplierDueReportData');
        });
    });

    Route::controller(\App\Http\Controllers\AccountingActivationController::class)->group(function () {
        Route::prefix('accounting/activation')->group(function () {
            Route::get('/', 'index')->name('accounting.activation.index');
            Route::post('/', 'activate')->name('accounting.activation.activate');
            Route::post('/reset', 'reset')->name('accounting.activation.reset');
        });
    });

    Route::middleware('accounting.activated')->group(function () {
        Route::controller(\App\Http\Controllers\AccountingReportController::class)->group(function () {
            Route::prefix('accounting')->group(function () {
                Route::get('trial-balance', 'trialBalance')->name('accounting.trialBalance');
                Route::get('general-ledger', 'generalLedger')->name('accounting.generalLedger');
                Route::get('balance-sheet', 'balanceSheet')->name('accounting.balanceSheet');
                Route::get('profit-loss', 'profitAndLoss')->name('accounting.profitAndLoss');
                Route::get('cash-flow-statement', 'cashFlowStatement')->name('accounting.cashFlowStatement');
            });
        });
    });


    Route::controller(UserController::class)->group(function () {
        Route::get('user/profile/{id}', 'profile')->name('user.profile');
        Route::put('user/update_profile/{id}', 'profileUpdate')->name('user.profileUpdate');
        Route::put('user/changepass/{id}', 'changePassword')->name('user.password');
        Route::get('user/genpass', 'generatePassword');
        Route::post('user/deletebyselection', 'deleteBySelection');
        Route::get('user/notification', 'notificationUsers')->name('user.notification');
        Route::get('user/all', 'allUsers')->name('user.all');
        Route::post('user/toggle-status', [UserController::class, 'toggleStatus'])->name('user.toggleStatus');
    });
    Route::resource('user', UserController::class);


    Route::controller(SettingController::class)->group(function () {
        Route::prefix('setting')->group(function () {
            Route::get('activity-log', 'activityLog')->name('setting.activityLog');
            Route::get('general_setting', 'generalSetting')->name('setting.general');
            Route::post('general_setting_store', 'generalSettingStore')->name('setting.generalStore');

            Route::get('app_setting', 'appSetting')->name('setting.app');
            Route::delete('app_setting/{id}', 'appSettingDelete')->name('setting.tokenDelete');

            Route::get('reward-point-setting', 'rewardPointSetting')->name('setting.rewardPoint');
            Route::post('reward-point-setting_store', 'rewardPointSettingStore')->name('setting.rewardPointStore');

            Route::get('general_setting/change-theme/{theme}', 'changeTheme');
            Route::get('mail_setting', 'mailSetting')->name('setting.mail');
            Route::get('sms_setting', 'smsSetting')->name('setting.sms');
            Route::get('createsms', 'createSms')->name('setting.createSms');
            Route::post('sendsms', 'sendSMS')->name('setting.sendSms');
            Route::get('payment-gateways/list', 'gateway')->name('setting.gateway');
            Route::post('payment-gateways/update', 'gatewayUpdate')->name('setting.gateway.update');
            Route::get('hrm_setting', 'hrmSetting')->name('setting.hrm');
            Route::post('hrm_setting_store', 'hrmSettingStore')->name('setting.hrmStore');
            Route::post('mail_setting_store', 'mailSettingStore')->name('setting.mailStore');
            Route::post('sms_setting_store', 'smsSettingStore')->name('setting.smsStore');
            Route::post('test_custom_http_sms', 'testCustomHttpSms')->name('setting.testCustomHttpSms');
            Route::get('pos_setting', 'posSetting')->name('setting.pos');
            Route::post('pos_setting_store', 'posSettingStore')->name('setting.posStore');
            Route::get('empty-database', 'emptyDatabase')->name('setting.emptyDatabase');
        });
        Route::get('backup', 'backup')->name('setting.backup');
    });

    Route::prefix('setting')->name('settings.')->group(function () {
        Route::resource('invoice', InvoiceSettingController::class);
    });

    Route::prefix('setting')->name('setting.')->group(function () {
        Route::get('theme-settings', [ThemeSettingController::class, 'index'])->name('themeSettings.index');
        Route::get('theme-settings/create', [ThemeSettingController::class, 'create'])->name('themeSettings.create');
        Route::get('theme-settings/palette', [ThemeSettingController::class, 'palette'])->name('themeSettings.palette');
        Route::post('theme-settings', [ThemeSettingController::class, 'store'])->name('themeSettings.store');
        Route::get('theme-settings/{themeSetting}/edit', [ThemeSettingController::class, 'edit'])->name('themeSettings.edit');
        Route::put('theme-settings/{themeSetting}', [ThemeSettingController::class, 'update'])->name('themeSettings.update');
        Route::delete('theme-settings/{themeSetting}', [ThemeSettingController::class, 'destroy'])->name('themeSettings.destroy');
        Route::put('theme-settings/{themeSetting}/active-for', [ThemeSettingController::class, 'updateActiveFor'])->name('themeSettings.activeFor');
    });

    Route::get('/barcodes/set_default/{id}', [BarcodeController::class, 'setDefault']);
    Route::controller(BarcodeController::class)->group(function () {
        Route::post('barcodes/barcode-data', 'barcodeData')->name('barcodes.data');
    });
    Route::resource('barcodes', BarcodeController::class);


    Route::get('/labels/show', [LabelsController::class, 'show'])->name('print.labels');
    Route::get('/labels/add-product-row', [LabelsController::class, 'addProductRow']);
    Route::get('/labels/print', [LabelsController::class, 'printLabel'])->name('print.label');

    Route::controller(ExpenseCategoryController::class)->group(function () {
        Route::get('expense_categories/gencode', 'generateCode');
        Route::post('expense_categories/import', 'import')->name('expense_category.import');
        Route::post('expense_categories/deletebyselection', 'deleteBySelection');
        Route::get('expense_categories/all', 'expenseCategoriesAll')->name('expense_category.all');;
    });
    Route::resource('expense_categories', ExpenseCategoryController::class);


    Route::controller(ExpenseController::class)->group(function () {
        Route::post('expenses/expense-data', 'expenseData')->name('expenses.data');
        Route::post('expenses/deletebyselection', 'deleteBySelection');
    });
    Route::resource('expenses', ExpenseController::class);

    // IncomeCategory & Income Start
    Route::controller(IncomeCategoryController::class)->group(function () {
        Route::get('income_categories/gencode', 'generateCode');
        Route::post('income_categories/import', 'import')->name('income_category.import');
        Route::post('income_categories/deletebyselection', 'deleteBySelection');
        Route::get('income_categories/all', 'incomeCategoriesAll')->name('income_category.all');;
    });
    Route::resource('income_categories', IncomeCategoryController::class);


    Route::controller(IncomeController::class)->group(function () {
        Route::post('incomes/income-data', 'incomeData')->name('incomes.data');
        Route::post('incomes/deletebyselection', 'deleteBySelection');
    });
    Route::resource('incomes', IncomeController::class);
    // IncomeCategory & Income End


    Route::controller(GiftCardController::class)->group(function () {
        Route::get('gift_cards/gencode', 'generateCode');
        Route::post('gift_cards/recharge/{id}', 'recharge')->name('gift_cards.recharge');
        Route::post('gift_cards/deletebyselection', 'deleteBySelection');
    });
    Route::resource('gift_cards', GiftCardController::class);

    Route::resource('couriers', CourierController::class);

    Route::controller(CouponController::class)->group(function () {
        Route::get('coupons/gencode', 'generateCode');
        Route::post('coupons/deletebyselection', 'deleteBySelection');
    });
    Route::resource('coupons', CouponController::class);

    Route::get('phpfileinfo', function () {
        phpinfo();
    })->name('phpfileinfo');


    //accounting routes
    Route::middleware('accounting.activated')->group(function () {
        Route::controller(AccountsController::class)->group(function () {
            Route::get('make-default/{id}', 'makeDefault');
            Route::get('balancesheet', 'balanceSheet')->name('accounts.balancesheet');
            Route::post('account-statement', 'accountStatement')->name('accounts.statement');
            Route::get('accounts/all', 'accountsAll')->name('account.all');
        });
        Route::resource('accounts', AccountsController::class);
    });

    Route::middleware('accounting.activated')->group(function () {
        Route::controller(\App\Http\Controllers\AccountingReconciliationController::class)->group(function () {
            Route::get('accounting/reconciliation', 'index')->name('accounting.reconciliation.index');
            Route::post('accounting/reconciliation/retry/{id}', 'retry')->name('accounting.reconciliation.retry');
        });
    });


    Route::resource('money-transfers', MoneyTransferController::class);


    //HRM routes
    Route::post('departments/deletebyselection', [DepartmentController::class, 'deleteBySelection']);
    Route::resource('departments', DepartmentController::class);
    Route::resource('designations', DesignationController::class);
    Route::resource('shift', ShiftController::class);
    Route::resource('overtime', OvertimeController::class);
    Route::resource('leave-type', LeaveTypeController::class);
    Route::resource('leave', LeaveController::class);
    Route::get('hrm-panel', [HrmController::class, 'index'])->name('hrm-panel');
    Route::resource('sale-agents', SaleAgentController::class)->except('show');
    Route::get('/payroll/monthly-data', [PayrollController::class, 'monthlyData'])->name('payroll.monthlyData');
    Route::get('payroll/get-employees-by-warehouse', [PayrollController::class, 'getEmployeesByWarehouse'])->name('payroll.getEmployeesByWarehouse');
    Route::post('payroll/store-multiple', [PayrollController::class, 'storeMultiple'])->name('payroll.storeMultiple');
    Route::post('payroll/generate', [PayrollController::class, 'generateCards'])->name('payroll.generateCards');





    Route::post('employees/deletebyselection', [EmployeeController::class, 'deleteBySelection']);
    Route::resource('employees', EmployeeController::class);


    Route::post('payroll/deletebyselection', [PayrollController::class, 'deleteBySelection']);
    Route::resource('payroll', PayrollController::class);


    Route::post('attendance/delete/{date}/{employee_id}', [AttendanceController::class, 'delete'])->name('attendances.delete');
    Route::post('attendance/deletebyselection', [AttendanceController::class, 'deleteBySelection']);
    Route::post('attendance/importDeviceCsv', [AttendanceController::class, 'importDeviceCsv'])->name('attendances.importDeviceCsv');
    Route::resource('attendance', AttendanceController::class);

    Route::controller(StockCountController::class)->group(function () {
        Route::post('stock-count/finalize', 'finalize')->name('stock-count.finalize');
        Route::get('stock-count/stockdif/{id}', 'stockDif');
        Route::get('stock-count/{id}/qty_adjustment', 'qtyAdjustment')->name('stock-count.adjustment');
    });
    Route::resource('stock-count', StockCountController::class);


    Route::controller(HolidayController::class)->group(function () {
        Route::post('holidays/deletebyselection', 'deleteBySelection');
        Route::get('approve-holiday/{id}', 'approveHoliday')->name('approveHoliday');
        Route::get('holidays/my-holiday/{year}/{month}', 'myHoliday')->name('myHoliday');
    });
    Route::resource('holidays', HolidayController::class);


    Route::controller(CashRegisterController::class)->group(function () {
        Route::prefix('cash-register')->group(function () {
            Route::get('/', 'index')->name('cashRegister.index');
            Route::get('check-availability/{warehouse_id}', 'checkAvailability')->name('cashRegister.checkAvailability');
            Route::post('store', 'store')->name('cashRegister.store');
            Route::get('getDetails/{id}', 'getDetails');
            Route::post('close', 'close')->name('cashRegister.close');
        });
    });


    Route::controller(NotificationController::class)->group(function () {
        Route::prefix('notifications')->group(function () {
            Route::get('/', 'index')->name('notifications.index');
            Route::post('store', 'store')->name('notifications.store');
            Route::get('mark-as-read', 'markAsRead');
            // Added for Settings Matrix
            Route::get('settings', 'settings')->name('notifications.settings');
            Route::post('settings', 'updateSettings')->name('notifications.settings.update');
        });
    });


    Route::resource('currency', CurrencyController::class);

    Route::resource('custom-fields', CustomFieldController::class);

    Route::controller(AddonInstallController::class)->group(function () {
        Route::post('saas-install', 'saasInstall')->name('saas.install');
        Route::post('ecommerce-install', 'ecommerceInstall')->name('ecommerce.install');
        Route::post('woocommerce-install', 'woocommerceInstall')->name('woocommerce.install');
        Route::post('api-install', 'apiInstall')->name('api.install');
    });

    Route::prefix('whatsapp')->group(function () {
        Route::get('/settings', [WhatsappController::class, 'settings'])->name('whatsapp.settings');
        Route::post('/settings', [WhatsappController::class, 'updateSettings'])->name('whatsapp.settings.update');

        Route::get('/templates', [WhatsappController::class, 'templates'])->name('whatsapp.templates');
        Route::delete('/template/delete/{name}', [WhatsappController::class, 'deleteTemplate'])->name('whatsapp.template.delete');

        Route::get('/send', [WhatsappController::class, 'sendPage'])->name('whatsapp.send.page');
        Route::post('/send', [WhatsappController::class, 'sendMessage'])->name('whatsapp.send');
    });

    //ticket routes
    if (class_exists('App\Http\Controllers\landlord\TicketController')) {
        Route::controller(\App\Http\Controllers\landlord\TicketController::class)->group(function () {
            Route::get('tickets', 'index')->name('tickets.index');
            Route::get('tickets/create', 'create')->name('tickets.create');
            Route::post('tickets', 'store')->name('tickets.store');
            Route::get('tickets/{id}', 'show')->name('tickets.show');
            Route::post('tickets/{id}/reply', 'reply')->name('tickets.reply');
            Route::delete('tickets/{id}', 'destroy')->name('tickets.destroy');
        });
    }

    Route::controller(DamageStockController::class)->group(function () {
        Route::get('damage-stock/getproduct/{id}',       'getProduct')         ->name('damage-stock.getproduct');
        Route::get('damage-stock/lims_product_search',   'limsProductSearch')  ->name('damage-stock.search');
        Route::post('damage-stock/deletebyselection',    'deleteBySelection');
    });
    Route::resource('damage-stock', DamageStockController::class);

    // booking route..........
    Route::controller(BookingController::class)->group(function () {
        Route::get('bookings/calendar', 'index')->name('booking.index');
        Route::get('bookings/events', 'getEvents')->name('booking.events');
        Route::post('bookings/deletebyselection', 'deleteBySelection');
    });
    Route::resource('bookings', BookingController::class)->except(['create', 'edit']);

    // QR Code routes (Protected)
    Route::prefix('qr')->group(function () {
        Route::get('/', [QrCodeController::class, 'index'])->name('qr.index');
        Route::post('/generate/{type}/{id}', [QrCodeController::class, 'generate']);
        Route::get('/show/{id}', [QrCodeController::class, 'show']);
        Route::get('/download/{id}', [QrCodeController::class, 'download']);
        Route::post('/save-settings', [QrCodeController::class, 'saveSettings'])->name('qr.saveSettings');
    });

    // Delivery Management Module Routes
    Route::prefix('delivery-man')->name('delivery-man.')->group(function () {
        Route::get('login', [Modules\DeliveryManagement\Http\Controllers\DeliveryManAuthController::class, 'showLogin'])->name('login');
        Route::post('login', [Modules\DeliveryManagement\Http\Controllers\DeliveryManAuthController::class, 'login']);
        Route::middleware('delivery.man.auth')->group(function () {
            Route::get('dashboard', [Modules\DeliveryManagement\Http\Controllers\DeliveryManAuthController::class, 'dashboard'])->name('dashboard');
            Route::post('logout', [Modules\DeliveryManagement\Http\Controllers\DeliveryManAuthController::class, 'logout'])->name('logout');
        });
    });

    Route::prefix('delivery-men')->name('delivery-men.')->group(function () {
        Route::get('/', [Modules\DeliveryManagement\Http\Controllers\DeliveryManController::class, 'index'])->name('index');
        Route::get('create', [Modules\DeliveryManagement\Http\Controllers\DeliveryManController::class, 'create'])->name('create');
        Route::post('store', [Modules\DeliveryManagement\Http\Controllers\DeliveryManController::class, 'store'])->name('store');
        Route::get('{id}/edit', [Modules\DeliveryManagement\Http\Controllers\DeliveryManController::class, 'edit'])->name('edit');
        Route::post('update', [Modules\DeliveryManagement\Http\Controllers\DeliveryManController::class, 'update'])->name('update');
        Route::post('delete/{id}', [Modules\DeliveryManagement\Http\Controllers\DeliveryManController::class, 'delete'])->name('delete');
        Route::get('{id}', [Modules\DeliveryManagement\Http\Controllers\DeliveryManController::class, 'show'])->name('show');
        Route::post('toggle-status', [Modules\DeliveryManagement\Http\Controllers\DeliveryManController::class, 'toggleStatus'])->name('toggleStatus');
        Route::get('performance/{id}', [Modules\DeliveryManagement\Http\Controllers\DeliveryManController::class, 'performance'])->name('performance');
        Route::post('upload-photo', [Modules\DeliveryManagement\Http\Controllers\DeliveryManController::class, 'uploadPhoto'])->name('uploadPhoto');
        Route::post('delivery-man-data', [Modules\DeliveryManagement\Http\Controllers\DeliveryManController::class, 'deliveryManData'])->name('deliveryManData');
        Route::get('{id}/customers', [Modules\DeliveryManagement\Http\Controllers\DeliveryManController::class, 'assignedCustomers'])->name('assignedCustomers');
        Route::get('{delivery_man_id}/customers/{customer_id}/orders', [Modules\DeliveryManagement\Http\Controllers\DeliveryManController::class, 'customerOrderHistory'])->name('customerOrderHistory');
        Route::get('{delivery_man_id}/customers/{customer_id}/ledger', [Modules\DeliveryManagement\Http\Controllers\DeliveryManController::class, 'customerLedger'])->name('customerLedger');
        Route::post('{delivery_man_id}/customers/{customer_id}/collect-payment', [Modules\DeliveryManagement\Http\Controllers\DeliveryManController::class, 'collectDuePayment'])->name('collectDuePayment');
    });

    Route::prefix('delivery-man-assignments')->name('delivery-man-assignments.')->group(function () {
        Route::get('/', [Modules\DeliveryManagement\Http\Controllers\DeliveryManAssignmentController::class, 'index'])->name('index');
        Route::post('store', [Modules\DeliveryManagement\Http\Controllers\DeliveryManAssignmentController::class, 'store'])->name('store');
        Route::post('update/{id}', [Modules\DeliveryManagement\Http\Controllers\DeliveryManAssignmentController::class, 'update'])->name('update');
        Route::post('delete/{id}', [Modules\DeliveryManagement\Http\Controllers\DeliveryManAssignmentController::class, 'delete'])->name('delete');
        Route::get('delivery-men-by-warehouse/{warehouse_id}', [Modules\DeliveryManagement\Http\Controllers\DeliveryManAssignmentController::class, 'getDeliveryMenByWarehouse']);
        Route::get('delivery-men-by-route/{route_id}', [Modules\DeliveryManagement\Http\Controllers\DeliveryManAssignmentController::class, 'getDeliveryMenByRoute']);
        Route::get('delivery-men-by-area/{area_id}', [Modules\DeliveryManagement\Http\Controllers\DeliveryManAssignmentController::class, 'getDeliveryMenByArea']);
    });

    Route::prefix('delivery-man-routes')->name('delivery-man-routes.')->group(function () {
        Route::get('/', [Modules\DeliveryManagement\Http\Controllers\DeliveryManRouteController::class, 'index'])->name('index');
        Route::post('data', [Modules\DeliveryManagement\Http\Controllers\DeliveryManRouteController::class, 'routeData'])->name('data');
        Route::post('store', [Modules\DeliveryManagement\Http\Controllers\DeliveryManRouteController::class, 'store'])->name('store');
        Route::get('{id}/edit', [Modules\DeliveryManagement\Http\Controllers\DeliveryManRouteController::class, 'edit'])->name('edit');
        Route::post('update/{id}', [Modules\DeliveryManagement\Http\Controllers\DeliveryManRouteController::class, 'update'])->name('update');
        Route::post('delete/{id}', [Modules\DeliveryManagement\Http\Controllers\DeliveryManRouteController::class, 'delete'])->name('delete');
    });

    Route::prefix('delivery-man-vehicles')->name('delivery-man-vehicles.')->group(function () {
        Route::get('/', [Modules\DeliveryManagement\Http\Controllers\DeliveryManVehicleController::class, 'index'])->name('index');
        Route::post('store', [Modules\DeliveryManagement\Http\Controllers\DeliveryManVehicleController::class, 'store'])->name('store');
        Route::post('update/{id}', [Modules\DeliveryManagement\Http\Controllers\DeliveryManVehicleController::class, 'update'])->name('update');
        Route::post('delete/{id}', [Modules\DeliveryManagement\Http\Controllers\DeliveryManVehicleController::class, 'delete'])->name('delete');
    });

    Route::prefix('field-orders')->name('field-orders.')->group(function () {
        Route::get('/', [Modules\DeliveryManagement\Http\Controllers\FieldOrderController::class, 'index'])->name('index');
        Route::get('create', [Modules\DeliveryManagement\Http\Controllers\FieldOrderController::class, 'create'])->name('create');
        Route::post('store', [Modules\DeliveryManagement\Http\Controllers\FieldOrderController::class, 'store'])->name('store');
        Route::get('draft-list', [Modules\DeliveryManagement\Http\Controllers\FieldOrderController::class, 'draftList'])->name('draftList');
        Route::get('draft/{id}', [Modules\DeliveryManagement\Http\Controllers\FieldOrderController::class, 'loadDraft'])->name('loadDraft');
        Route::post('draft/{id}/update', [Modules\DeliveryManagement\Http\Controllers\FieldOrderController::class, 'updateDraft'])->name('updateDraft');
        Route::post('draft/{id}/delete', [Modules\DeliveryManagement\Http\Controllers\FieldOrderController::class, 'deleteDraft'])->name('deleteDraft');
        Route::get('{id}', [Modules\DeliveryManagement\Http\Controllers\FieldOrderController::class, 'show'])->name('show');
        Route::post('update/{id}', [Modules\DeliveryManagement\Http\Controllers\FieldOrderController::class, 'update'])->name('update');
        Route::post('cancel/{id}', [Modules\DeliveryManagement\Http\Controllers\FieldOrderController::class, 'cancel'])->name('cancel');
        Route::get('products/search', [Modules\DeliveryManagement\Http\Controllers\FieldOrderController::class, 'searchProducts'])->name('searchProducts');
        Route::get('customers/search', [Modules\DeliveryManagement\Http\Controllers\FieldOrderController::class, 'searchCustomers'])->name('searchCustomers');
        Route::post('customers/quick-create', [Modules\DeliveryManagement\Http\Controllers\FieldOrderController::class, 'quickCreateCustomer'])->name('quickCreateCustomer');
        Route::post('validate-stock', [Modules\DeliveryManagement\Http\Controllers\FieldOrderController::class, 'validateStock']);
        Route::get('invoice/{id}', [Modules\DeliveryManagement\Http\Controllers\FieldOrderController::class, 'genInvoice'])->name('invoice');
        Route::post('field-order-data', [Modules\DeliveryManagement\Http\Controllers\FieldOrderController::class, 'fieldOrderData'])->name('fieldOrderData');
        Route::post('send-whatsapp/{id}', [Modules\DeliveryManagement\Http\Controllers\FieldOrderController::class, 'sendWhatsApp'])->name('sendWhatsApp');
        Route::post('send-sms/{id}', [Modules\DeliveryManagement\Http\Controllers\FieldOrderController::class, 'sendSMS'])->name('sendSMS');
        Route::get('barcode/{id}', [Modules\DeliveryManagement\Http\Controllers\FieldOrderController::class, 'printBarcode'])->name('printBarcode');
    });

    Route::prefix('field-payments')->name('field-payments.')->group(function () {
        Route::get('/', [Modules\DeliveryManagement\Http\Controllers\FieldPaymentController::class, 'index'])->name('index');
        Route::get('create/{field_order_id}', [Modules\DeliveryManagement\Http\Controllers\FieldPaymentController::class, 'create'])->name('create');
        Route::post('store', [Modules\DeliveryManagement\Http\Controllers\FieldPaymentController::class, 'store'])->name('store');
        Route::get('{id}', [Modules\DeliveryManagement\Http\Controllers\FieldPaymentController::class, 'show'])->name('show');
        Route::get('{id}/edit', [Modules\DeliveryManagement\Http\Controllers\FieldPaymentController::class, 'edit'])->name('edit');
        Route::put('update/{id}', [Modules\DeliveryManagement\Http\Controllers\FieldPaymentController::class, 'update'])->name('update');
        Route::delete('delete/{id}', [Modules\DeliveryManagement\Http\Controllers\FieldPaymentController::class, 'destroy'])->name('destroy');
        Route::get('receipt/{id}', [Modules\DeliveryManagement\Http\Controllers\FieldPaymentController::class, 'receipt'])->name('receipt');
        Route::post('split-payment/{order_id}', [Modules\DeliveryManagement\Http\Controllers\FieldPaymentController::class, 'splitPayment'])->name('splitPayment');
        Route::get('order-payments/{order_id}', [Modules\DeliveryManagement\Http\Controllers\FieldPaymentController::class, 'getOrderPayments']);
        Route::get('send-receipt/{id}', [Modules\DeliveryManagement\Http\Controllers\FieldPaymentController::class, 'sendReceipt'])->name('sendReceipt');
        Route::get('daily-summary', [Modules\DeliveryManagement\Http\Controllers\FieldPaymentController::class, 'dailySummary'])->name('dailySummary');
        Route::get('weekly-summary', [Modules\DeliveryManagement\Http\Controllers\FieldPaymentController::class, 'weeklySummary'])->name('weeklySummary');
        Route::get('monthly-summary', [Modules\DeliveryManagement\Http\Controllers\FieldPaymentController::class, 'monthlySummary'])->name('monthlySummary');
        Route::post('field-payment-data', [Modules\DeliveryManagement\Http\Controllers\FieldPaymentController::class, 'fieldPaymentData'])->name('fieldPaymentData');
    });

    Route::prefix('delivery-man-delivery')->name('delivery-man-delivery.')->group(function () {
        Route::get('/', [Modules\DeliveryManagement\Http\Controllers\DeliveryManagementController::class, 'index'])->name('index');
        Route::get('delivery-list-data', [Modules\DeliveryManagement\Http\Controllers\DeliveryManagementController::class, 'deliveryListData'])->name('deliveryListData');
        Route::post('assign', [Modules\DeliveryManagement\Http\Controllers\DeliveryManagementController::class, 'assign'])->name('assign');
        Route::post('auto-assign', [Modules\DeliveryManagement\Http\Controllers\DeliveryManagementController::class, 'autoAssign'])->name('autoAssign');
        Route::post('update-status/{id}', [Modules\DeliveryManagement\Http\Controllers\DeliveryManagementController::class, 'updateStatus'])->name('updateStatus');
        Route::get('map-view', [Modules\DeliveryManagement\Http\Controllers\DeliveryManagementController::class, 'mapView'])->name('mapView');
        Route::get('live-tracking', [Modules\DeliveryManagement\Http\Controllers\DeliveryManagementController::class, 'liveTracking'])->name('liveTracking');
        Route::get('route-optimization/{delivery_man_id}', [Modules\DeliveryManagement\Http\Controllers\DeliveryManagementController::class, 'routeOptimization'])->name('routeOptimization');
        Route::post('set-priority/{id}', [Modules\DeliveryManagement\Http\Controllers\DeliveryManagementController::class, 'setPriority'])->name('setPriority');
        Route::get('pending-deliveries', [Modules\DeliveryManagement\Http\Controllers\DeliveryManagementController::class, 'pendingDeliveries'])->name('pendingDeliveries');
        Route::get('completed-deliveries', [Modules\DeliveryManagement\Http\Controllers\DeliveryManagementController::class, 'completedDeliveries'])->name('completedDeliveries');
        Route::get('due-deliveries', [Modules\DeliveryManagement\Http\Controllers\DeliveryManagementController::class, 'dueDeliveries'])->name('dueDeliveries');
        Route::post('delete/{id}', [Modules\DeliveryManagement\Http\Controllers\DeliveryManagementController::class, 'delete'])->name('delete');
    });

    Route::prefix('warehouse-products')->name('warehouse-products.')->group(function () {
        Route::get('/', [Modules\DeliveryManagement\Http\Controllers\WarehouseProductController::class, 'index'])->name('index');
        Route::get('create', [Modules\DeliveryManagement\Http\Controllers\WarehouseProductController::class, 'create'])->name('create');
        Route::post('store', [Modules\DeliveryManagement\Http\Controllers\WarehouseProductController::class, 'store'])->name('store');
        Route::get('{id}/edit', [Modules\DeliveryManagement\Http\Controllers\WarehouseProductController::class, 'edit'])->name('edit');
        Route::put('update/{id}', [Modules\DeliveryManagement\Http\Controllers\WarehouseProductController::class, 'update'])->name('update');
        Route::post('warehouse-product-data', [Modules\DeliveryManagement\Http\Controllers\WarehouseProductController::class, 'warehouseProductData'])->name('warehouseProductData');
        Route::post('delete/{id}', [Modules\DeliveryManagement\Http\Controllers\WarehouseProductController::class, 'destroy'])->name('destroy');
        Route::post('deletebyselection', [Modules\DeliveryManagement\Http\Controllers\WarehouseProductController::class, 'deleteBySelection'])->name('deletebyselection');
    });

    Route::prefix('delivery-proofs')->name('delivery-proofs.')->group(function () {
        Route::get('{delivery_id}', [Modules\DeliveryManagement\Http\Controllers\DeliveryProofController::class, 'index'])->name('index');
        Route::post('store', [Modules\DeliveryManagement\Http\Controllers\DeliveryProofController::class, 'store'])->name('store');
        Route::get('{id}/edit', [Modules\DeliveryManagement\Http\Controllers\DeliveryProofController::class, 'edit'])->name('edit');
        Route::post('update/{id}', [Modules\DeliveryManagement\Http\Controllers\DeliveryProofController::class, 'update'])->name('update');
        Route::post('upload-photo', [Modules\DeliveryManagement\Http\Controllers\DeliveryProofController::class, 'uploadPhoto'])->name('uploadPhoto');
        Route::post('capture-signature', [Modules\DeliveryManagement\Http\Controllers\DeliveryProofController::class, 'captureSignature'])->name('captureSignature');
        Route::post('verify-otp', [Modules\DeliveryManagement\Http\Controllers\DeliveryProofController::class, 'verifyOtp'])->name('verifyOtp');
        Route::post('geofence-check', [Modules\DeliveryManagement\Http\Controllers\DeliveryProofController::class, 'geofenceCheck'])->name('geofenceCheck');
    });

    Route::prefix('cash-deposits')->name('cash-deposits.')->group(function () {
        Route::get('/', [Modules\DeliveryManagement\Http\Controllers\CashDepositController::class, 'index'])->name('index');
        Route::get('create', [Modules\DeliveryManagement\Http\Controllers\CashDepositController::class, 'create'])->name('create');
        Route::post('store', [Modules\DeliveryManagement\Http\Controllers\CashDepositController::class, 'store'])->name('store');
        Route::get('{id}', [Modules\DeliveryManagement\Http\Controllers\CashDepositController::class, 'show'])->name('show');
        Route::post('verify/{id}', [Modules\DeliveryManagement\Http\Controllers\CashDepositController::class, 'verify'])->name('verify');
        Route::get('summary', [Modules\DeliveryManagement\Http\Controllers\CashDepositController::class, 'summary'])->name('summary');
        Route::get('delivery-man-summary/{delivery_man_id}', [Modules\DeliveryManagement\Http\Controllers\CashDepositController::class, 'deliveryManSummary'])->name('deliveryManSummary');
        Route::get('pending-deposits', [Modules\DeliveryManagement\Http\Controllers\CashDepositController::class, 'pendingDeposits'])->name('pendingDeposits');
    });

    Route::prefix('field-returns')->name('field-returns.')->group(function () {
        Route::get('/', [Modules\DeliveryManagement\Http\Controllers\FieldReturnController::class, 'index'])->name('index');
        Route::get('create/{field_order_id}', [Modules\DeliveryManagement\Http\Controllers\FieldReturnController::class, 'create'])->name('create');
        Route::post('store', [Modules\DeliveryManagement\Http\Controllers\FieldReturnController::class, 'store'])->name('store');
        Route::get('{id}', [Modules\DeliveryManagement\Http\Controllers\FieldReturnController::class, 'show'])->name('show');
        Route::post('confirm/{id}', [Modules\DeliveryManagement\Http\Controllers\FieldReturnController::class, 'confirm'])->name('confirm');
        Route::get('reasons', [Modules\DeliveryManagement\Http\Controllers\FieldReturnController::class, 'getReasons']);
        Route::post('upload-photo', [Modules\DeliveryManagement\Http\Controllers\FieldReturnController::class, 'uploadPhoto'])->name('uploadPhoto');
        Route::post('create-replacement/{id}', [Modules\DeliveryManagement\Http\Controllers\FieldReturnController::class, 'createReplacement'])->name('createReplacement');
    });

    Route::prefix('customer-visits')->name('customer-visits.')->group(function () {
        Route::get('/', [Modules\DeliveryManagement\Http\Controllers\CustomerVisitController::class, 'index'])->name('index');
        Route::post('check-in', [Modules\DeliveryManagement\Http\Controllers\CustomerVisitController::class, 'checkIn'])->name('checkIn');
        Route::post('check-out/{id}', [Modules\DeliveryManagement\Http\Controllers\CustomerVisitController::class, 'checkOut'])->name('checkOut');
        Route::get('history/{customer_id}', [Modules\DeliveryManagement\Http\Controllers\CustomerVisitController::class, 'history'])->name('history');
        Route::get('logs', [Modules\DeliveryManagement\Http\Controllers\CustomerVisitController::class, 'logs'])->name('logs');
        Route::get('today-visits', [Modules\DeliveryManagement\Http\Controllers\CustomerVisitController::class, 'todayVisits'])->name('todayVisits');
    });

    Route::prefix('delivery-schedules')->name('delivery-schedules.')->group(function () {
        Route::get('/', [Modules\DeliveryManagement\Http\Controllers\DeliveryManScheduleController::class, 'index'])->name('index');
        Route::post('store', [Modules\DeliveryManagement\Http\Controllers\DeliveryManScheduleController::class, 'store'])->name('store');
        Route::post('update/{id}', [Modules\DeliveryManagement\Http\Controllers\DeliveryManScheduleController::class, 'update'])->name('update');
        Route::post('delete/{id}', [Modules\DeliveryManagement\Http\Controllers\DeliveryManScheduleController::class, 'delete'])->name('delete');
        Route::get('by-delivery-man/{delivery_man_id}', [Modules\DeliveryManagement\Http\Controllers\DeliveryManScheduleController::class, 'getByDeliveryMan']);
        Route::get('calendar', [Modules\DeliveryManagement\Http\Controllers\DeliveryManScheduleController::class, 'calendar'])->name('calendar');
    });

    Route::prefix('delivery-settings')->name('delivery-settings.')->group(function () {
        Route::get('/', [Modules\DeliveryManagement\Http\Controllers\DeliverySettingController::class, 'index'])->name('index');
        Route::post('update', [Modules\DeliveryManagement\Http\Controllers\DeliverySettingController::class, 'update'])->name('update');
        Route::get('commission-settings', [Modules\DeliveryManagement\Http\Controllers\DeliverySettingController::class, 'commissionSettings'])->name('commissionSettings');
        Route::post('commission-settings', [Modules\DeliveryManagement\Http\Controllers\DeliverySettingController::class, 'updateCommissionSettings'])->name('updateCommissionSettings');
        Route::get('route-settings', [Modules\DeliveryManagement\Http\Controllers\DeliverySettingController::class, 'routeSettings'])->name('routeSettings');
        Route::post('route-settings', [Modules\DeliveryManagement\Http\Controllers\DeliverySettingController::class, 'updateRouteSettings'])->name('updateRouteSettings');
        Route::get('delivery-charge-settings', [Modules\DeliveryManagement\Http\Controllers\DeliverySettingController::class, 'deliveryChargeSettings'])->name('deliveryChargeSettings');
        Route::post('delivery-charge-settings', [Modules\DeliveryManagement\Http\Controllers\DeliverySettingController::class, 'updateDeliveryChargeSettings'])->name('updateDeliveryChargeSettings');
        Route::get('time-slot-settings', [Modules\DeliveryManagement\Http\Controllers\DeliverySettingController::class, 'timeSlotSettings'])->name('timeSlotSettings');
        Route::post('time-slot-settings', [Modules\DeliveryManagement\Http\Controllers\DeliverySettingController::class, 'updateTimeSlotSettings'])->name('updateTimeSlotSettings');
        Route::get('general-settings', [Modules\DeliveryManagement\Http\Controllers\DeliverySettingController::class, 'generalSettings'])->name('generalSettings');
        Route::post('general-settings', [Modules\DeliveryManagement\Http\Controllers\DeliverySettingController::class, 'updateGeneralSettings'])->name('updateGeneralSettings');
    });

    Route::prefix('delivery-notifications')->name('delivery-notifications.')->group(function () {
        Route::get('/', [Modules\DeliveryManagement\Http\Controllers\DeliveryNotificationController::class, 'index'])->name('index');
        Route::post('store', [Modules\DeliveryManagement\Http\Controllers\DeliveryNotificationController::class, 'store'])->name('store');
        Route::get('unread-count', [Modules\DeliveryManagement\Http\Controllers\DeliveryNotificationController::class, 'unreadCount']);
        Route::post('mark-as-read/{id}', [Modules\DeliveryManagement\Http\Controllers\DeliveryNotificationController::class, 'markAsRead'])->name('markAsRead');
        Route::post('mark-all-as-read', [Modules\DeliveryManagement\Http\Controllers\DeliveryNotificationController::class, 'markAllAsRead'])->name('markAllAsRead');
        Route::get('templates', [Modules\DeliveryManagement\Http\Controllers\DeliveryNotificationController::class, 'templates'])->name('templates');
        Route::post('templates', [Modules\DeliveryManagement\Http\Controllers\DeliveryNotificationController::class, 'updateTemplates'])->name('updateTemplates');
    });

    Route::prefix('delivery-reports')->name('delivery-reports.')->group(function () {
        Route::get('/', [Modules\DeliveryManagement\Http\Controllers\DeliveryReportController::class, 'index'])->name('index');
        Route::post('dashboard-data', [Modules\DeliveryManagement\Http\Controllers\DeliveryReportController::class, 'dashboardData'])->name('dashboardData');
        Route::get('delivery-man-wise-order', [Modules\DeliveryManagement\Http\Controllers\DeliveryReportController::class, 'deliveryManWiseOrder'])->name('deliveryManWiseOrder');
        Route::get('delivery-man-wise-collection', [Modules\DeliveryManagement\Http\Controllers\DeliveryReportController::class, 'deliveryManWiseCollection'])->name('deliveryManWiseCollection');
        Route::get('delivery-man-wise-due', [Modules\DeliveryManagement\Http\Controllers\DeliveryReportController::class, 'deliveryManWiseDue'])->name('deliveryManWiseDue');
        Route::get('area-wise-sales', [Modules\DeliveryManagement\Http\Controllers\DeliveryReportController::class, 'areaWiseSales'])->name('areaWiseSales');
        Route::get('delivery-performance', [Modules\DeliveryManagement\Http\Controllers\DeliveryReportController::class, 'deliveryPerformance'])->name('deliveryPerformance');
        Route::get('commission-report', [Modules\DeliveryManagement\Http\Controllers\DeliveryReportController::class, 'commissionReport'])->name('commissionReport');
        Route::get('commission-payout', [Modules\DeliveryManagement\Http\Controllers\DeliveryReportController::class, 'commissionPayout'])->name('commissionPayout');
        Route::get('cash-reconciliation', [Modules\DeliveryManagement\Http\Controllers\DeliveryReportController::class, 'cashReconciliation'])->name('cashReconciliation');
        Route::get('customer-visit-report', [Modules\DeliveryManagement\Http\Controllers\DeliveryReportController::class, 'customerVisitReport'])->name('customerVisitReport');
        Route::get('product-wise-field-sale', [Modules\DeliveryManagement\Http\Controllers\DeliveryReportController::class, 'productWiseFieldSale'])->name('productWiseFieldSale');
        Route::get('delivery-man-dashboard/{id}', [Modules\DeliveryManagement\Http\Controllers\DeliveryReportController::class, 'deliveryManDashboard'])->name('deliveryManDashboard');
    });

    Route::prefix('delivery-man-commissions')->name('delivery-man-commissions.')->group(function () {
        Route::get('/', [Modules\DeliveryManagement\Http\Controllers\DeliveryManCommissionController::class, 'index'])->name('index');
        Route::get('settings', [Modules\DeliveryManagement\Http\Controllers\DeliveryManCommissionController::class, 'settings'])->name('settings');
        Route::post('settings', [Modules\DeliveryManagement\Http\Controllers\DeliveryManCommissionController::class, 'updateSettings'])->name('updateSettings');
        Route::get('slabs', [Modules\DeliveryManagement\Http\Controllers\DeliveryManCommissionController::class, 'slabs'])->name('slabs');
        Route::post('slabs', [Modules\DeliveryManagement\Http\Controllers\DeliveryManCommissionController::class, 'storeSlabs'])->name('storeSlabs');
        Route::post('calculate/{field_order_id}', [Modules\DeliveryManagement\Http\Controllers\DeliveryManCommissionController::class, 'calculate'])->name('calculate');
        Route::get('payout-report', [Modules\DeliveryManagement\Http\Controllers\DeliveryManCommissionController::class, 'payoutReport'])->name('payoutReport');
        Route::post('payout', [Modules\DeliveryManagement\Http\Controllers\DeliveryManCommissionController::class, 'processPayout'])->name('processPayout');
        Route::get('incentives/new-customer', [Modules\DeliveryManagement\Http\Controllers\DeliveryManCommissionController::class, 'newCustomerIncentives'])->name('newCustomerIncentives');
        Route::get('incentives/due-collection', [Modules\DeliveryManagement\Http\Controllers\DeliveryManCommissionController::class, 'dueCollectionIncentives'])->name('dueCollectionIncentives');
    });

});
// Public QR Menu — no authentication required
Route::get('/menu/{slug}', [\App\Http\Controllers\PublicMenuController::class, 'index'])->name('public.menu');
Route::get('/menu/{slug}/products', [\App\Http\Controllers\PublicMenuController::class, 'getProducts'])->name('public.menu.products');
Route::post('/menu/place-order', [\App\Http\Controllers\PublicMenuController::class, 'placeOrder'])->name('public.menu.placeOrder');

// QR Code Redirect Route (Public)
Route::get('/q/{code}', [QrCodeController::class, 'redirect']);

Route::get('/fix-live-cache', [PaymentGatewayController::class, 'fixLiveCache']);

// ===== Public Payment Gateway Routes (M-Pesa, MTN MoMo, PayHere, Stripe QR Checkouts) =====
Route::get('/payment/payhere/checkout/{order_id}', [PaymentGatewayController::class, 'payhereCheckout'])->name('payhere.qr_checkout');

// Generic Payment Success/Failed screens for Customer's Mobile Browser
Route::get('/payment/success', [PaymentGatewayController::class, 'paymentSuccess'])->name('payment.success');

Route::get('/payment/failed', [PaymentGatewayController::class, 'paymentFailed'])->name('payment.failed');

// We also need these POST routes available to the POS page (which is logged in) 
// but since we moved them out, they are accessible. However, it's safer if they are public.
Route::post('/payment/{gateway}/push',         [PaymentGatewayController::class, 'push'])->name('payment.push');
Route::post('/payment/{gateway}/query-status', [PaymentGatewayController::class, 'queryStatus'])->name('payment.queryStatus');

Route::post('/mpesa/stk-push',    [PaymentGatewayController::class, 'push'])->defaults('gateway', 'mpesa')->name('mpesa.stkPush');
Route::post('/mpesa/query-status',[PaymentGatewayController::class, 'queryStatus'])->defaults('gateway', 'mpesa')->name('mpesa.queryStatus');
