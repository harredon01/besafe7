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


Route::delete('contacts/{code?}', 'UserApiController@deleteContact');
Route::get('contacts', 'UserApiController@getContacts');
Route::post('contacts/check', 'UserApiController@checkContacts');
Route::post('contacts', 'UserApiController@importContactsId');
Route::post('contacts/level', 'UserApiController@updateContactsLevel');
Route::post('contacts/add/{code?}', 'UserApiController@addContact');
Route::get('contacts/block/{code?}', 'UserApiController@blockContact');
Route::get('contacts/unblock/{code?}', 'UserApiController@unblockContact');
Route::get('user/medical/{code?}', 'UserApiController@notificationMedical');
Route::post('user/token', 'UserApiController@registerToken');
Route::get('user/authtokens', 'UserApiController@getTokens');
Route::post('user/change_password', 'UserApiController@changePassword');
Route::resource('user', 'UserApiController');

Route::get('groups/admin', 'GroupController@getAdminGroups');
Route::post('groups/admin', 'GroupController@setAdminGroup');
Route::get('groups/leave/{code?}', 'GroupController@leaveGroup');
Route::post('groups/remove', 'GroupController@removeGroup'); 
Route::post('groups/invite', 'GroupController@inviteUsers');
Route::resource('groups', 'GroupController');

Route::get('alerts', 'AlertsApiController@getNotifications');
Route::delete('alerts/{code?}', 'AlertsApiController@deleteNotification');
Route::get('alerts/count_unread', 'AlertsApiController@getCountNotificationsUnread');
Route::post('alerts/read', 'AlertsApiController@openNotifications');
Route::post('alerts/setread', 'AlertsApiController@postMarkAsDownloaded');
Route::get('alerts/request_ping/{code?}', 'AlertsApiController@getRequestPing');
Route::post('alerts/reply_ping', 'AlertsApiController@postReplyPing');

Route::post('emergency', 'AlertsApiController@postEmergency');
Route::post('emergency/stop', 'AlertsApiController@postStopEmergency');

Route::post('reports/share', 'AlertsApiController@postAddFollower');
Route::post('reports/approve/{code?}', 'ReportApiController@approveReport');
Route::get('reports/hash/{code?}', 'ReportApiController@getReportHash');
Route::resource('reports', 'ReportApiController');

Route::get('merchants', 'MerchantApiController@getMerchants');
Route::get('merchants/import', 'MerchantApiController@importMerchant');
Route::get('merchants/export', 'MerchantApiController@exportMerchant');
Route::get('merchants/export_orders', 'MerchantApiController@exportMerchantOrders');
Route::get('merchants/import_update', 'MerchantApiController@importUpdateMerchant');
Route::get('merchants/nearby', 'MerchantApiController@getNearby');
Route::get('merchants/payment_methods/{code?}', 'MerchantApiController@getPaymentMethodsMerchant');
Route::post('merchants/search', 'MerchantApiController@findMerchant');


Route::post('imagesapi', 'FileApiController@postFile');
Route::delete('imagesapi/{code?}', 'FileApiController@delete');

Route::post('ordersapi/add_item', 'OrderApiController@addCartItem');
Route::post('ordersapi/update_item', 'OrderApiController@updateItem');
Route::get('ordersapi/cart', 'OrderApiController@getCart');
Route::get('ordersapi/clear', 'OrderApiController@clearCart');
Route::post('ordersapi/shipping', 'OrderApiController@setShippingAddress');
Route::post('ordersapi/set_details', 'OrderApiController@setOrderDetails');
Route::get('ordersapi/confirm/{code?}', 'OrderApiController@confirmOrder');
Route::get('ordersapi/deny/{code?}', 'OrderApiController@denyOrder');

Route::get('auth/logout', 'AuthApiController@getLogout');
Route::post('auth/verify_medical', 'AuthApiController@verifyMedical');
Route::post('auth/unlock', 'AuthApiController@unlockMedical');
Route::post('auth/update_medical', 'AuthApiController@updateMedical');
Route::post('auth/verify_codes', 'AuthApiController@verifyCodes');
Route::post('auth/validate_codes', 'AuthApiController@validateCodes');
Route::post('auth/update_codes', 'AuthApiController@updateCodes');
Route::post('auth/register', 'AuthApiController@create');

Route::post('locations/user', 'LocationController@postLocation');
Route::post('locations/follower', 'AlertsApiController@postAddFollower');
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

Route::get('messages/user/{code?}', 'AlertsApiController@getMessagesUser');
Route::get('messages/group/{code?}', 'AlertsApiController@getMessagesGroup');
Route::post('messages/send', 'AlertsApiController@postMessage');