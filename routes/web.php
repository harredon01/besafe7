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

Route::get('pay/banks', 'PayuController@getBanks');
Route::get('pay/payment_methods', 'PayuController@getPaymentMethods');
Route::post('pay/pay_cc', 'PayuController@postPayCreditCard');
Route::post('pay/pay_debit', 'PayuController@postPayDebitCard');
Route::post('pay/pay_cash', 'PayuController@postPayCash');
Route::post('pay/response', 'PayuController@webhookPayU');
Route::get('pay/return', 'PayuController@returnPayU');
Route::get('pay/cron', 'PayuController@cronPayU');



Route::get('merchant/{code?}', 'MerchantController@getMerchantOrders');
Route::get('merchantProducts/{code?}', 'UserController@getMerchant');

Route::get('map/{code?}', 'MapExternalController@index');
Route::get('safereportsext/{code?}', 'MapExternalController@report');