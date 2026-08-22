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

// Route::prefix('ecommerce')->group(function() {
//     Route::get('/', 'EcommerceController@index');
// });

//Route::get('admin-login', 'AuthController@adminLogin')->name('login');

Route::group(['middleware' => ['common', 'auth', 'active']], function() {
    //sliders
	Route::group(['prefix' => 'sliders'], function() {
		Route::get('/', 'SliderController@slidersShow')->name('slider.index');
		Route::post('/create', 'SliderController@slidersCreate')->name('slidersCreate');
		Route::get('/delete/{id}', 'SliderController@slidersDelete')->name('slidersDelete');
	});
	
	//page
	Route::group(['prefix' => 'pages'], function() {
		Route::get('/','PageController@index')->name('page.index');
		Route::get('/create','PageController@create')->name('page.create');
		Route::post('/store','PageController@store')->name('page.store');
		Route::get('/edit/{id}','PageController@edit')->name('page.edit');
		Route::post('/update','PageController@update')->name('page.update');
		Route::get('/delete/{id}','PageController@destroy')->name('page.destroy');
		Route::get('/{status}/{id}','PageController@status')->name('page.status');
		Route::post('/massdelete','PageController@delete_by_selection'); 
		Route::get('/{slug}/','PageController@generateUniqueSlug')->name('page.slug');
		Route::get('/edit/{id}/{slug}/','PageController@generateUniqueSlugEdit')->name('page.edit.slug');
		  
		Route::post('/widget/store', 'PageWidgetController@store')->name('page.widget.store');
		Route::post('/widget/update', 'PageWidgetController@update')->name('page.widget.update');
		Route::post('/widget/order', 'PageWidgetController@order')->name('page.widget.order');
		Route::get('/widget/delete/{id}', 'PageWidgetController@delete')->name('page.widget.delete'); 
	});

	Route::group(['prefix' => 'social'], function() {
		Route::get('/','SocialLinksController@index')->name('social.links');
		Route::post('/store','SocialLinksController@store')->name('social.links.store');
		Route::get('/edit/{id}','SocialLinksController@edit')->name('social.links.edit');
		Route::post('/update','SocialLinksController@update')->name('social.links.update');
		Route::get('/delete/{id}','SocialLinksController@destroy')->name('social.links.destroy');
	});

	Route::prefix('setting')->group(function () {
		Route::get('ecommerce-setting', 'EcommerceSettingController@index')->name('setting.ecommerce');
		Route::post('ecommerce-setting/update', 'EcommerceSettingController@update')->name('setting.ecommerce.update');

		Route::get('payment-gateways', 'EcommerceSettingController@gateway')->name('setting.ecommerce.gateway');
		Route::post('payment-gateways/update', 'EcommerceSettingController@gatewayUpdate')->name('setting.ecommerce.gateway.update');
	});

	Route::prefix('menu')->group(function () {
		Route::get('/', 'MenuController@index')->name('menu.index');
		Route::post('/store','MenuController@store')->name('menu.store');
		Route::get('/edit/{id}','MenuController@edit');
		Route::post('/update','MenuController@updateMenu')->name('menu.update');	
		Route::get('/delete/{id}','MenuController@destroy');

		Route::get('/{id}', 'MenuItemsController@index');	
		Route::get('/add-category-to-menu/{menuid}/{ids}','MenuItemsController@addCatToMenu');
		Route::get('/add-collection-to-menu/{menuid}/{ids}','MenuItemsController@addCollectionToMenu');
		Route::get('/add-brand-to-menu/{menuid}/{ids}','MenuItemsController@addBrandToMenu');
		Route::get('/add-page-to-menu/{menuid}/{ids}','MenuItemsController@addPageToMenu');
		Route::get('/add-custom-link/{menuid}/{link}/{url}','MenuItemsController@addCustomLink');	
		Route::post('/menuitem/update/{id}','MenuItemsController@updateMenuItem');
		Route::get('/menuitem/delete/{id}/{key}/{in}','MenuItemsController@deleteMenuItem');		
	});

	Route::prefix('collection')->group(function () {
		Route::get('/', 'CollectionController@index')->name('collection.index');
		Route::get('/create','CollectionController@create')->name('collection.create');
		Route::post('/store','CollectionController@store')->name('collection.store');
		Route::get('/edit/{id}','CollectionController@edit');
		Route::post('/update','CollectionController@update')->name('collection.update');	
		Route::get('/delete/{id}','CollectionController@destroy');	
		Route::get('/{slug}/','CollectionController@generateUniqueSlug')->name('collection.slug');	
		Route::get('/edit/{id}/{slug}/','CollectionController@generateUniqueSlugEdit')->name('collection.edit.slug');
	});

	Route::prefix('faq')->group(function () {
		Route::get('/', 'FaqController@index')->name('faq.index');
		Route::post('/store','FaqController@store')->name('faq.store');
		Route::get('/edit/{id}','FaqController@edit');
		Route::post('/update','FaqController@update')->name('faq.update');	
		Route::get('/delete/{id}','FaqController@destroy');		
	});

	Route::prefix('faq/categories')->group(function () {
		Route::get('/', 'FaqCategoriesController@index')->name('faq.category.index');
		Route::post('/store','FaqCategoriesController@store')->name('faq.category.store');
		Route::get('/edit/{id}','FaqCategoriesController@edit');
		Route::post('/update','FaqCategoriesController@update')->name('faq.category.update');	
		Route::get('/delete/{id}','FaqCategoriesController@destroy');		
	});

	Route::prefix('widget')->group(function () {
		Route::get('/', 'WidgetController@index')->name('widget.index');
		Route::post('/store', 'WidgetController@store')->name('widget.store');
		Route::post('/update', 'WidgetController@update')->name('widget.update');
		Route::post('/order', 'WidgetController@order')->name('widget.order');
		Route::get('/delete/{id}', 'WidgetController@delete')->name('widget.delete');
	});
});

