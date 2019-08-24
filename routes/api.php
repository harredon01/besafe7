<?php

use Illuminate\Http\Request;

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

Route::resource('addresses', 'AddressApiController');
Route::resource('coverage', 'CoveragePolygonController');
Route::post('subscriptions/{source?}', 'SubscriptionApiController@store');
Route::get('subscriptions/{source?}', 'SubscriptionApiController@index');
Route::post('subscriptions/{source?}/existing', 'SubscriptionApiController@storeExistingSource');
Route::delete('subscriptions/{source?}/{id?}', 'SubscriptionApiController@destroy');
Route::post('subscriptions/{source?}/{id?}', 'SubscriptionApiController@edit');
Route::patch('subscriptions/{source?}/{id?}', 'SubscriptionApiController@edit');
Route::put('subscriptions/{source?}/{id?}', 'SubscriptionApiController@edit');
Route::get('subscriptionsearch/{type?}/{object?}', 'SubscriptionApiController@getSubscriptionsObject');
//Route::get('subscriptions/{source?}', 'SubscriptionApiController@show');

Route::get('sources', 'SourceApiController@quickSources');
Route::post('sources/{source?}', 'SourceApiController@store');
Route::get('sources/{source?}', 'SourceApiController@index');
Route::delete('sources/{source?}/{id?}', 'SourceApiController@destroy');
//Route::post('sources/{source?}/{id?}', 'SourceApiController@edit');
//Route::patch('sources/{source?}/{id?}', 'SourceApiController@edit');
//Route::put('sources/{source?}/{id?}', 'SourceApiController@edit');
Route::get('sources/{source?}/{id?}', 'SourceApiController@show');
Route::post('sources/{source?}/default', 'SourceApiController@setAsDefault');

Route::get('plans', 'SourceApiController@getPlans');
Route::post('hife_webhooks/stripe', 'WebhookController@handleWebhookStripe');
Route::post('hife_webhooks/payu', 'WebhookController@handleWebhookPayu');

Route::get('payu/banks', 'PayuController@getBanks');
Route::get('payu/payment_methods', 'PayuController@getPaymentMethods');
Route::post('payu/pay_cc', 'PayuController@postPayCreditCard');
Route::post('payu/pay_debit', 'PayuController@postPayDebitCard');
Route::post('payu/pay_cash', 'PayuController@postPayCash');
Route::post('billing/pay_cc/{source?}', 'BillingApiController@postPayCreditCard');
Route::post('billing/pay_in_bank/{source?}', 'BillingApiController@postPayInBank');
Route::post('billing/add_transaction_costs/{payment?}', 'BillingApiController@postAddTransactionCosts');
Route::get('billing/raw_sources/{source?}', 'BillingApiController@getRawSources');
Route::get('billing/payments', 'BillingApiController@getPayments');
Route::post('billing/pay_debit/{source?}', 'BillingApiController@postPayDebitCard');
Route::post('billing/pay_cash/{source?}', 'BillingApiController@postPayCash');
Route::post('billing/retry/{payment?}', 'BillingApiController@retryPayment');
Route::post('payu/all', 'PayuController@postcreateAll');
Route::post('payu/webhook', 'PayuController@webhookPayU');
Route::post('rapigo/webhook', 'RapigoController@webhook');


Route::delete('contacts/{code?}', 'ContactsApiController@deleteContact');
Route::get('contacts', 'ContactsApiController@getContacts');
Route::post('contacts/check', 'ContactsApiController@checkContacts');
Route::post('contacts', 'ContactsApiController@importContactsId');
Route::post('contacts/level', 'ContactsApiController@updateContactsLevel');
Route::post('contacts/add/{code?}', 'ContactsApiController@addContact');
Route::get('contacts/block/{code?}', 'ContactsApiController@blockContact');
Route::get('contacts/code/{code?}', 'ContactsApiController@getContactByCode');
Route::get('contacts/email/{email?}', 'ContactsApiController@getContactByEmail');
Route::get('contacts/unblock/{code?}', 'ContactsApiController@unblockContact');
Route::get('user/medical/{code?}', 'AuthApiController@notificationMedical');
Route::post('user/token', 'UserApiController@registerToken');
Route::get('user/authtokens', 'UserApiController@getTokens');
Route::post('user/change_password', 'AuthApiController@changePassword');
Route::post('user/address/{address?}/{type?}', 'UserApiController@setAddressType');
Route::post('user/credits/{user?}', 'OrderApiController@checkUserCredits');
Route::resource('user', 'UserApiController');

