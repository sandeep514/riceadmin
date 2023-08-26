<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('save/order',['as'=>'save.order','uses' => 'ApiController@saveOrder']);

Route::post('login',['uses'=>'ApiController@login']);

Route::group(['middleware'=>'auth:api'], function(){
    Route::get('pre-load-sample-data',      ['uses'=>'ApiController@preLoadSampleEntryContent']);
    Route::post('sample/save',              ['uses'=>'ApiController@saveSampleEntry']);
    Route::get('pending/courier/samples',   ['uses'=>'ApiController@pendingCourierSamples']);
    Route::post('courier/save',             ['uses'=>'ApiController@saveCourier']);
    Route::post('millstatus/save',          ['uses'=>'ApiController@saveMillStatus']);
});

Route::get('prices/{state}/{type}','ApiController@getPrices');
Route::get('list/port',                 ['uses'=>'ApiController@getPorts']);
Route::get('get/price/{state}/{riceType}/{rice}/{timePeriod}' , ['as' => 'get.price.by.period' ,'uses' => 'ApiController@getpriceByTimePeriod']);
Route::get('get/plans' ,                ['as' => 'get.plans'                , 'uses' => 'ApiController@getPlans']);
Route::get('get/price/states' ,         ['as' => 'get.price.states'         , 'uses' => 'ApiController@getPriceStates']);


Route::get('get/gallery/list' ,         ['as' => 'get.gallery.details'      , 'uses' => 'ApiController@getGalleryData']);
Route::get('get/gallery/details/{id}' , ['as' => 'get.gallery.details.id'   , 'uses' => 'ApiController@getGalleryDetails']);
Route::POST('save/user' ,               ['as' => 'save.user' 			    , 'uses' => 'ApiController@saveUser']);
Route::POST('update/user' ,               ['as' => 'update.user'            , 'uses' => 'ApiController@updateUser']);
Route::POST('verify/user' ,             ['as' => 'verify.user'              , 'uses' => 'ApiController@verifyUser']);

Route::POST('change/password' ,         ['as' => 'change.password'          , 'uses' => 'ApiController@changePassword']);

Route::GET('send/otp/{id}' ,            ['as' => 'send.otp'                 , 'uses' => 'ApiController@sendOTP']);
Route::GET('resend/otp/{mobile}' ,            ['as' => 'resend.otp'                 , 'uses' => 'ApiController@resendOTP']);
Route::GET('verify/otp/{number}/{id}' , ['as' => 'verify.otp'               , 'uses' => 'ApiController@verifyOTP']);

Route::get('get/basmati/state' ,        ['as' => 'get.basmati.state'        , 'uses' => 'ApiController@getBasmatiState']);
Route::get('get/nonbasmati/state' ,     ['as' => 'get.nonbasmati.state'     , 'uses' => 'ApiController@getNONBasmatiState']);
Route::get('get/images/for/dashboard' , ['as' => 'get.images.for.dashboard' , 'uses' => 'ApiController@getImagesForDashboard']);

Route::post('send/message' ,            ['as' => 'send.message'             , 'uses' => 'MessageController@sendMessage']);
Route::post('update/user/token' ,       ['as' => 'update.user.token'        , 'uses' => 'ApiController@updateUserToken']);

//ChartIntervals
Route::get('get/chartinterval' ,        ['as' => 'get.chartinterval'        , 'uses' => 'ApiController@getChartinterval']);

//Orders
Route::get('check/user/plan/{userId}',  ['as'=>'get.order'                  , 'uses' => 'ApiController@isUserOrderExistAndActive']);

//Users
Route::post('update/user/token',        ['as'=>'update.user.token'          , 'uses' => 'ApiController@updateUserTokenById']);

//Message
Route::get('get/user/messages/count/{userId}' , ['as' => 'get.user.messages.count' , 'uses' => 'ApiController@getUserMessageCount']);
Route::get('get/message/contacts/list',['as'=>'get.message.contact','uses'=>'ApiController@getMessageContacts']);
Route::get('get/message/{from}/{to}',['as'=>'get.message','uses'=>'ApiController@getMessagesByIds']);
Route::post('save/message',['as'=>'save.message','uses'=>'ApiController@saveMessage']);

Route::get('get/message/contacts/list/RefactorCode',['as'=>'get.message.contact.refactor','uses'=>'ApiController@getMessageContactsRefator']);
Route::get('check/user/expired/{id}',['as'=>'check.user.expired','uses'=>'ApiController@checkUserExpired']);

Route::get('get/transport/states' ,     ['as' => 'get.transport.states' , 'uses' => 'ApiController@getTransportStates']);
Route::get('get/port/details/{state}' , ['as' => 'get.port.details' , 'uses' => 'ApiController@getPortDetails']);
Route::get('get/user/plan/{user_id}' ,  ['as' => 'get.user.plan' , 'uses' => 'ApiController@getUserPlan']);
Route::get('get/chat/status' ,          ['as' => 'get.chat.status' , 'uses' => 'ApiController@getChatStatus']);

//TV app
Route::get('get/all/state/list' , ['as' => 'get.all.basmati' , 'uses' => 'ApiController@getAllStateList']);
Route::get('get/all/basmati/{state}' , ['as' => 'get.all.basmati' , 'uses' => 'ApiController@getAllBasmatiPrice']);
Route::get('get/all/nonbasmati/{state}' , ['as' => 'get.all.nonbasmati' , 'uses' => 'ApiController@getAllNONBasmatiPrice']);

