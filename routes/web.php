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
Route::middleware('cache.headers:public;max_age=2628000;etag')->group(function () {
    Route::get('/', function () {
        return view(config("app.views").'.welcome');
    });
});
Route::get('/test', function () {
    return view(config("app.views").'.lonchis');
});
Route::get('/a/faq', function () {
    return view(config("app.views").'.faq');
});
Route::get('/a/about-us', function () {
    return view(config("app.views").'.about-us');
});
Route::get('/a/blog',  'WelcomeController@getBlogList');
Route::get('/a/contact-us/{type?}',  'LeadController@getLanding');
Route::get('/a/blog/{slug?}','WelcomeController@getBlogDetail');
Route::get('/a/terms', function () {
    return view(config("app.views").'.content.terms');
});
Route::get('/a/icons', function () {
    return view(config("app.views").'.content.icons');
});
Route::get('/zones', function () {
    return view(config("app.views").'.content.zonespublic');
});
Auth::routes();
Route::get('landing/{type?}', 'LeadController@getLanding');
Route::post('landing', 'LeadController@postLanding');
Route::get('/home', 'HomeController@index');
Route::get('user/editProfile', 'UserController@getEditProfile');
Route::get('user/editPassword', 'UserController@getEditPassword');
Route::get('user/editAddress', 'UserController@getEditAddress');
Route::get('user/editAccess', 'UserController@access');
Route::get('user/addresses', 'UserController@getAddresses');
Route::get('user/payments', 'BillingController@getPayments');
Route::get('user/payments/{id?}', 'BillingController@getPaymentDetail');
Route::get('login/facebook', 'SocialiteController@redirectToFacebook');
Route::get('login/facebook/callback', 'SocialiteController@handleFacebookCallback');
Route::get('login/google', 'SocialiteController@redirectToGoogle');
Route::get('login/google/callback', 'SocialiteController@handleGoogleCallback');
Route::delete('user/addresses/{code?}', 'UserController@deleteAddress');
Route::post('user/billingAddress/{code?}', 'UserController@postSetAsBillingAddress');
Route::post('user/editAddress', 'UserController@postEditAddress');

Route::get('a/products/{category?}/{page?}', 'ProductController@getproducts');
Route::get('a/product-detail/{code?}', 'ProductController@getproduct');
Route::get('a/merchant/{merchant?}', 'MerchantController@getMerchantDetail');
Route::get('a/merchant/{merchant?}/products', 'ProductController@getproductsMerchant');

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
Route::get('mercado/return', 'MercadoPagoController@returnMerc');
Route::get('payu/cron', 'PayuController@cronPayU');
Route::get('billing/orders', 'BillingController@getOrders');
Route::get('billing/payments', 'BillingController@getPaymentsAdmin');

Route::get('merchant/register', 'MerchantController@getRegisterMerchant');
Route::get('merchantProducts/{code?}', 'UserController@getMerchant');
Route::get('a/merchants/{category?}', 'MerchantController@index');
Route::get('a/merchants/{category?}/nearby', 'MerchantController@getNearbyMerchants');
Route::get('a/merchants/{category?}/coverage', 'MerchantController@getCoverageMerchants');
Route::get('merchants/import', 'MerchantApiController@importMerchant');
Route::get('merchants/export', 'MerchantApiController@exportMerchant');
Route::get('merchants/export_orders', 'MerchantApiController@exportMerchantOrders');
Route::get('merchants/import_update', 'MerchantApiController@importUpdateMerchant');

Route::get('a/reports/{category?}', 'ReportController@index');
Route::get('a/reports/{category?}/nearby', 'ReportController@getNearbyReports');
Route::get('a/report/{slug?}', 'ReportController@getReportDetail');