Route::post('deliveries/options', 'DeliveryController@postDeliveryOptions');
Route::post('deliveries/cancel/{delivery?}', 'DeliveryController@postCancelDeliverySelection');
Route::resource('deliveries', 'DeliveryController');
Route::resource('articles', 'ArticleController');

Route::resource('routes', 'RouteController');

Route::get('groups/leave/{code?}', 'GroupController@leaveGroup');
Route::get('groups/code/auth/{id?}', 'GroupController@getGroupCode');
Route::post('groups/code/refresh/{id?}', 'GroupController@regenerateGroupCode');
Route::get('groups/code/{code?}', 'GroupController@getGroupByCode');
Route::post('groups/code/{code?}', 'GroupController@joinGroupByCode');
Route::post('groups/admin_users', 'GroupController@getAdminGroupUsers');
Route::post('groups/status', 'GroupController@changeStatusGroup'); 
Route::post('groups/invite', 'GroupController@inviteUsers');
Route::resource('groups', 'GroupController');

Route::get('alerts', 'AlertsApiController@getNotifications');
Route::delete('alerts/{code?}', 'AlertsApiController@deleteNotification');
Route::get('alerts/count_unread', 'AlertsApiController@getCountNotificationsUnread');
Route::post('alerts/read', 'AlertsApiController@openNotifications');
Route::get('alerts/read_all', 'AlertsApiController@readAllNotifications');
Route::post('alerts/setread', 'AlertsApiController@postMarkAsDownloaded');
Route::get('alerts/request_ping/{code?}', 'AlertsApiController@getRequestPing');
Route::post('alerts/reply_ping', 'AlertsApiController@postReplyPing');

Route::post('emergency', 'AlertsApiController@postEmergency');
Route::post('emergency/stop', 'AlertsApiController@postStopEmergency');

Route::post('reports/share', 'ShareApiController@postAddFollower');
Route::post('reports/approve/{code?}', 'ReportApiController@approveReport');
Route::post('reports/status/{code?}', 'ReportApiController@updateObjectStatus');
Route::get('reports/hash/{code?}', 'ReportApiController@getReportHash');
Route::get('reports/nearby', 'ReportApiController@getNearbyReports');
Route::delete('reports/group/{groupId?}/{objectId?}', 'ReportApiController@removeObjectGroup');
Route::resource('reports', 'ReportApiController');


Route::get('merchants/import', 'MerchantApiController@importMerchant');
Route::post('merchants/share', 'ShareApiController@postAddFollower');
Route::get('merchants/export', 'MerchantApiController@exportMerchant');
Route::get('merchants/hash/{code?}', 'MerchantApiController@getMerchantHash');
Route::get('merchants/export_orders', 'MerchantApiController@exportMerchantOrders');
Route::get('merchants/import_update', 'MerchantApiController@importUpdateMerchant');
Route::get('merchants/nearby', 'MerchantApiController@getNearbyMerchants');
Route::get('merchants/nearby_all', 'MerchantApiController@getNearby');
Route::get('merchants/payment_methods/{code?}', 'MerchantApiController@getPaymentMethodsMerchant');
Route::post('merchants/search', 'MerchantApiController@findMerchant');
Route::post('merchants/status/{code?}', 'MerchantApiController@updateObjectStatus');
Route::delete('merchants/group/{groupId?}/{objectId?}', 'MerchantApiController@removeObjectGroup');
Route::resource('merchants', 'MerchantApiController');


Route::post('imagesapi', 'FileApiController@postFile');
Route::delete('imagesapi/{code?}', 'FileApiController@delete');

Route::get('products/group/{group?}/{page?}', 'ProductApiController@getProductsGroup');
Route::get('products/merchant/private/{merchant?}/{page?}', 'ProductApiController@getProductsPrivateMerchant');
Route::get('products/merchant/{merchant?}/{page?}', 'ProductApiController@getProductsMerchant');

