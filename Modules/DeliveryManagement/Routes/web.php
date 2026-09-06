<?php

use Illuminate\Support\Facades\Route;
use Modules\DeliveryManagement\Http\Controllers\DeliveryManController;
use Modules\DeliveryManagement\Http\Controllers\DeliveryManAssignmentController;
use Modules\DeliveryManagement\Http\Controllers\DeliveryManRouteController;
use Modules\DeliveryManagement\Http\Controllers\DeliveryManVehicleController;
use Modules\DeliveryManagement\Http\Controllers\DeliveryManagementController;
use Modules\DeliveryManagement\Http\Controllers\DeliveryProofController;
use Modules\DeliveryManagement\Http\Controllers\DeliveryManCommissionController;
use Modules\DeliveryManagement\Http\Controllers\CashDepositController;
use Modules\DeliveryManagement\Http\Controllers\CustomerVisitController;
use Modules\DeliveryManagement\Http\Controllers\DeliveryNotificationController;
use Modules\DeliveryManagement\Http\Controllers\DeliveryManScheduleController;
use Modules\DeliveryManagement\Http\Controllers\DeliverySettingController;
use Modules\DeliveryManagement\Http\Controllers\DeliveryReportController;
use Modules\DeliveryManagement\Http\Controllers\DeliverySaleController;
use Modules\DeliveryManagement\Http\Controllers\DeliveryInstallmentController;
use Modules\DeliveryManagement\Http\Controllers\DeliveryReturnController;
use Modules\DeliveryManagement\Http\Controllers\DeliveryExchangeController;