Route::get('admin/store/products', 'ProductImportController@getProducts');
Route::get('admin/store/variants', 'ProductImportController@getVariants');
Route::get('admin/store/merchants', 'ProductImportController@getMerchants');
Route::get('admin/store/prod_categories', 'ProductImportController@getCategories');
Route::post('admin/store/products', 'ProductImportController@postProducts');
Route::post('admin/store/variants', 'ProductImportController@postVariants');
Route::post('admin/store/merchants', 'ProductImportController@postMerchants');
Route::post('admin/store/categories', 'ProductImportController@postCategories');
Route::get('admin/store/global', 'StoreExportController@getImport');
Route::post('admin/store/global', 'StoreExportController@postImport');
Route::get('admin/store/export', 'StoreExportController@getExport');
Route::get('location', 'MapExternalController@location');
Route::get('map/{code?}', 'MapExternalController@index');
Route::get('safereportsext/{code?}', 'MapExternalController@report');
Route::get('food/build_route_id/{id?}/{hash?}', 'FoodController@buildScenarioRouteId');
Route::get('food/build_complete_scenario/{scenario?}/{provider?}/{hash?}', 'FoodController@buildCompleteScenario');
Route::get('food/build_scenario_positive/{scenario?}/{provider?}/{hash?}', 'FoodController@buildScenarioPositive');
Route::get('food/get_scenario_structure/{scenario?}/{provider?}/{status?}/{hash?}', 'FoodController@getScenarioStructure');
Route::get('food/delete_deposit/{user?}/{hash?}', 'FoodController@cancelUserCredit');
Route::get('food/delete_last_lunch/{user?}/{hash?}', 'FoodController@cancelDelivery');
Route::get('food/regenerate_scenarios/{polygon?}/{hash?}', 'FoodController@regenerateScenarios');
Route::get('food/regenerate_deliveries', 'FoodController@regenerateDeliveries');
Route::get('food/summary/{polygon?}', 'FoodController@getSummaryShipping');
Route::get('food/routes', 'FoodController@getRoutes');
Route::get('food/largest_addresses', 'FoodController@getLargestAddresses');
Route::get('food/menu', 'FoodImportController@getMenu');
Route::get('food/deliveries', 'FoodImportController@getDeliveries');
Route::get('admin/zones', 'FoodImportController@getZones');
Route::get('food/messages', 'FoodImportController@getMessages');
Route::get('food/content', 'FoodImportController@getContent');
Route::post('food/menu', 'FoodImportController@postMenu');
Route::post('food/content', 'FoodImportController@postContent');
Route::post('admin/zones', 'ZonesController@postZones');
Route::post('food/messages', 'FoodImportController@postMessages');

Route::get('/purchase', function () {
    $className = "App\\Services\\Food";
    $rapigoClassName = "App\\Services\\Rapigo";
    $deliveries = App\Models\Delivery::where("status", "transit")->get();
    $rapigo = new $rapigoClassName;
    $gateway = new $className($rapigo); //// <--- this thing will be autoloaded
    $data = $gateway->getPurchaseOrder($deliveries);
    $data['level'] = "";
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
    $data = $gateway->getTotalEstimatedShipping("preorganize", "pending");
    return new App\Mail\RouteChoose($data['routes']);
});
Route::get('/scenario_select', function () {
    $className = "App\\Services\\Food";
    $rapigoClassName = "App\\Services\\Rapigo";
    $rapigo = new $rapigoClassName;
    $gateway = new $className($rapigo); //// <--- this thing will be autoloaded
    $data = $gateway->getShippingCosts("pending");
    return new App\Mail\ScenarioSelect($data['resultsPre'], $data['resultsSimple'], $data['winner']);
});

Route::get('/email_register', function () {
    return new App\Mail\Register("testCoupon");
});

Route::get('/email_payment_cash', function () {
    $user = App\Models\User::find(2);
    $payment = App\Models\Payment::find(118);
    $url = "http://www.google.com";
    $pdf = "http://www.google.com";
    return new App\Mail\EmailPaymentCash($payment, $user, $url, $pdf);
});
Route::get('/newsletter_descuento', function () {
    return new App\Mail\Newsletter4();
});
Route::get('/newsletter_padres2', function () {
    return new App\Mail\Newsletter3();
});
Route::get('/newsletter_padre2', function () {
    return new App\Mail\Newsletter3();
});
Route::get('/newsletter_sancho', function () {
    return redirect('newsletter_sistole_quadi');
});
Route::get('/newsletter_lift', function () {
    return new App\Mail\NewsletterLift();
});
Route::get('/newsletter_axure', function () {
    return new App\Mail\NewsletterAxure();
});
Route::get('/newsletter_geometry', function () {
    return new App\Mail\NewsletterGeometry();
});
Route::get('/newsletter_huge', function () {
    return new App\Mail\NewsletterHuge();
});
Route::get('/newsletter_catering', function () {
    return new App\Mail\Newsletter1();
});
Route::get('/newsletter_menu', function () {
    $className = "App\\Services\\Food";
    $gateway = new $className();
    $days = $gateway->getDataNewsletter();
    return new App\Mail\NewsletterMenus($days, "Octubre", "Octubre");
});
Route::get('/pedidos_lonchis', function () {
    return new App\Mail\Newsletter2();
});
Route::get('/newsletter_4', function () {
    return new App\Mail\Newsletter4();
});
Route::get('/newsletter_3', function () {
    return new App\Mail\Newsletter3();
});