//Notification
Route::get('get/user/notification/{user_id?}', ['as' => 'get.user.notification', 'uses' => 'NotificationController@getUserNotifications']);
Route::get('get/ports', ['as' => 'get.user.notification', 'uses' => 'ApiController@getPortsInOrder']);

//version
Route::get('get/latest/version', ['as' => 'get.latest.version', 'uses' => 'ApiController@getLatestAndroidVersion']);

//Ocean Freight
Route::get('get/ocean/freight', ['as' => 'get.ocean.freight', 'uses' => 'ApiController@getOceanFreight']);

//get USD Prices
Route::get('get/usd/prices/{id}' , ['as' => 'get.usd.prices' , 'uses' => 'ApiController@getUSDPrices']);
Route::get('get/distinct/region' , ['as' => 'get.distinct.region' , 'uses' => 'ApiController@getDistinctRegion']);
Route::get('get/quality/details/{id}' , ['as' => 'get.quality.details' , 'uses' => 'ApiController@getQualityDetails']);

Route::get('get/all/ports/{riceQualityId}/{userId}' , ['as' => 'get.all.ports' , 'uses' => 'ApiController@getAllPorts']);
Route::get('get/data/for/buyer' , ['as' => 'get.data.for.buyer' ,'uses' =>'ApiController@getAllPortsgetDataForBuyer']);
Route::POST('add/rice/query' , ['as' => 'add.rice.query' ,'uses' =>'ApiController@addRiceQuality']);
Route::POST('save/bid' , ['as' => 'save.bid' ,'uses' =>'ApiController@saveBid']);
Route::get('get/buyer/details/{id}' , ['as' => 'get.buyer.details' ,'uses' =>'ApiController@getBuyerDetails']);
Route::get('get/calculator/data' , ['as' => 'get.calculator.data' ,'uses' =>'ApiController@getCalculatorData']);
Route::POST('save/usd/prices' , ['as' => 'save.usd.prices' ,'uses' =>'ApiController@saveUSDPrices']);
Route::get('get/my/bids/{id}' , ['as' => 'get.my.bids' ,'uses' =>'ApiController@getMyBids']);
Route::POST('save/user/bid' , ['as' => 'save.user.bid' ,'uses' =>'ApiController@saveUserBid']);
Route::get('get/buyer/list' , ['as' => 'get.buyer.list' ,'uses' =>'ApiController@getAllVendors']);


Route::get('get/usd/plans' , ['as' => 'get.usd.plan' ,'uses' =>'ApiController@getUSDPlans']);
Route::get('get/bag/vendors' , ['as' => 'get.bag.vendors' ,'uses' =>'ApiController@getBagVendors']);

Route::get('get/countries/list' , ['as' => 'get.countries.list' ,'uses' =>'ApiController@getCountryList']);
Route::get('get/contact/details' , ['as' => 'get.contact.details' ,'uses' =>'ApiController@getContactDetails']);
Route::get('get/hot/deals/{id}' , ['as' => 'get.hot.deals' ,'uses' =>'ApiController@getHotDeals']);
Route::POST('update/counter/status' , ['as' => 'update.counter.status' ,'uses' =>'ApiController@updateCounterStatus']);
Route::POST('update/port' , ['as' => 'update.port' ,'uses' =>'ApiController@updatePort']);
Route::POST('accept/hot/deal/notification' , ['as' => 'accept.hot.deal.notification' ,'uses' =>'ApiController@acceptHotDealNotification']);
Route::POST('payment/success' , ['as' => 'payment.success' ,'uses' =>'ApiController@paymentSuccess']);

Route::get('start/trial/period/{userId}' , ['as' => 'start.trial.period' , 'uses' => 'ApiController@startTrialPerid']);

Route::get('user/notification/{userId}' , ['as' => 'user.notification' , 'uses' => 'ApiController@userNotification']);
Route::get('clear/notification/{userId}' , ['as' => 'clear.notification' , 'uses' => 'ApiController@clearNotifications']);
Route::get('delete/user/{userId}' , ['as' => 'delete.user' , 'uses' => 'ApiController@deleteUser']);
Route::POST('get/orderid/razorpay' , ['as' => 'get.orderid.razorpay' , 'uses' => 'ApiController@getRazorpayOrderId']);

Route::post('/check/customer', ['as' => 'stripe.customer' , 'uses' => 'StripeController@checkIfCustomer']);
// Route::get('/stripe-payment', ['as' => 'stripe.pay' , 'uses' => 'StripeController@handleGet']);
Route::POST('/stripe-payment', ['as' => 'stripe.payment' , 'uses' => 'StripeController@handlePost']);


Route::get('get/brand/list' , ['as' => 'get.brand.list' , 'uses' => 'ApiController@getBrandList']);
Route::get('get/rice/qualities/{riceType}' , ['as' => 'get.rice.qualities' , 'uses' => 'ApiController@getRiceQualities']);
Route::get('get/rice/qualities/name/{riceId}' , ['as' => 'get.rice.qualities.name' , 'uses' => 'ApiController@getRiceQualitiesName']);
Route::get('get/rice/wand/{riceNameId}' , ['as' => 'get.rice.wand' , 'uses' => 'ApiController@getRiceWand']);



Route::get('get/seller/inr/packing' , ['as' => 'get.seller.inr.packing' , 'uses' => 'ApiController@getSellerPackingINR']);


Route::PATCH('submit/sell/query' , ['as' => 'submit.sell.query' , 'uses' => 'ApiController@SubmitSellQuery']);