Route::group(['middleware' => ['common', 'auth', 'active']], function () {

    Route::controller(DeliveryManController::class)->prefix('delivery-men')->name('delivery-men.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('store', 'store')->name('store');
        Route::get('{id}/edit', 'edit')->name('edit');
        Route::post('update', 'update')->name('update');
        Route::post('delete/{id}', 'delete')->name('delete');
        Route::post('deletebyselection', 'deleteBySelection');
        Route::get('{id}', 'show')->name('show');
        Route::post('toggle-status', 'toggleStatus')->name('toggleStatus');
        Route::get('performance/{id}', 'performance')->name('performance');
        Route::post('upload-photo', 'uploadPhoto')->name('uploadPhoto');
        Route::get('{id}/customers', 'assignedCustomers')->name('assignedCustomers');
        Route::get('{delivery_man_id}/customers/{customer_id}/orders', 'customerOrderHistory')->name('customerOrderHistory');
        Route::get('{delivery_man_id}/customers/{customer_id}/ledger', 'customerLedger')->name('customerLedger');
        Route::post('{delivery_man_id}/customers/{customer_id}/collect-payment', 'collectDuePayment')->name('collectDuePayment');
    });

    Route::controller(DeliveryManAssignmentController::class)->prefix('delivery-man-assignments')->name('delivery-man-assignments.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('store', 'store')->name('store');
        Route::post('update/{id}', 'update')->name('update');
        Route::post('delete/{id}', 'delete')->name('delete');
        Route::get('delivery-men-by-warehouse/{warehouse_id}', 'getDeliveryMenByWarehouse');
        Route::get('delivery-men-by-route/{route_id}', 'getDeliveryMenByRoute');
        Route::get('delivery-men-by-area/{area_id}', 'getDeliveryMenByArea');
    });

    Route::controller(DeliveryManRouteController::class)->prefix('delivery-man-routes')->name('delivery-man-routes.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('store', 'store')->name('store');
        Route::post('update/{id}', 'update')->name('update');
        Route::post('delete/{id}', 'delete')->name('delete');
        Route::post('assign-delivery-man', 'assignDeliveryMan')->name('assignDeliveryMan');
    });

    Route::controller(DeliveryManVehicleController::class)->prefix('delivery-man-vehicles')->name('delivery-man-vehicles.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('store', 'store')->name('store');
        Route::post('update/{id}', 'update')->name('update');
        Route::post('delete/{id}', 'delete')->name('delete');
    });

    Route::controller(DeliveryManagementController::class)->prefix('delivery-man-delivery')->name('delivery-man-delivery.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('delivery-list-data', 'deliveryListData');
        Route::post('assign', 'assign')->name('assign');
        Route::post('auto-assign', 'autoAssign')->name('autoAssign');
        Route::post('update-status/{id}', 'updateStatus')->name('updateStatus');
        Route::get('map-view', 'mapView')->name('mapView');
        Route::get('live-tracking', 'liveTracking')->name('liveTracking');
        Route::get('route-optimization/{delivery_man_id}', 'routeOptimization')->name('routeOptimization');
        Route::post('set-priority/{id}', 'setPriority')->name('setPriority');
        Route::get('pending-deliveries', 'pendingDeliveries')->name('pendingDeliveries');
        Route::get('completed-deliveries', 'completedDeliveries')->name('completedDeliveries');
        Route::get('due-deliveries', 'dueDeliveries')->name('dueDeliveries');
    });

    Route::controller(DeliveryProofController::class)->prefix('delivery-proofs')->name('delivery-proofs.')->group(function () {
        Route::get('{delivery_id}', 'index')->name('index');
        Route::post('store', 'store')->name('store');
        Route::get('{id}/edit', 'edit')->name('edit');
        Route::post('update/{id}', 'update')->name('update');
        Route::post('upload-photo', 'uploadPhoto')->name('uploadPhoto');
        Route::post('capture-signature', 'captureSignature')->name('captureSignature');
        Route::post('verify-otp', 'verifyOtp')->name('verifyOtp');
        Route::post('geofence-check', 'geofenceCheck')->name('geofenceCheck');
    });

    Route::controller(CashDepositController::class)->prefix('cash-deposits')->name('cash-deposits.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('store', 'store')->name('store');
        Route::get('{id}', 'show')->name('show');
        Route::post('verify/{id}', 'verify')->name('verify');
        Route::get('summary', 'summary')->name('summary');
        Route::get('delivery-man-summary/{delivery_man_id}', 'deliveryManSummary')->name('deliveryManSummary');
        Route::get('pending-deposits', 'pendingDeposits')->name('pendingDeposits');
    });

    Route::controller(CustomerVisitController::class)->prefix('customer-visits')->name('customer-visits.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('check-in', 'checkIn')->name('checkIn');
        Route::post('check-out/{id}', 'checkOut')->name('checkOut');
        Route::get('history/{customer_id}', 'history')->name('history');
        Route::get('logs', 'logs')->name('logs');
        Route::get('today-visits', 'todayVisits')->name('todayVisits');
    });

    Route::controller(DeliveryManScheduleController::class)->prefix('delivery-man-schedules')->name('delivery-man-schedules.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('store', 'store')->name('store');
        Route::post('update/{id}', 'update')->name('update');
        Route::post('delete/{id}', 'delete')->name('delete');
        Route::get('by-delivery-man/{delivery_man_id}', 'getByDeliveryMan');
        Route::get('calendar', 'calendar')->name('calendar');
    });

    Route::controller(DeliverySettingController::class)->prefix('delivery-settings')->name('delivery-settings.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('update', 'update')->name('update');
        Route::get('commission-settings', 'commissionSettings')->name('commissionSettings');
        Route::post('commission-settings', 'updateCommissionSettings')->name('updateCommissionSettings');
        Route::get('route-settings', 'routeSettings')->name('routeSettings');
        Route::post('route-settings', 'updateRouteSettings')->name('updateRouteSettings');
        Route::get('delivery-charge-settings', 'deliveryChargeSettings')->name('deliveryChargeSettings');
        Route::post('delivery-charge-settings', 'updateDeliveryChargeSettings')->name('updateDeliveryChargeSettings');
        Route::get('time-slot-settings', 'timeSlotSettings')->name('timeSlotSettings');
        Route::post('time-slot-settings', 'updateTimeSlotSettings')->name('updateTimeSlotSettings');
        Route::get('general-settings', 'generalSettings')->name('generalSettings');
        Route::post('general-settings', 'updateGeneralSettings')->name('updateGeneralSettings');
    });

    Route::controller(DeliveryNotificationController::class)->prefix('delivery-notifications')->name('delivery-notifications.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('store', 'store')->name('store');
        Route::get('unread-count', 'unreadCount');
        Route::post('mark-as-read/{id}', 'markAsRead')->name('markAsRead');
        Route::post('mark-all-as-read', 'markAllAsRead')->name('markAllAsRead');
        Route::get('templates', 'templates')->name('templates');
        Route::post('templates', 'updateTemplates')->name('updateTemplates');
    });

    Route::controller(DeliveryReportController::class)->prefix('delivery-reports')->name('delivery-reports.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('dashboard-data', 'dashboardData')->name('dashboardData');
        Route::get('delivery-man-wise-order', 'deliveryManWiseOrder')->name('deliveryManWiseOrder');
        Route::get('delivery-man-wise-collection', 'deliveryManWiseCollection')->name('deliveryManWiseCollection');
        Route::get('delivery-man-wise-due', 'deliveryManWiseDue')->name('deliveryManWiseDue');
        Route::get('area-wise-sales', 'areaWiseSales')->name('areaWiseSales');
        Route::get('delivery-performance', 'deliveryPerformance')->name('deliveryPerformance');
        Route::get('commission-report', 'commissionReport')->name('commissionReport');
        Route::get('commission-payout', 'commissionPayout')->name('commissionPayout');
        Route::get('cash-reconciliation', 'cashReconciliation')->name('cashReconciliation');
        Route::get('customer-visit-report', 'customerVisitReport')->name('customerVisitReport');
        Route::get('product-wise-field-sale', 'productWiseFieldSale')->name('productWiseFieldSale');
        Route::get('delivery-man-dashboard/{id}', 'deliveryManDashboard')->name('deliveryManDashboard');
    });

    Route::controller(DeliverySaleController::class)->prefix('delivery-sale')->name('delivery-sale.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('store', 'store')->name('store');
        Route::get('data', 'saleData')->name('saleData');
        Route::get('pos', 'pos')->name('pos');
        Route::get('get-warehouse-products/{warehouse_id}', 'getWarehouseProducts')->name('getWarehouseProducts');
        Route::get('gift-card-list', 'giftCardList')->name('giftCardList');
        Route::get('challan-list', 'challanList')->name('challanList');
        Route::get('challan-slip-list', 'challanSlipList')->name('challan-slip-list');
        Route::get('packing-slip-list', 'packingSlipList')->name('packing-slip-list');
        Route::get('sale-return', 'saleReturn')->name('saleReturn');
        Route::get('installment-list', 'installmentList')->name('installmentList');
        Route::get('coupon-list', 'couponList')->name('couponList');
        Route::get('cupon-list', 'cuponList')->name('cupon-list');
        Route::get('courier-list', 'courierList')->name('courierList');
        Route::get('curirer-list', 'curirerList')->name('curirer-list');
        Route::get('delivery-list', 'deliveryList')->name('deliveryList');
        Route::get('sale-exchange', 'saleExchange')->name('saleExchange');
        Route::get('invoice/{id}', 'invoice')->name('invoice');
        Route::get('{id}', 'show')->name('show');
        Route::get('{id}/edit', 'edit')->name('edit');
        Route::put('{id}', 'update')->name('update');
        Route::post('delete/{id}', 'destroy')->name('delete');
        Route::post('toggle-status/{id}', 'toggleStatus')->name('toggleStatus');
    });

    Route::controller(DeliveryInstallmentController::class)->prefix('delivery-installment')->name('delivery-installment.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('store', 'store')->name('store');
        Route::get('data', 'installmentData')->name('installmentData');
        Route::get('{id}', 'show')->name('show');
        Route::get('{id}/edit', 'edit')->name('edit');
        Route::post('update/{id}', 'update')->name('update');
        Route::post('delete/{id}', 'destroy')->name('delete');
    });

    Route::controller(DeliveryReturnController::class)->prefix('delivery-return')->name('delivery-return.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('store', 'store')->name('store');
        Route::post('return-data', 'returnData')->name('returnData');
        Route::get('getcustomergroup/{id}', 'getCustomerGroup')->name('getcustomergroup');
        Route::get('getproduct/{id}', 'getProduct')->name('getproduct');
        Route::get('lims_product_search', 'limsProductSearch')->name('limsProductSearch');
        Route::get('product_return/{id}', 'productReturnData')->name('productReturnData');
        Route::post('sendmail', 'sendMail')->name('sendmail');
        Route::get('{id}', 'show')->name('show');
    });

    Route::controller(DeliveryExchangeController::class)->prefix('delivery-exchange')->name('delivery-exchange.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('store', 'store')->name('store');
        Route::post('exchange-data', 'exchangeData')->name('exchangeData');
        Route::get('product_exchange/{id}', 'productExchange')->name('productExchange');
        Route::get('getcustomergroup/{id}', 'getCustomerGroup')->name('getcustomergroup');
        Route::get('{id}', 'show')->name('show');
    });
});