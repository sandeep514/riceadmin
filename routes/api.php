<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::get('get/plans' ,                ['as' => 'get.plans' ,'uses' => 'ApiController@getPlans']);


Route::get('get/gallery/list' ,         ['as' => 'get.gallery.details'      , 'uses' => 'ApiController@getGalleryData']);
Route::get('get/gallery/details/{id}' , ['as' => 'get.gallery.details.id'   , 'uses' => 'ApiController@getGalleryDetails']);
Route::POST('save/user' ,               ['as' => 'save.user' 			    , 'uses' => 'ApiController@saveUser']);
Route::POST('verify/user' ,             ['as' => 'verify.user'              , 'uses' => 'ApiController@verifyUser']);

Route::POST('change/password' ,         ['as' => 'change.password'          , 'uses' => 'ApiController@changePassword']);

Route::GET('send/otp/{id}' , ['as' => 'send.otp'                , 'uses' => 'ApiController@sendOTP']);
Route::GET('verify/otp/{number}/{id}' , ['as' => 'verify.otp'   , 'uses' => 'ApiController@verifyOTP']);

Route::get('get/basmati/state' , ['as' => 'get.basmati.state' , 'uses' => 'ApiController@getBasmatiState']);
Route::get('get/nonbasmati/state' , ['as' => 'get.nonbasmati.state' , 'uses' => 'ApiController@getNONBasmatiState']);
Route::get('get/images/for/dashboard' , ['as' => 'get.images.for.dashboard' , 'uses' => 'ApiController@getImagesForDashboard']);


Route::post('send/message' , ['as' => 'send.message' , 'uses' => 'MessageController@sendMessage']);
Route::post('update/user/token' , ['as' => 'update.user.token' , 'uses' => 'ApiController@updateUserToken']);

//ChartIntervals
Route::get('get/chartinterval' , ['as' => 'get.chartinterval' , 'uses' => 'ApiController@getChartinterval']);

//Orders
Route::get('check/user/plan/{userId}',['as'=>'get.order','uses' => 'ApiController@isUserOrderExistAndActive']);

//Users
Route::post('update/user/token',['as'=>'update.user.token','uses' => 'ApiController@updateUserTokenById']);

//Message
Route::get('get/user/messages/count/{userId}' , ['as' => 'get.user.messages.count' , 'uses' => 'ApiController@getUserMessageCount']);
Route::get('get/message/contacts/list',['as'=>'get.message.contact','uses'=>'ApiController@getMessageContacts']);
Route::get('get/message/{from}/{to}',['as'=>'get.message','uses'=>'ApiController@getMessagesByIds']);
Route::post('save/message',['as'=>'save.message','uses'=>'ApiController@saveMessage']);

Route::get('get/message/contacts/list/RefactorCode',['as'=>'get.message.contact.refactor','uses'=>'ApiController@getMessageContactsRefator']);
Route::get('check/user/expired/{id}',['as'=>'check.user.expired','uses'=>'ApiController@checkUserExpired']);

