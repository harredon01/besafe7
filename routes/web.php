<?php

/*
  |--------------------------------------------------------------------------
  | Web Routes
  |--------------------------------------------------------------------------
  |
  | This file is where you may define all of the routes that are handled
  | by your application. Just tell Laravel the URIs it should respond
  | to using a Closure or controller method. Build something great!
  |
 */

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index');
Route::get('user/editProfile', 'UserController@getEditProfile');
Route::post('user/editProfile', 'UserController@postEditProfile');
Route::get('user/editAddress', 'UserController@getEditAddress');
Route::get('user/editAccess', 'UserController@access');
Route::get('user/addresses', 'UserController@getAddresses');
Route::delete('user/addresses/{code?}', 'UserController@deleteAddress');
Route::post('user/billingAddress/{code?}', 'UserController@postSetAsBillingAddress');
Route::post('user/editAddress', 'UserController@postEditAddress');

Route::get('products', 'ProductController@getproducts');
Route::get('products/{code?}', 'ProductController@getproduct');

Route::get('plans', 'BillingController@getPlans');
Route::get('sources', 'BillingController@getSources');
Route::get('subscriptions', 'BillingController@getSubscriptions');

Route::get('carts', 'CartController@getCart');
Route::get('cart/checkout', 'CartController@getCheckoutCart');
Route::post('cart/add', 'CartController@postAddCartItem');
Route::post('cart/update', 'CartController@postUpdateCartItem');
Route::post('cart/clear', 'CartController@postClearCart');
Route::get('cart/load', 'CartController@loadActiveCart');
Route::get('cart/test', 'CartController@cartTest');

Route::get('checkout', 'CartController@getCheckout');
Route::get('checkout/shippingConditions/{code?}', 'CartController@getShippingConditions');
Route::post('checkout/coupon/{code?}', 'CartController@postSetCoupon');
Route::post('checkout/billingAddress/{code?}', 'CartController@postSetBillingAddress');
Route::post('checkout/shippingAddress/{code?}', 'CartController@postSetShippingAddress');
Route::post('checkout/shippingCondition/{code?}', 'CartController@postSetShippingCondition');

Route::get('payu/banks', 'PayuController@getBanks');
Route::get('payu/payment_methods', 'PayuController@getPaymentMethods');
Route::post('payu/pay_cc', 'PayuController@postPayCreditCard');
Route::post('payu/pay_debit', 'PayuController@postPayDebitCard');
Route::post('payu/pay_cash', 'PayuController@postPayCash');
Route::get('payu/return', 'PayuController@returnPayU');
Route::get('payu/cron', 'PayuController@cronPayU');
Route::get('billing/orders', 'BillingController@getOrders');
Route::get('billing/payments', 'BillingController@getPayments');


Route::get('merchant/{code?}', 'MerchantController@getMerchantOrders');
Route::get('merchantProducts/{code?}', 'UserController@getMerchant');

Route::get('map/{code?}', 'MapExternalController@index');
Route::get('safereportsext/{code?}', 'MapExternalController@report');
Route::get('food/build_route_id/{id?}/{hash?}', 'FoodController@buildScenarioRouteId');
Route::get('food/build_complete_scenario/{scenario?}/{hash?}', 'FoodController@buildCompleteScenario');
Route::get('food/build_scenario_positive/{scenario?}/{hash?}', 'FoodController@buildScenarioPositive');
Route::get('food/get_scenario_structure/{scenario?}/{polygon?}', 'FoodController@getScenarioStructure');
Route::get('food/regenerate_scenarios/{polygon?}/{hash?}', 'FoodController@regenerateScenarios');
Route::get('food/regenerate_deliveries', 'FoodController@regenerateDeliveries');
Route::get('food/summary/{polygon?}', 'FoodController@getSummaryShipping');
Route::get('food/polygons', 'FoodController@getPolygons');
Route::get('food/routes', 'FoodController@getRoutes');
Route::get('food/menu', 'FoodController@getMenu');

Route::get('/purchase', function () {
    $className = "App\\Services\\Food";
    $rapigoClassName = "App\\Services\\Rapigo";
    $deliveries = App\Models\Delivery::where("status","transit")->get();
    $rapigo = new $rapigoClassName;
    $gateway = new $className($rapigo); //// <--- this thing will be autoloaded
    $data = $gateway->getPurchaseOrder($deliveries);
    $data['level']="";
    return new App\Mail\PurchaseOrder($data);
});
Route::get('/route_organize', function () {
    $className = "App\\Services\\Food";
    $rapigoClassName = "App\\Services\\Rapigo";
    $rapigo = new $rapigoClassName;
    $results = App\Models\Route::where("type", "preorganize-1")->where("status", "pending")->with(['deliveries.user'])->get();
    $gateway = new $className($rapigo); //// <--- this thing will be autoloaded
    $data = $gateway->buildScenario($results);
    return new App\Mail\RouteOrganize($data);
});
Route::get('/route_deliver', function () {
    $className = "App\\Services\\Food";
    $rapigoClassName = "App\\Services\\Rapigo";
    $results = App\Models\Route::where("description", "preorganize")->where("status", "pending")->with(['deliveries.user'])->get();
    $rapigo = new $rapigoClassName;
    $gateway = new $className($rapigo); //// <--- this thing will be autoloaded
    $data = $gateway->buildScenario($results);
    return new App\Mail\RouteDeliver($data);
});
Route::get('/route_choose', function () {
    $className = "App\\Services\\Food";
    $rapigoClassName = "App\\Services\\Rapigo";
    $rapigo = new $rapigoClassName;
    $gateway = new $className($rapigo); //// <--- this thing will be autoloaded
    $data = $gateway->getTotalEstimatedShipping("preorganize-1");
    return new App\Mail\RouteChoose($data['routes']);
});
Route::get('/scenario_select', function () {
    $className = "App\\Services\\Food";
    $rapigoClassName = "App\\Services\\Rapigo";
    $rapigo = new $rapigoClassName;
    $polygons = App\Models\CoveragePolygon::where('lat',"<>",0)->where('long',"<>",0)->first();
    $gateway = new $className($rapigo); //// <--- this thing will be autoloaded
    $data = $gateway->getShippingCosts($polygons->id);
    return new App\Mail\ScenarioSelect($data['resultsPre'], $data['resultsSimple'], $data['winner'],$polygons->id);
});

Route::get('/email_payment_pse', function () {
    $user = App\Models\User::find(2);
    $payment = App\Models\Payment::find(118);
    $url = "http://www.google.com";
    return new App\Mail\EmailPaymentPse($payment,$user,$url);
});

Route::get('/email_payment_cash', function () {
    $user = App\Models\User::find(2);
    $payment = App\Models\Payment::find(118);
    $url = "http://www.google.com";
    $pdf = "http://www.google.com";
    return new App\Mail\EmailPaymentCash($payment,$user,$url,$pdf);
});
Route::get('/test_order', function () {
    $order = App\Models\Order::find(9);
    $user = App\Models\User::find(2);
    $shipping = App\Models\OrderAddress::find(10);
    return new App\Mail\OrderApproved($order, $user, $shipping);
});
