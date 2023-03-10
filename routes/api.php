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
Route::post('leads', 'LeadController@postLanding');
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
Route::get('mercadopago/payment_methods', 'MercadoPagoController@getPaymentMethods');
Route::get('mercadopago/cards', 'MercadoPagoController@getCards');
Route::post('mercadopago/pay_cc', 'MercadoPagoController@postPayCreditCard');
Route::post('mercadopago/pay_debit', 'MercadoPagoController@postPayDebitCard');
Route::post('mercadopago/pay_cash', 'MercadoPagoController@postPayCash');
Route::post('mercadopago/webhook', 'MercadoPagoController@webhook');
Route::post('billing/pay_cc/{source?}', 'BillingApiController@postPayCreditCard');
Route::post('billing/pay_in_bank/{source?}', 'BillingApiController@postPayInBank');
Route::post('billing/pay_ondelivery/{source?}', 'BillingApiController@postPayOnDelivery');
Route::post('billing/add_transaction_costs/{payment?}', 'BillingApiController@postAddTransactionCosts');
Route::get('billing/raw_sources/{source?}', 'BillingApiController@getRawSources');
Route::get('billing/payments', 'BillingApiController@getPayments');
Route::post('billing/pay_debit/{source?}', 'BillingApiController@postPayDebitCard');
Route::post('billing/pay_cash/{source?}', 'BillingApiController@postPayCash');
Route::post('billing/complete_paid/{platform?}', 'BillingApiController@postCompletePaidOrder');
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
Route::post('user/phone', 'UserApiController@registerPhone');
Route::get('user/authtokens', 'UserApiController@getTokens');
Route::post('user/change_password', 'AuthApiController@changePassword');
Route::post('admin/login', 'AuthApiController@checkAdminToken');
Route::post('user/address/{address?}/{type?}', 'UserApiController@setAddressType');
Route::post('user/credits/{user?}', 'OrderApiController@checkUserCredits');
Route::get('user/merchants', 'MerchantApiController@getUserMerchant');
Route::resource('user', 'UserApiController');

Route::post('deliveries/options', 'DeliveryController@postDeliveryOptions');
Route::post('deliveries/date', 'DeliveryController@posUpdateDeliveryDate');
Route::get('deliveries/pending', 'DeliveryController@getPendingDelivery');
Route::post('deliveries/address', 'DeliveryController@posUpdateDeliveryAddress');
Route::post('deliveries/cancel/{delivery?}', 'DeliveryController@postCancelDeliverySelection');
Route::resource('deliveries', 'DeliveryController');
Route::resource('articles', 'ArticleController');

Route::post('documents/{document}/sign ', 'DocumentController@signDocument');
Route::post('documents/{document}/verify_signature ', 'DocumentController@verifySignatures');
Route::resource('documents', 'DocumentController');
Route::resource('certificates', 'CertificateController');

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
Route::get('reports/detail', 'ReportApiController@getObject');
Route::get('reports/nearby', 'ReportApiController@getNearbyReports');
Route::get('reports/search', 'ReportApiController@textSearch');
Route::delete('reports/group/{groupId?}/{objectId?}', 'ReportApiController@removeObjectGroup');
Route::resource('reports', 'ReportApiController');


Route::post('merchants/share', 'ShareApiController@postAddFollower');
Route::post('merchants/status', 'MerchantApiController@updateStatus');
Route::post('merchants/coverage', 'MerchantApiController@checkCoverageMerchants');
Route::delete('merchants/group/{groupId?}/{objectId?}', 'MerchantApiController@removeObjectGroup');
Route::get('private/merchants', 'MerchantApiController@indexPrivate');
Route::get('private/merchants/detail', 'MerchantApiController@getObject');
Route::get('private/merchants/coverage', 'MerchantApiController@getCoverageMerchants');
Route::get('private/merchants/products', 'ProductApiController@getProductsMerchant');
Route::middleware('cache.headers:public;max_age=2628000;etag')->group(function () {
    Route::get('merchants/products', 'ProductApiController@getProductsMerchant');
    Route::get('merchants/nearby', 'MerchantApiController@getNearbyMerchants');
    Route::get('merchants/coverage', 'MerchantApiController@getCoverageMerchants');
    Route::get('merchants/detail', 'MerchantApiController@getObject');
    Route::get('merchants/nearby_all', 'MerchantApiController@getNearby');
    Route::get('merchants/payment_methods/{code?}', 'MerchantApiController@getPaymentMethodsMerchant');
    Route::get('merchants/{id?}/categories/{type?}', 'MerchantApiController@getCategoriesMerchant');
    Route::get('merchants/{id?}/active_categories', 'MerchantApiController@getActiveCategoriesMerchant');
    Route::get('merchants/search', 'MerchantApiController@textSearch');
//Route::post('merchants/status/{code?}', 'MerchantApiController@updateObjectStatus');


    Route::resource('merchants', 'MerchantApiController');
});


Route::get('imagesapi', 'FileApiController@getFiles');
Route::post('imagesapi', 'FileApiController@postFile');
Route::delete('imagesapi/{code?}', 'FileApiController@delete');

Route::get('products/group/{group?}/{page?}', 'ProductApiController@getProductsGroup');
Route::get('products/merchant/private/{merchant?}/{page?}', 'ProductApiController@getProductsPrivateMerchant');
Route::get('products/merchant/{merchant?}/{page?}', 'ProductApiController@getProductsMerchantOld');
Route::get('products/search', 'ProductApiController@textSearch');