Route::resource('products', 'ProductApiController');
Route::get('products/hash/{code?}', 'ProductApiController@getProductHash');
Route::post('products/variant', 'ProductVariantApiController@store');
Route::post('products/share', 'ShareApiController@postAddFollower');
Route::patch('products/variant/{variant?}', 'ProductVariantApiController@update');
Route::post('products/variant/{variant?}', 'ProductVariantApiController@update'); 
Route::get('products/variant/{variant?}', 'ProductVariantApiController@show');
Route::delete('products/variant/{variant?}', 'ProductVariantApiController@destroy');

Route::post('cart/add', 'CartApiController@postAddCartItem');
Route::post('cart/add/custom', 'CartApiController@postAddCustomCartItem');
Route::post('cart/update', 'CartApiController@postUpdateCartItem');
Route::post('cart/update_custom', 'CartApiController@postUpdateCustomCartItem');
Route::post('cart/mass_update', 'CartApiController@postUpdateCartItems');
Route::get('cart/get', 'CartApiController@getCart');
Route::get('cart/checkout', 'CartApiController@getCheckoutCart');
Route::post('cart/clear', 'CartApiController@postClearCart');
Route::get('cart/load', 'CartApiController@loadActiveCart');
Route::post('cart/order/{code?}', 'CartApiController@loadActiveCart');

Route::post('orders/shipping', 'OrderApiController@setShippingAddress');
Route::post('orders/platform/shipping/{order?}/{platform?}', 'OrderApiController@setPlatformShippingCondition');
Route::post('orders/tax', 'OrderApiController@setTaxesCondition');
Route::post('orders/coupon', 'OrderApiController@setCouponCondition');
Route::post('orders/set_details', 'OrderApiController@setOrderDetails');
Route::get('orders/confirm/{code?}', 'OrderApiController@confirmOrder');
Route::post('orders/prepare/{platform?}', 'OrderApiController@prepareOrder');
Route::post('orders/discounts/{platform?}/{order?}', 'OrderApiController@addDiscounts');
Route::post('orders/check/{order?}', 'OrderApiController@checkOrder');
Route::post('orders/recurring/{order?}', 'OrderApiController@setOrderRecurringType');
Route::get('orders/deny/{code?}', 'OrderApiController@denyOrder');
Route::resource('orders', 'OrderApiController');
Route::get('payments', 'BillingApiController@getPaymentsAdmin'); 
Route::post('payments/{payment?}/approve', 'FoodApiController@approvePayment'); 

Route::get('admin/store/products', 'ProductImportApiController@getProducts'); 
Route::get('admin/store/variants', 'ProductImportApiController@getVariants'); 
Route::get('admin/store/merchants', 'ProductImportApiController@getMerchants'); 
Route::get('admin/store/categories', 'ProductImportApiController@getCategories'); 

Route::get('food/menu', 'FoodApiController@getMenu');
Route::get('food/zones', 'FoodApiController@getZones');
Route::get('food/newsletter', 'FoodApiController@sendNewsletter');
Route::get('food/messages', 'FoodApiController@getMessages');
Route::delete('food/content/{item?}', 'FoodApiController@deleteContentItem');
Route::delete('food/zones/{item?}', 'FoodApiController@deleteZoneItem');
Route::patch('food/zones/{item?}', 'FoodApiController@updateZoneItem');
Route::post('food/zones', 'FoodApiController@createZoneItem');
Route::delete('food/messages/{item?}', 'FoodApiController@deleteMessageItem');
Route::get('food/build_route_id/{id?}', 'FoodApiController@buildScenarioRouteId');
Route::get('food/build_complete_scenario/{scenario?}/{provider?}', 'FoodApiController@buildCompleteScenario');
Route::get('food/build_scenario_positive/{scenario?}/{provider?}', 'FoodApiController@buildScenarioPositive');
Route::get('food/get_scenario_structure', 'FoodApiController@getScenarioStructure');
Route::post('food/build_scenario_logistics', 'FoodApiController@buildScenarioLogistics');
Route::get('food/regenerate_scenarios', 'FoodApiController@regenerateScenarios');
Route::get('food/regenerate_deliveries', 'FoodApiController@regenerateDeliveries');
Route::get('food/summary/{status?}', 'FoodApiController@getSummaryShipping');
Route::get('food/largest_addresses', 'FoodApiController@getLargestAddresses');
Route::post('food/delegate_deliveries', 'FoodApiController@delegateDeliveries');
Route::post('food/reminder', 'FoodApiController@sendReminder');
Route::get('food/route_detail/{delivery?}', 'FoodApiController@getRouteInfo');
Route::post('food/user/{user?}/address/{address?}', 'FoodApiController@updateUserDeliveriesAddress');
Route::get('food/deliveries', 'FoodApiController@getDeliveries');
Route::get('food/purchase_order', 'FoodApiController@getPurchaseOrder');
Route::get('food/route_organize', 'FoodApiController@showOrganizeEmails');

