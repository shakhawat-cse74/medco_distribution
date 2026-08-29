<?php

use Illuminate\Support\Facades\Route;
use Modules\DeliveryManagement\Http\Controllers\DeliveryManAuthController;
use Modules\DeliveryManagement\Http\Controllers\DeliveryReportController;
use Modules\DeliveryManagement\Http\Controllers\DeliveryManagementController;
use Modules\DeliveryManagement\Http\Controllers\DeliveryManController;
use Modules\DeliveryManagement\Http\Controllers\FieldOrderController;
use Modules\DeliveryManagement\Http\Controllers\FieldPaymentController;
use Modules\DeliveryManagement\Http\Controllers\FieldReturnController;
use Modules\DeliveryManagement\Http\Controllers\DeliveryProofController;
use Modules\DeliveryManagement\Http\Controllers\CashDepositController;
use Modules\DeliveryManagement\Http\Controllers\CustomerVisitController;
use Modules\DeliveryManagement\Http\Controllers\DeliveryManScheduleController;
use Modules\DeliveryManagement\Http\Controllers\DeliveryNotificationController;
use Modules\DeliveryManagement\Http\Controllers\DeliverySettingController;
use Modules\DeliveryManagement\Http\Controllers\WarehouseProductController;
use Modules\DeliveryManagement\Http\Controllers\DeliveryManRouteController;