Route::group(['middleware' => ['ecommerce']], function() {

	Route::get('/', 'FrontController@index');

	Route::get('search/{product}', 'FrontController@search')->name('live.search');
	Route::post('search-grocery/', 'FrontController@searchProduct')->name('products.search');

	Route::get('product/{product_name}/{product_id}', 'FrontController@productDetails');

	//customer registration
	Route::get('/customer/register', 'AuthController@register');
	Route::post('register-customer', 'AuthController@processRegisterCustomer')->name('customerRegistration');
	//LOGIN ROUTES
	Route::get('/customer/login/{verify?}', 'AuthController@login')->name('customer.login');
	Route::post('/login-customer', 'AuthController@processLogin')->name('customerLogin');
	Route::get('/customer/logout', 'AuthController@logout')->name('customer.logout');

	Route::get('verify/{id}', 'AuthController@verify')->name('verify');
	//Forgot Password
	Route::get('/customer/forgot-password', 'AuthController@getPhone')->name('getPhone');
	Route::post('/customer/forgot-password', 'AuthController@checkPhone')->name('checkPhone');
	Route::post('/customer/reset-password', 'AuthController@getPass')->name('getPass');
	Route::post('/customer/save-password', 'AuthController@changePass')->name('changePass');


	//category pages
	Route::get('shop/', 'FrontController@shop');
	Route::get('shop/{category}', 'FrontController@category');

	//brand pages
	Route::get('brand/{brand}', 'FrontController@brandProducts');

	//collection pages
	Route::get('products/{collection}', 'FrontController@collectionProducts');

	// All products json data
	Route::get('all-products', 'FrontController@allProducts');

	//cart route
	Route::get('cart', 'CartController@index')->name('cart');
	Route::post('add-to-cart', 'CartController@addToCart')->name('addToCart');
	Route::post('update-cart', 'CartController@updateCart')->name('updateCart');
	Route::post('remove-from-cart', 'CartController@removeFromCart')->name('removeFromCart');

	//checkout route
	Route::get('checkout', 'CheckoutController@index')->name('checkout');
	Route::post('/place-order', 'OrdersController@create');

	Route::get('checkout/payment', 'OrdersController@onlinePayment')->name('online.payment');
	Route::post('stripe-payment', 'OrdersController@stripePayment');
	Route::post('paypal-payment', 'OrdersController@paypalPayment');

	Route::get('/order/success/{sale_reference}', 'OrdersController@success')->name('order.success');

	// apply coupon 
	Route::post('/apply-coupon', 'CheckoutController@applyCoupon')->name('applyCoupon');

	Route::post('/newsletter/subscribe', 'FrontController@newsletter');

	Route::post('/send-email', 'FrontController@contactMail');

	// pages
	Route::get('/{slug}', 'FrontController@page');

});
 
Route::group(['middleware' => ['ecommerce', 'customerauth'],'prefix' => 'customer'], function() {
	Route::get('/profile', 'CustomerController@index')->name('customer.profile'); 
	Route::get('/orders', 'CustomerController@orders');
	Route::get('/order-details/{id}', 'CustomerController@orderDetails');
	Route::get('/order-cancel/{id}', 'CustomerController@orderCancel');
	Route::get('/address', 'CustomerController@address');
	Route::post('/address/create', 'CustomerController@addressCreate');
	Route::get('/address/default/{id}', 'CustomerController@addressDefault');
	Route::get('/address/edit/{id}', 'CustomerController@addressEdit');
	Route::post('/address/update', 'CustomerController@addressUpdate');
	Route::get('/address/delete/{id}','CustomerController@AddressDelete');
	Route::get('/account-details', 'CustomerController@accountDetails');
    Route::post('/account-details/update', 'CustomerController@updateAccountDetails')->name('updateAccountDetails');
});

