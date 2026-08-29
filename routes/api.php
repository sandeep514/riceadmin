<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PortalApiController;
use App\Http\Controllers\BrandInterestController;
use Illuminate\Support\Facades\Broadcast;
use Pusher\Pusher;

// Route::group(['middleware' => 'cors'], function () {

    Route::post('save/order',['as'=>'save.order','uses' => 'ApiController@saveOrder']);

    Route::post('login',['uses'=>'ApiController@login']);

    // Native app session check: invalid/old token => 401 session_expired (first phone kicked after second login).
    Route::get('app/session', ['as' => 'app.session', 'uses' => 'ApiController@checkAppSession'])->middleware('app.api.token');

    Route::group(['middleware'=>'app.api.token'], function(){
        Route::get('pre-load-sample-data',      ['uses'=>'ApiController@preLoadSampleEntryContent']);
        Route::post('sample/save',              ['uses'=>'ApiController@saveSampleEntry']);
        Route::get('pending/courier/samples',   ['uses'=>'ApiController@pendingCourierSamples']);
        Route::post('courier/save',             ['uses'=>'ApiController@saveCourier']);
        Route::post('millstatus/save',          ['uses'=>'ApiController@saveMillStatus']);
    });

    Route::post('web/brand-interest', [BrandInterestController::class, 'store'])->middleware('portal.api.token');


    Route::get('prices/{state}/{type}','ApiController@getPrices');
    Route::get('live/prices/today', ['as' => 'live.prices.today', 'uses' => 'ApiController@getLivePricesToday']);
    Route::get('web/prices/{state}/{type}','ApiController@getPricesWeb')->middleware('portal.api.token');
    Route::get('get/price/by/year/{state}/{type}','ApiController@getPricesByYear');

    
    Route::get('prices2/{state}/{type}','ApiController@getPrices2');
    Route::get('list/port',                 ['uses'=>'ApiController@getPorts']);
    // Must be registered BEFORE get/price/{state}/... or "chart/records/..." is captured as state=chart, riceType=records.
    Route::get('get/price/chart/records/{encodedRiceType}/{encodedRice}', ['as' => 'get.price.chart.records', 'uses' => 'ApiController@getPriceChartRecords']);
    Route::get('get/price/{state}/{riceType}/{rice}/{timePeriod}' , ['as' => 'get.price.by.period' ,'uses' => 'ApiController@getpriceByTimePeriod']);
    // Alias for web clients (same handler as get/price/...)
    Route::get('web/get/price/{state}/{riceType}/{rice}/{timePeriod}' , ['as' => 'get.web.price.by.period' ,'uses' => 'ApiController@getpriceByTimePeriod']);
    Route::get('get/plans' ,                ['as' => 'get.plans'                , 'uses' => 'ApiController@getPlans']);
    Route::get('/states' ,         ['as' => 'get.price.states'         , 'uses' => 'ApiController@getPriceStates']);

    Route::get('get/web/other/services' ,         ['as' => 'get.web.other.service'         , 'uses' => 'ApiController@getWebOtherServices', 'middleware' => 'portal.api.token']);


    Route::get('get/gallery/list' ,         ['as' => 'get.gallery.details'      , 'uses' => 'ApiController@getGalleryData']);
    Route::get('get/gallery/details/{id}' , ['as' => 'get.gallery.details.id'   , 'uses' => 'ApiController@getGalleryDetails']);
    Route::POST('save/user' ,               ['as' => 'save.userr' 			    , 'uses' => 'ApiController@saveUser']);
    Route::POST('check/email' ,             ['as' => 'check.email'              , 'uses' => 'ApiController@checkEmailExists']);
    Route::POST('update/user' ,               ['as' => 'update.userr'            , 'uses' => 'ApiController@updateUser']);
    Route::POST('verify/user' ,             ['as' => 'verify.user'              , 'uses' => 'ApiController@verifyUser']);

    Route::POST('change/password' ,         ['as' => 'change.password'          , 'uses' => 'ApiController@changePassword']);

    Route::GET('send/otp/{id}' ,            ['as' => 'send.otp'                 , 'uses' => 'ApiController@sendOTP']);
    Route::GET('resend/otp/{mobile}' ,      ['as' => 'resend.otp'               , 'uses' => 'ApiController@resendOTP']);
    Route::GET('verify/otp/{number}/{id}' , ['as' => 'verify.otp'               , 'uses' => 'ApiController@verifyOTP']);

    Route::get('get/basmati/state' ,        ['as' => 'get.basmati.state'        , 'uses' => 'ApiController@getBasmatiState']);
    Route::get('get/web/basmati/state' ,        ['as' => 'get.basmati.state'        , 'uses' => 'ApiController@getBasmatiStateForWeb', 'middleware' => 'portal.api.token']);
    Route::get('get/nonbasmati/state' ,     ['as' => 'get.nonbasmati.state'     , 'uses' => 'ApiController@getNONBasmatiState']);
    Route::get('get/web/nonbasmati/state' ,     ['as' => 'get.nonbasmati.state'     , 'uses' => 'ApiController@getNONBasmatiStateForWeb', 'middleware' => 'portal.api.token']);
    Route::get('get/images/for/dashboard' , ['as' => 'get.images.for.dashboard' , 'uses' => 'ApiController@getImagesForDashboard']);

    Route::post('send/message' ,            ['as' => 'send.message'             , 'uses' => 'ApiController@saveMessage']);
    // FCM device token: shared by legacy app (userType 1) and portal RN app (userType 2).
    Route::post('update/user/token',        ['as' => 'update.user.token', 'uses' => 'ApiController@updateUserTokenById', 'middleware' => 'app.or.portal.api.token']);

    //ChartIntervals
    Route::get('get/chartinterval' ,        ['as' => 'get.chartinterval'        , 'uses' => 'ApiController@getChartinterval']);

    //Orders
    Route::get('check/user/plan/{userId}',  ['as'=>'get.order'                  , 'uses' => 'ApiController@isUserOrderExistAndActive', 'middleware' => 'app.api.token']);

    //Message
    Route::get('get/user/messages/count/{userId}' , ['as' => 'get.user.messages.count' , 'uses' => 'ApiController@getUserMessageCount', 'middleware' => 'app.api.token']);
    Route::get('get/message/contacts/list',['as'=>'get.message.contact','uses'=>'ApiController@getMessageContacts', 'middleware' => 'app.api.token']);
    Route::get('get/message/{from}/{to}',['as'=>'get.message','uses'=>'ApiController@getMessagesByIds', 'middleware' => 'app.api.token']);
    Route::post('save/message',['as'=>'save.message','uses'=>'ApiController@saveMessage', 'middleware' => 'app.api.token']);

    Route::get('get/message/contacts/list/RefactorCode',['as'=>'get.message.contact.refactor','uses'=>'ApiController@getMessageContactsRefator', 'middleware' => 'app.api.token']);
    Route::get('check/user/expired/{id}',['as'=>'check.user.expired','uses'=>'ApiController@checkUserExpired', 'middleware' => 'app.api.token']);

    Route::get('get/transport/states' ,     ['as' => 'get.transport.states' , 'uses' => 'ApiController@getTransportStates']);
    Route::get('get/port/details/{state}' , ['as' => 'get.port.details' , 'uses' => 'ApiController@getPortDetails']);
    Route::get('get/user/plan/{user_id}' ,  ['as' => 'get.user.plan' , 'uses' => 'ApiController@getUserPlan', 'middleware' => 'app.api.token']);
    Route::get('get/chat/status' ,          ['as' => 'get.chat.status' , 'uses' => 'ApiController@getChatStatus']);

    //TV app
    Route::get('get/all/state/list' , ['as' => 'get.all.basmati' , 'uses' => 'ApiController@getAllStateList']);
    Route::get('get/all/basmati/{state}' , ['as' => 'get.all.basmatii' , 'uses' => 'ApiController@getAllBasmatiPrice']);
    Route::get('get/all/nonbasmati/{state}' , ['as' => 'get.all.nonbasmati' , 'uses' => 'ApiController@getAllNONBasmatiPrice']);

    //Notification
    Route::get('get/user/notification/{user_id?}', ['as' => 'get.user.notification', 'uses' => 'NotificationController@getUserNotifications', 'middleware' => 'app.api.token']);
    Route::get('get/ports', ['as' => 'get.user.notificationn', 'uses' => 'ApiController@getPortsInOrder']);

    //version
    Route::get('get/latest/version', ['as' => 'get.latest.version', 'uses' => 'ApiController@getLatestAndroidVersion']);

    //Ocean Freight
    Route::get('get/ocean/freight', ['as' => 'get.ocean.freight', 'uses' => 'ApiController@getOceanFreight']);
    Route::get('get/rice/forms', ['as' => 'get.rice.forms', 'uses' => 'ApiController@getRiceForms']);

    //get USD Prices
    Route::get('get/usd/prices/{id}' , ['as' => 'get.usd.prices' , 'uses' => 'ApiController@getUSDPrices']);
    Route::get('get/usd/prices2/{id}' , ['as' => 'get.usd.pricess' , 'uses' => 'ApiController@getUSDPrices2']);
    Route::get('get/distinct/region' , ['as' => 'get.distinct.region' , 'uses' => 'ApiController@getDistinctRegion']);
    Route::get('get/quality/details/{id}' , ['as' => 'get.quality.details' , 'uses' => 'ApiController@getQualityDetails']);

    Route::get('get/all/ports/{riceQualityId}/{userId}' , ['as' => 'get.all.ports' , 'uses' => 'ApiController@getAllPorts']);
    Route::get('get/data/for/buyer' , ['as' => 'get.data.for.buyer' ,'uses' =>'ApiController@getAllPortsgetDataForBuyer']);
    Route::get('web/get/data/for/buyer', ['as' => 'web.get.data.for.buyer', 'uses' => 'ApiController@getWebDataForBuyer', 'middleware' => 'portal.api.token']);
    Route::POST('add/rice/query' , ['as' => 'add.rice.query' ,'uses' =>'ApiController@addRiceQuality']);
    Route::POST('save/bid' , ['as' => 'save.bid' ,'uses' =>'ApiController@saveBid']);
    Route::get('get/buyer/details/{id}' , ['as' => 'get.buyer.details' ,'uses' =>'ApiController@getBuyerDetails']);
    Route::get('get/calculator/data' , ['as' => 'get.calculator.data' ,'uses' =>'ApiController@getCalculatorData']);
    Route::POST('save/usd/prices' , ['as' => 'save.usd.prices' ,'uses' =>'ApiController@saveUSDPrices']);
    Route::get('get/my/bids/{id}' , ['as' => 'get.my.bids' ,'uses' =>'ApiController@getMyBids']);
    Route::POST('save/user/bid' , ['as' => 'save.user.bid' ,'uses' =>'ApiController@saveUserBid']);
    Route::get('get/buyer/list' , ['as' => 'get.buyer.list' ,'uses' =>'ApiController@getAllVendors']);


    Route::get('get/usd/plans' , ['as' => 'get.usd.plans' ,'uses' =>'ApiController@getUSDPlans']);
    Route::get('get/bag/vendors' , ['as' => 'get.bag.vendors' ,'uses' =>'ApiController@getBagVendors']);

    Route::get('get/countries/list' , ['as' => 'get.countries.list' ,'uses' =>'ApiController@getCountryList']);
    Route::get('get/contact/details' , ['as' => 'get.contact.details' ,'uses' =>'ApiController@getContactDetails']);
    Route::get('get/hot/deals/{id}' , ['as' => 'get.hot.deals' ,'uses' =>'ApiController@getHotDeals']);
    Route::POST('update/counter/status' , ['as' => 'update.counter.status' ,'uses' =>'ApiController@updateCounterStatus']);
    Route::POST('update/port' , ['as' => 'update.port' ,'uses' =>'ApiController@updatePort']);
    Route::POST('accept/hot/deal/notification' , ['as' => 'accept.hot.deal.notification' ,'uses' =>'ApiController@acceptHotDealNotification']);
    Route::POST('payment/success' , ['as' => 'payment.success' ,'uses' =>'ApiController@paymentSuccess']);

    Route::get('start/trial/period/{userId}' , ['as' => 'start.trial.period' , 'uses' => 'ApiController@startTrialPerid', 'middleware' => 'app.api.token']);

    Route::get('user/notification/{userId}' , ['as' => 'user.notification' , 'uses' => 'ApiController@userNotification', 'middleware' => 'app.api.token']);
    Route::get('clear/notification/{userId}' , ['as' => 'clear.notification' , 'uses' => 'ApiController@clearNotifications', 'middleware' => 'app.api.token']);
    Route::get('delete/user/{userId}' , ['as' => 'delete.userr' , 'uses' => 'ApiController@deleteUser', 'middleware' => 'app.api.token']);
    Route::POST('get/orderid/razorpay' , ['as' => 'get.orderid.razorpay' , 'uses' => 'ApiController@getRazorpayOrderId', 'middleware' => 'app.api.token']);

    Route::post('/check/customer', ['as' => 'stripe.customer' , 'uses' => 'StripeController@checkIfCustomer']);
    // Route::get('/stripe-payment', ['as' => 'stripe.pay' , 'uses' => 'StripeController@handleGet']);
    Route::POST('/stripe-payment', ['as' => 'stripe.payment' , 'uses' => 'StripeController@handlePost']);


    Route::get('get/brand/list' , ['as' => 'get.brand.list' , 'uses' => 'ApiController@getBrandList']);
    Route::get('get/packing/by/{tradeType}' , ['as' => 'get.packing.tradeType' , 'uses' => 'ApiController@getPackingByTradeType']);
    Route::get('get/packing/type' , ['as' => 'get.packing.type' , 'uses' => 'ApiController@getBagPacking']);
    Route::get('get/cartoon/types' , ['as' => 'get.cartoon.types' , 'uses' => 'ApiController@getCartoonTypes']);
    Route::get('get/cylinder/types' , ['as' => 'get.cylinder.types' , 'uses' => 'ApiController@getCylinderTypes']);
    Route::get('get/lab/equipments' , ['as' => 'get.lab.equipments' , 'uses' => 'ApiController@getLabEquipments']);
    Route::get('get/machinery/equipments' , ['as' => 'get.machinery.equipments' , 'uses' => 'ApiController@getMachineryEquipments']);
    Route::get('get/packing/forms' , ['as' => 'get.packing.forms' , 'uses' => 'ApiController@getPackingForms']);
    Route::get('get/packing/size' , ['as' => 'get.packing.size' , 'uses' => 'ApiController@getPackingSize']);
    Route::get('web/get/farming-types', ['as' => 'web.get.farming.types', 'uses' => 'ApiController@getFarmingTypesWeb']);
    Route::get('get/rice/qualities/{riceType}' , ['as' => 'get.rice.qualities' , 'uses' => 'ApiController@getRiceQualities']);
    Route::get('get/rice/qualities/name/{riceId}' , ['as' => 'get.rice.qualities.name' , 'uses' => 'ApiController@getRiceQualitiesName']);
    Route::get('web/get/rice/qualities/name/{riceId}', ['as' => 'web.get.rice.qualities.name', 'uses' => 'ApiController@getWebRiceQualitiesName', 'middleware' => 'portal.api.token']);
    Route::get('get/rice/wand/{riceNameId}' , ['as' => 'get.rice.wand' , 'uses' => 'ApiController@getRiceWand']);
    Route::get('web/get/rice/wand/{riceNameId}', ['as' => 'web.get.rice.wand', 'uses' => 'ApiController@getWebRiceWand', 'middleware' => 'portal.api.token']);



    Route::get('get/designation' , ['as' => 'get.designation' , 'uses' => 'ApiController@getDesignation']);


    Route::get('get/seller/inr/packing' , ['as' => 'get.seller.inr.packing' , 'uses' => 'ApiController@getSellerPackingINR']);
    Route::get('get/trades/{userId}' , ['as' => 'get.trade' , 'uses' => 'ApiController@getTrade', 'middleware' => 'app.api.token']);
    // Web trades: All tab must NOT send trade_type (or send 0/"all"). Sell trades appear after all buys;
    // use trade_list_meta.sell_starts_at_page when only loading page 1. Manufacturer role does not filter types.
    Route::get('web/get/trades/{userId}' , ['as' => 'web.get.trade' , 'uses' => 'ApiController@getWebTrades', 'middleware' => 'portal.api.token']);
    Route::get('get/personal/trades/{userId}' , ['as' => 'get.personal.trade' , 'uses' => 'ApiController@getPersonalTrade', 'middleware' => 'app.api.token']);
    Route::match(['get', 'post'], 'get/all/trades/count', [
        'as' => 'get.personal.trades.count',
        'uses' => 'ApiController@getTradeCounts',
        'middleware' => 'app.api.token',
    ]);
    Route::get('get/personal/query/{userId}' , ['as' => 'get.personal.query' , 'uses' => 'ApiController@getPersonalQuery', 'middleware' => 'app.api.token']);
    Route::post('get/trades/filter/{userId}' , ['as' => 'get.trade.filter' , 'uses' => 'ApiController@filterTrade', 'middleware' => 'app.api.token']);
    // trade_type 1–4 = single type; omit/0/"all" = All (buy → sell → 15 sold). See trade_list_meta in response.
    Route::post('web/get/trades/filter/{userId}' , ['as' => 'web.get.trade.filter' , 'uses' => 'ApiController@webFilterTrade', 'middleware' => 'portal.api.token']);

    Route::match(['get', 'post'], 'get/guest/rice-sourcing/trades', [
        'as' => 'get.guest.rice.sourcing.trades',
        'uses' => 'ApiController@getGuestRiceSourcingTrades',
    ]);

    Route::get('get/posted-jobs', [
        'as' => 'get.posted.jobs',
        'uses' => 'ApiController@getPublicPostedJobs',
    ]);

    Route::post('save/job-application', [
        'as' => 'save.job.application',
        'uses' => 'ApiController@saveJobApplication',
    ]);


    Route::get('get/personal/query/count/{userId}' , ['as' => 'get.personal.query' , 'uses' => 'ApiController@getPersonalQueryCount', 'middleware' => 'app.api.token']);


    //get trade details
    Route::get('get/trade/details/{tradeId}' , ['as' => 'get.trade.details' , 'uses' => 'ApiController@getTradeDetail']);


    Route::PATCH('submit/sell/query' , ['as' => 'submit.sell.query' , 'uses' => 'ApiController@SubmitSellQuery', 'middleware' => 'app.api.token']);
    Route::post('submit/sell/query/web' , ['as' => 'submit.sell.query' , 'uses' => 'ApiController@SubmitSellQueryWeb', 'middleware' => 'portal.api.token']);
    Route::PATCH('submit/buy/query' , ['as' => 'submit.buy.query' , 'uses' => 'ApiController@SubmitBuyQuery', 'middleware' => 'app.api.token']);
    Route::POST('submit/buy/query/web' , ['as' => 'submit.buy.query.web' , 'uses' => 'ApiController@SubmitBuyQuery', 'middleware' => 'portal.api.token']);

    Route::post('future/submit/sell/query' , ['as' => 'future.submit.sell.query' , 'uses' => 'ApiController@FutureSubmitSellQuery', 'middleware' => 'app.api.token']);
    Route::post('future/submit/buy/query' , ['as' => 'future.submit.buy.query' , 'uses' => 'ApiController@FutureSubmitBuyQuery', 'middleware' => 'app.api.token']);





    // buy query INR
    Route::get('get/buyer/inr/packing' , ['as' => 'get.buyer.inr.packing' , 'uses' => 'ApiController@getBuyerPackingINR']);
    Route::POST('like/trade' , ['as' => 'post.like.trade' , 'uses' => 'ApiController@likeTrade', 'middleware' => 'app.api.token']);
    Route::POST('intrested/trade' , ['as' => 'post.intrested.trade' , 'uses' => 'ApiController@tradeintrested', 'middleware' => 'app.api.token']);
    Route::POST('web/intrested/trade' , ['as' => 'post.intrested.trade' , 'uses' => 'ApiController@webTradeintrested', 'middleware' => 'portal.api.token']);
    Route::POST('get/my/trades' , ['as' => 'get.personal.trades' , 'uses' => 'ApiController@getMyTrades', 'middleware' => 'app.api.token']);



    Route::get('get/news/runner' , ['as' => 'get.news.runner' , 'uses' => 'ApiController@NewsRunner']);
    Route::get('get/web/news/runner' , ['as' => 'get.web.news.runner' , 'uses' => 'ApiController@getWebNewsRunner']);
    Route::get('get/testimonial' , ['as' => 'get.testimonial' , 'uses' => 'ApiController@getTestimonial']);
    Route::get('get/testimonial/videos' , ['as' => 'get.testimonial' , 'uses' => 'ApiController@getTestimonialVideos']);
    Route::get('get/grades' , ['as' => 'list.grade' , 'uses' => 'ApiController@listGrade']);
    Route::POST('contact/us' , ['as' => 'contact.us' , 'uses' => 'ApiController@contactUs']);


    Route::get('list/web/paddy/state',      ['as' => 'list.web.paddy.state',    'uses' => 'PaddyApiController@listPaddy', 'middleware' => 'portal.api.token']);
    Route::get('list/web/paddy/mandi/{stateId}',      ['as' => 'list.web.paddy.state.mandi',    'uses' => 'PaddyApiController@listPaddyMandi', 'middleware' => 'portal.api.token']);
    Route::get('list/web/paddy/quality',   ['as' => 'list.web.paddy.quality', 'uses' => 'PaddyApiController@listPaddyQualities', 'middleware' => 'portal.api.token']);
    Route::post('submit/paddy/sell/query', ['as' => 'submit.paddy.sell.query', 'uses' => 'PaddyApiController@submitPaddySellQuery', 'middleware' => 'portal.api.token']);

    // Paddy trades — app
    Route::get('list/paddy/trades', ['as' => 'list.paddy.trades.api', 'uses' => 'PaddyApiController@listPaddyTrades', 'middleware' => 'app.api.token']);
    Route::get('get/paddy/trade/{id}', ['as' => 'get.paddy.trade.api', 'uses' => 'PaddyApiController@getPaddyTradeDetail', 'middleware' => 'app.api.token']);
    Route::post('interested/paddy/trade', ['as' => 'interested.paddy.trade.api', 'uses' => 'PaddyApiController@showPaddyTradeInterest', 'middleware' => 'app.api.token']);
    // Paddy trades — web portal
    Route::get('list/web/paddy/trades', ['as' => 'list.web.paddy.trades.api', 'uses' => 'PaddyApiController@listPaddyTrades', 'middleware' => 'portal.api.token']);
    Route::get('web/get/paddy/trade/{id}', ['as' => 'web.get.paddy.trade.api', 'uses' => 'PaddyApiController@getPaddyTradeDetail', 'middleware' => 'portal.api.token']);
    Route::post('web/interested/paddy/trade', ['as' => 'web.interested.paddy.trade.api', 'uses' => 'PaddyApiController@showPaddyTradeInterest', 'middleware' => 'portal.api.token']);

    Route::get('get/paddy/prices/{mandi_id}/{state_id}',      ['as' => 'get.paddy.prices',    'uses' => 'PaddyApiController@getPaddyPrices']);
    Route::get('get/paddy/prices/by/paddy/{stateId}/{paddyId}',      ['as' => 'get.paddy.prices.by.paddy',    'uses' => 'PaddyApiController@getPaddyPricesByPaddy']);
    Route::get('get/states/by/paddy/{stateId}',      ['as' => 'get.paddy.states',    'uses' => 'PaddyApiController@getPaddyQualities']);
    Route::get('get/paddy/map/data/{mandi_id}/{state_id}/{quality_id}',      ['as' => 'get.paddy.pricess',    'uses' => 'PaddyApiController@GetPaddyMapData']);

    Route::get('get/category/role/{roleId}',      ['as' => 'get.category.role',    'uses' => 'ApiController@getCategoryByRole']);

    Route::get('web/quality/list' , ['as' => 'web.quality.list' , 'uses' => 'WebBrandController@getQualities', 'middleware' => 'portal.api.token' ]); 
    Route::get('web/brand/index/{userId}' , ['as' => 'web.brand.index' , 'uses' => 'WebBrandController@index', 'middleware' => 'portal.api.token' ]); 
    Route::get('web/brand/availability/{userId}' , ['as' => 'web.brand.index' , 'uses' => 'WebBrandController@brandsForDistributers', 'middleware' => 'portal.api.token' ]); 
    Route::post('web/brand/create' , ['as' => 'web.brand.create' , 'uses' => 'WebBrandController@create', 'middleware' => 'portal.api.token' ]); 
    Route::post('web/brand/edit' , ['as' => 'web.brand.edit' , 'uses' => 'WebBrandController@edit', 'middleware' => 'portal.api.token' ]); 


    Route::get('web/vendor/type' , ['as' => 'web.vendor.type' , 'uses' => 'WebBrandController@vendorType', 'middleware' => 'portal.api.token' ]); 
    Route::get('web/vendor/list/{vendorType}' , ['as' => 'web.vendor.list' , 'uses' => 'WebBrandController@vendorList', 'middleware' => 'portal.api.token' ]); 
    Route::get('web/vendor/products/{id}' , ['as' => 'web.vendor.products' , 'uses' => 'WebVendorProductController@listByVendorId', 'middleware' => 'portal.api.token' ]);

    



    Route::get('web/brand/variant/{brandId}' , ['as' => 'web.variant.index' , 'uses' => 'WebBrandController@indexVariant', 'middleware' => 'portal.api.token' ]); 
    Route::get('web/brand/variant/delete/{variantId}' , ['as' => 'web.variant.delete' , 'uses' => 'WebBrandController@deleteVariant', 'middleware' => 'portal.api.token' ]); 
    Route::POST('web/brand/variant/edit' , ['as' => 'web.variant.edit' , 'uses' => 'WebBrandController@editVariant', 'middleware' => 'portal.api.token' ]); 
    Route::post('web/variant/create' , ['as' => 'web.variant.create' , 'uses' => 'WebBrandController@createVariant', 'middleware' => 'portal.api.token' ]); 


    Route::get('get/web/states' , ['as' => 'web.get.web.states' , 'uses' => 'WebStatesController@getStatesList']);
    Route::get('get/web/cities/{stateId}' , ['as' => 'web.get.web.cities.stateId' , 'uses' => 'WebStatesController@getCityFromStateId']);
    

    Route::get('get/web/brand/form' , ['as' => 'web.get.brand.form' , 'uses' => 'WebStatesController@getWebBrandForm', 'middleware' => 'portal.api.token']);




    Route::POST('save/brand/availability' , ['as' => 'save.brand.availability' , 'uses' => 'ApiController@saveBrandAvailability']);
    Route::GET('get/brand/availability/{brandId}' , ['as' => 'get.brand.availability' , 'uses' => 'ApiController@getBrandAvailability']);



    Route::GET('admin/is/viewed/by/admin' , ['as' => 'admin.is.viewed.by.admin' , 'uses' => 'ApiController@adminIsViewedByAdmin']);
    // Public broadcasting auth for dev (signs without requiring a logged-in user)
    Route::post('/broadcasting/auth', function (Request $request) {
        $channel = $request->input('channel_name');
        $socketId = $request->input('socket_id');

        if (! $channel || ! $socketId) {
            return response()->json(['message' => 'Invalid request'], 422);
        }

        $driver = config('broadcasting.default', 'pusher');
        if ($driver === 'reverb') {
            $cfg = [
                'key' => config('broadcasting.connections.reverb.key'),
                'secret' => config('broadcasting.connections.reverb.secret'),
                'app_id' => config('broadcasting.connections.reverb.app_id'),
                'options' => config('broadcasting.connections.reverb.options') ?? [],
            ];
        } else {
            $cfg = config('broadcasting.connections.pusher');
        }
        if (! $cfg) {
            return response()->json(['message' => 'Pusher not configured'], 500);
        }
        $options = $cfg['options'] ?? [];
        $pusher = new Pusher(
            $cfg['key'] ?? '',
            $cfg['secret'] ?? '',
            $cfg['app_id'] ?? '',
            $options
        );

        try {
            if (str_starts_with($channel, 'private')) {
                $resp = method_exists($pusher, 'authorizeChannel')
                    ? $pusher->authorizeChannel($channel, $socketId)
                    : $pusher->socket_auth($channel, $socketId);
                return response()->json(json_decode($resp, true));
            }
            if (str_starts_with($channel, 'presence')) {
                $userId = 'guest-'.bin2hex(random_bytes(4));
                $userInfo = ['name' => 'Guest'];
                $resp = method_exists($pusher, 'authorizePresenceChannel')
                    ? $pusher->authorizePresenceChannel($channel, $socketId, $userId, $userInfo)
                    : $pusher->presence_auth($channel, $socketId, $userId, $userInfo);
                return response()->json(json_decode($resp, true));
            }
            // Public channels do not need auth
            return response()->json(['status' => 'ok']);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Broadcast auth error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Forbidden'], 403);
        }
    })->middleware(['api'])->name('broadcasting.auth');

    // Quick test endpoint to emit a broadcast for debugging
    Route::get('/broadcast/test', function () {
        try {
            event(new \App\Events\AdminEvent('debug', ['message' => 'hello from api /broadcast/test']));
            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Broadcast test failed', ['error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    });




    // Web SPA logout (session cookie) — same handler as portal/logout
    Route::group([
        'middleware' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
        ],
    ], function () {
        Route::post('web/logout', [PortalApiController::class, 'logout'])->name('api.web.logout');
    });

    require __DIR__ . '/portal.php';
// });