Route::prefix('delivery-man')->name('delivery-man.')->group(function () {
    Route::get('login', [DeliveryManAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [DeliveryManAuthController::class, 'login'])->name('login.post');
    Route::post('logout', [DeliveryManAuthController::class, 'logout'])->name('logout');

    Route::middleware('delivery.man.auth')->group(function () {
        Route::get('dashboard', [DeliveryManAuthController::class, 'dashboard'])->name('dashboard');

        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [DeliveryReportController::class, 'index'])->name('index');
            Route::post('dashboard-data', [DeliveryReportController::class, 'dashboardData'])->name('dashboardData');
        });

        Route::prefix('deliveries')->name('deliveries.')->group(function () {
            Route::get('/', [DeliveryManagementController::class, 'index'])->name('index');
            Route::get('list-data', [DeliveryManagementController::class, 'deliveryListData'])->name('listData');
            Route::get('pending', [DeliveryManagementController::class, 'pendingDeliveries'])->name('pending');
            Route::get('completed', [DeliveryManagementController::class, 'completedDeliveries'])->name('completed');
            Route::get('due', [DeliveryManagementController::class, 'dueDeliveries'])->name('due');
        });

        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [FieldOrderController::class, 'index'])->name('index');
            Route::get('create', [FieldOrderController::class, 'create'])->name('create');
            Route::post('store', [FieldOrderController::class, 'store'])->name('store');
            Route::get('{id}', [FieldOrderController::class, 'show'])->name('show');
            Route::get('draft-list', [FieldOrderController::class, 'draftList'])->name('draftList');
            Route::get('draft/{id}', [FieldOrderController::class, 'loadDraft'])->name('loadDraft');
            Route::post('draft/{id}/update', [FieldOrderController::class, 'updateDraft'])->name('updateDraft');
            Route::post('draft/{id}/delete', [FieldOrderController::class, 'deleteDraft'])->name('deleteDraft');
            Route::post('cancel/{id}', [FieldOrderController::class, 'cancel'])->name('cancel');
            Route::get('products/search', [FieldOrderController::class, 'searchProducts'])->name('searchProducts');
            Route::get('customers/search', [FieldOrderController::class, 'searchCustomers'])->name('searchCustomers');
            Route::post('validate-stock', [FieldOrderController::class, 'validateStock'])->name('validateStock');
            Route::get('invoice/{id}', [FieldOrderController::class, 'genInvoice'])->name('invoice');
            Route::post('send-whatsapp/{id}', [FieldOrderController::class, 'sendWhatsApp'])->name('sendWhatsApp');
            Route::post('send-sms/{id}', [FieldOrderController::class, 'sendSMS'])->name('sendSMS');
            Route::get('barcode/{id}', [FieldOrderController::class, 'printBarcode'])->name('printBarcode');
        });

        Route::prefix('warehouse-products')->name('warehouse-products.')->group(function () {
            Route::get('/', [WarehouseProductController::class, 'index'])->name('index');
        });

        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [FieldPaymentController::class, 'index'])->name('index');
            Route::get('create/{field_order_id}', [FieldPaymentController::class, 'create'])->name('create');
            Route::post('store', [FieldPaymentController::class, 'store'])->name('store');
            Route::get('{id}', [FieldPaymentController::class, 'show'])->name('show');
            Route::get('{id}/edit', [FieldPaymentController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [FieldPaymentController::class, 'update'])->name('update');
            Route::get('receipt/{id}', [FieldPaymentController::class, 'receipt'])->name('receipt');
            Route::post('split-payment/{order_id}', [FieldPaymentController::class, 'splitPayment'])->name('splitPayment');
            Route::get('order-payments/{order_id}', [FieldPaymentController::class, 'getOrderPayments'])->name('getOrderPayments');
            Route::get('daily-summary', [FieldPaymentController::class, 'dailySummary'])->name('dailySummary');
            Route::get('weekly-summary', [FieldPaymentController::class, 'weeklySummary'])->name('weeklySummary');
            Route::get('monthly-summary', [FieldPaymentController::class, 'monthlySummary'])->name('monthlySummary');
        });

        Route::prefix('returns')->name('returns.')->group(function () {
            Route::get('create/{field_order_id}', [FieldReturnController::class, 'create'])->name('create');
            Route::post('store', [FieldReturnController::class, 'store'])->name('store');
            Route::get('{id}', [FieldReturnController::class, 'show'])->name('show');
        });

        Route::prefix('proofs')->name('proofs.')->group(function () {
            Route::get('{delivery_id}', [DeliveryProofController::class, 'index'])->name('index');
            Route::post('store', [DeliveryProofController::class, 'store'])->name('store');
            Route::get('{id}/edit', [DeliveryProofController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [DeliveryProofController::class, 'update'])->name('update');
            Route::post('upload-photo', [DeliveryProofController::class, 'uploadPhoto'])->name('uploadPhoto');
            Route::post('capture-signature', [DeliveryProofController::class, 'captureSignature'])->name('captureSignature');
            Route::post('verify-otp', [DeliveryProofController::class, 'verifyOtp'])->name('verifyOtp');
            Route::post('geofence-check', [DeliveryProofController::class, 'geofenceCheck'])->name('geofenceCheck');
        });

        Route::prefix('cash-deposits')->name('cash-deposits.')->group(function () {
            Route::get('/', [CashDepositController::class, 'index'])->name('index');
            Route::get('create', [CashDepositController::class, 'create'])->name('create');
            Route::post('store', [CashDepositController::class, 'store'])->name('store');
            Route::get('{id}', [CashDepositController::class, 'show'])->name('show');
            Route::get('summary', [CashDepositController::class, 'summary'])->name('summary');
            Route::get('pending', [CashDepositController::class, 'pendingDeposits'])->name('pending');
        });

        Route::prefix('visits')->name('visits.')->group(function () {
            Route::get('check-in', [CustomerVisitController::class, 'checkIn'])->name('checkIn');
            Route::post('check-out/{id}', [CustomerVisitController::class, 'checkOut'])->name('checkOut');
            Route::get('history/{customer_id}', [CustomerVisitController::class, 'history'])->name('history');
            Route::get('logs', [CustomerVisitController::class, 'logs'])->name('logs');
            Route::get('today-visits', [CustomerVisitController::class, 'todayVisits'])->name('todayVisits');
        });

        Route::prefix('schedules')->name('schedules.')->group(function () {
            Route::get('/', [DeliveryManScheduleController::class, 'index'])->name('index');
            Route::get('calendar', [DeliveryManScheduleController::class, 'calendar'])->name('calendar');
        });

        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [DeliveryNotificationController::class, 'index'])->name('index');
            Route::get('unread-count', [DeliveryNotificationController::class, 'unreadCount'])->name('unreadCount');
            Route::post('mark-as-read/{id}', [DeliveryNotificationController::class, 'markAsRead'])->name('markAsRead');
            Route::post('mark-all-as-read', [DeliveryNotificationController::class, 'markAllAsRead'])->name('markAllAsRead');
        });

        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [DeliverySettingController::class, 'index'])->name('index');
        });

        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', [DeliveryManController::class, 'show'])->name('show');
            Route::post('update', [DeliveryManController::class, 'update'])->name('update');
            Route::post('upload-photo', [DeliveryManController::class, 'uploadPhoto'])->name('uploadPhoto');
        });

        Route::prefix('customers')->name('customers.')->group(function () {
            Route::get('/', [DeliveryManController::class, 'assignedCustomers'])->name('index');
            Route::get('{customer_id}/orders', [DeliveryManController::class, 'customerOrderHistory'])->name('orders');
            Route::get('{customer_id}/ledger', [DeliveryManController::class, 'customerLedger'])->name('ledger');
            Route::post('{customer_id}/collect-payment', [DeliveryManController::class, 'collectDuePayment'])->name('collectPayment');
        });

        Route::prefix('routes')->name('routes.')->group(function () {
            Route::get('/', [DeliveryManRouteController::class, 'index'])->name('index');
            Route::get('create', [DeliveryManRouteController::class, 'create'])->name('create');
            Route::post('store', [DeliveryManRouteController::class, 'store'])->name('store');
            Route::get('{id}/edit', [DeliveryManRouteController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [DeliveryManRouteController::class, 'update'])->name('update');
            Route::post('delete/{id}', [DeliveryManRouteController::class, 'delete'])->name('delete');
            Route::get('assign', [DeliveryManRouteController::class, 'assignForm'])->name('assignForm');
            Route::post('assign-delivery-man', [DeliveryManRouteController::class, 'assignDeliveryMan'])->name('assignDeliveryMan');
        });
    });
});