Route::resource('products', 'ProductApiController');
Route::get('products/hash/{code?}', 'ProductApiController@getProductHash');
Route::get('products/favorites', 'ProductApiController@getFavoriteProducts');
Route::post('products/favorites', 'ProductApiController@checkFavoriteProducts');
Route::post('products/variant', 'ProductVariantApiController@store');
Route::post('products/share', 'ShareApiController@postAddFollower');
Route::patch('products/variant/{variant?}', 'ProductVariantApiController@update');
Route::post('products/variant/{variant?}', 'ProductVariantApiController@update');
Route::get('products/variant/{variant?}', 'ProductVariantApiController@show');
Route::delete('products/variant/{variant?}', 'ProductVariantApiController@destroy');

Route::post('cart/add', 'CartApiController@postAddCartItem');
Route::post('cart/check', 'CartApiController@postCheckCart');
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
Route::get('orders/active', 'OrderApiController@getOrder');
Route::post('orders/platform/shipping/{order?}/{platform?}/{platform_id?}', 'OrderApiController@setPlatformShippingCondition');
Route::get('orders/platform/shipping/{order?}/{platform?}/{platform_id?}', 'OrderApiController@getPlatformShippingPrice');
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

Route::get('items', 'ItemApiController@index');
Route::post('items/status', 'ItemApiController@fulfillItem');
Route::get('rapigo/status/{key?}', 'RapigoController@getKeyStatus');
Route::get('admin/store/products', 'ProductImportApiController@getProducts');
Route::get('admin/store/variants', 'ProductImportApiController@getVariants');
Route::get('admin/store/merchants', 'ProductImportApiController@getMerchants');
Route::get('admin/store/categories', 'ProductImportApiController@getCategories');
Route::get('admin/zones', 'ZonesController@getZones');
Route::delete('admin/zones/{item?}', 'ZonesController@deleteZoneItem');
Route::patch('admin/zones/{item?}', 'ZonesController@updateZoneItem');
Route::post('admin/zones', 'ZonesController@createZoneItem');
Route::post('admin/merchant/content', 'StoreExportApiController@postMerchantExport');
Route::post('admin/merchant/orders', 'StoreExportApiController@postStoreOrdersExport');
Route::get('food/menu', 'FoodApiController@getMenu');
Route::get('food/indicators', 'FoodApiController@getActiveIndicators');
Route::get('food/tips', 'FoodApiController@getTips');
Route::get('food/newsletter', 'FoodApiController@sendNewsletter');
Route::get('food/messages', 'FoodApiController@getMessages');
Route::delete('food/content/{item?}', 'FoodApiController@deleteContentItem');
Route::delete('food/messages/{item?}', 'FoodApiController@deleteMessageItem');
Route::get('food/build_route_id/{id?}', 'FoodApiController@buildScenarioRouteId');
Route::get('food/build_complete_scenario/{scenario?}/{provider?}', 'FoodApiController@buildCompleteScenario');
Route::get('food/build_scenario_positive/{scenario?}/{provider?}', 'FoodApiController@buildScenarioPositive');
Route::get('food/get_scenario_structure', 'FoodApiController@getScenarioStructure');
Route::post('food/build_scenario_logistics', 'FoodApiController@buildScenarioLogistics');
Route::post('food/update_dish', 'FoodApiController@updateMissingDish');
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
Route::post('auth/social', 'AuthApiController@checkSocialToken');
Route::post('auth/verify_two_factor', 'AuthApiController@verifyTwoFactorToken');
Route::post('auth/unlock', 'AuthApiController@unlockMedical');
Route::post('auth/update_medical', 'AuthApiController@updateMedical');
Route::post('auth/verify_codes', 'AuthApiController@verifyCodes');
Route::post('auth/validate_codes', 'AuthApiController@validateCodes');
Route::post('auth/update_codes', 'AuthApiController@updateCodes');
Route::post('auth/password_request', 'AuthApiController@changePasswordRequest');
Route::post('auth/password_request_update', 'AuthApiController@changePasswordUpdate');
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
Route::get('categories', 'CategoriesApiController@getCategoriesType');
Route::get('bookings', 'BookingApiController@getBookingsObject');
Route::get('bookings/user', 'BookingApiController@getObjectsWithBookingUser');
Route::get('bookings/{code?}', 'BookingApiController@getBooking');

Route::delete('bookings/{booking?}', 'BookingApiController@deleteBookingObject');
Route::post('bookings', 'BookingApiController@postAddBookingObject');
Route::post('bookings/edit', 'BookingApiController@postEditBookingObject');
Route::post('bookings/now', 'BookingApiController@postImmediateBookingObject');
Route::post('bookings/status', 'BookingApiController@postChangeStatusBookingObject');
Route::get('bookings/{code?}/check', 'BookingApiController@getCheckExistingBookingObject');
Route::post('bookings/schedule', 'BookingApiController@postRescheduleBookingObject');
Route::post('bookings/connection', 'BookingApiController@postRegisterConnection');
Route::post('bookings/connection_end', 'BookingApiController@postleaveChatroom');
Route::get('availabilities', 'BookingApiController@getAvailabilitiesObject');
Route::post('availabilities', 'BookingApiController@postAddAvailabilitiesObject');
Route::delete('availabilities', 'BookingApiController@deleteAvailabilityObject');

Route::post('runner/route/start', 'RunnerApiController@postRouteStarted');
Route::post('runner/route/complete', 'RunnerApiController@postRouteCompleted');
Route::post('runner/stop/arrived', 'RunnerApiController@postStopArrived');
Route::post('runner/stop/failed', 'RunnerApiController@postStopFailed');
Route::post('runner/stop/complete', 'RunnerApiController@postStopCompleted');

Route::post('zoom/webhook', 'ZoomController@webhook');