Route::get('auth/logout', 'AuthApiController@getLogout');
Route::post('auth/verify_medical', 'AuthApiController@verifyMedical');
Route::post('auth/unlock', 'AuthApiController@unlockMedical');
Route::post('auth/update_medical', 'AuthApiController@updateMedical');
Route::post('auth/verify_codes', 'AuthApiController@verifyCodes');
Route::post('auth/validate_codes', 'AuthApiController@validateCodes');
Route::post('auth/update_codes', 'AuthApiController@updateCodes');
Route::post('auth/clean', 'UserApiController@cleanServer');
Route::post('auth/register', 'UserApiController@create');
Route::post('store/reports', 'StoreExportApiController@getStoreExport');

Route::post('locations/user', 'LocationController@postLocation');
Route::post('locations/follower', 'ShareApiController@postAddFollower');
Route::get('locations', 'LocationController@index');

Route::get('historic_locations', 'LocationController@historicLocations');
Route::get('locations/hash', 'LocationController@getUserHash');
Route::get('locations/user', 'LocationController@getUserSharedLocations');
Route::get('locations/user/hash', 'LocationController@getUserHash');
Route::get('locations/moveold', 'LocationController@moveOldLocations');

Route::get('countries', 'LocationController@getCountries2');
Route::get('regions', 'LocationController@getRegions');
Route::get('cities', 'LocationController@getCities');
Route::post('cities/from', 'LocationController@getCitiesFrom');

Route::get('messages/user/{code?}', 'MessagesApiController@getMessagesUser');
Route::get('messages/group/{code?}', 'MessagesApiController@getMessagesGroup');
Route::get('messages/chat', 'MessagesApiController@getChat');
Route::get('messages/received', 'MessagesApiController@getReceivedChats');
Route::get('messages/support/{type?}/{object?}', 'MessagesApiController@getSupportAgent');
Route::post('messages/send', 'MessagesApiController@postMessage');

Route::get('ratings', 'RatingController@getRatingsObject');
Route::post('ratings', 'RatingController@postAddRatingObject');

Route::get('routes', 'RouteController@index');
Route::post('routes/{code?}/build', 'RouteController@buildRoute');
Route::post('routes/{code?}/return', 'RouteController@addReturnStop');
Route::post('routes/{code?}/stop/{stop?}', 'RouteController@updateRouteStop');
Route::post('routes/add_delivery', 'RouteController@updateRouteDelivery');
Route::delete('routes/{route?}', 'RouteController@deleteRoute');
Route::delete('stops/{stop?}', 'RouteController@deleteStop');
Route::post('routes/stop/{stop?}', 'RouteController@sendStopToNewRoute');

Route::post('favorites', 'FavoriteController@postAddFavoriteObject');
Route::post('favorites/delete', 'FavoriteController@postDeleteFavoriteObject');
Route::get('categories/{type?}', 'CategoriesApiController@getCategoriesType');
Route::get('bookings', 'BookingApiController@getBookingsObject');
Route::get('bookings/user', 'BookingApiController@getObjectsWithBookingUser');
Route::post('bookings', 'BookingApiController@postAddBookingObject');
Route::post('bookings/status', 'BookingApiController@postChangeStatusBookingObject');
Route::post('bookings/schedule', 'BookingApiController@postRescheduleBookingObject');
Route::get('availabilities', 'BookingApiController@getAvailabilitiesObject');
Route::post('availabilities', 'BookingApiController@postAddAvailabilitiesObject');

Route::post('runner/route/start', 'RunnerApiController@postRouteStarted');
Route::post('runner/route/complete', 'RunnerApiController@postRouteCompleted');
Route::post('runner/stop/arrived', 'RunnerApiController@postStopArrived');
Route::post('runner/stop/failed', 'RunnerApiController@postStopFailed');
Route::post('runner/stop/complete', 'RunnerApiController@postStopCompleted');