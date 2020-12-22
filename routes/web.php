<?php

use Illuminate\Support\Facades\Route;

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

Route::group(['prefix'=>'administrator'], function(){
    Route::group(['middleware'=>'auth'], function(){
        Route::get('/',['as'=>'home','uses'=>'HomeController@index']);

        Route::group(['middleware'=>'admin'], function(){
        
            Route::get('call/is/active' , ['as' => 'is.active.call' , 'uses' => 'PlanController@isActiveCall']);
    
            //plans
            Route::get('list/plans' ,       ['as' => 'list.plan',           'uses' => 'PlanController@index']);
            Route::get('plan/create' ,['as' => 'list.plan.create',    'uses' => 'PlanController@create']);
            Route::post('create/plans' ,    ['as' => 'create.plan',         'uses' => 'PlanController@createPlan']);
            Route::get('edit/plan/{id}' ,   ['as' => 'edit.plan',           'uses' => 'PlanController@editPlan']);
            Route::post('update/plan' ,     ['as' => 'update.plan',         'uses' => 'PlanController@updatePlan']);
            Route::get('delete/plan' ,      ['as' => 'delete.plan',         'uses' => 'PlanController@deletePlan']);
            
            
            
            
            //gallery
            Route::get('gallery', ['as' => 'gallery', 'uses' => 'GalleryController@index']);
            Route::get('gallery/create',['as'=>'gallery.create','uses'=>'GalleryController@Create']);
            Route::delete('gallery/delete/{id}',['as'=>'gallery.delete','uses'=>'GalleryController@deleteGallery']);
            Route::post('gallery/save', ['as' => 'save.gallery', 'uses' => 'GalleryController@save']);
            Route::get('gallery/edit/{id}' , ['as' => 'gallery.edit' , 'uses' => 'GalleryController@editGallery']);
            Route::POST('gallery/update' , ['as' => 'gallery.update' , 'uses' => 'GalleryController@galleryUpdate']);
            

            //Users
            //Route::group(['module'=>'users','icon'=>'fa-users'], function(){
                Route::get('users/{role}',['as'=>'users','uses'=>'UsersController@index','action'=>'view']);
                Route::get('users/{role}/create',['as'=>'create.user','uses'=>'UsersController@create','action'=>'create']);
                Route::post('users/save/{role}',['as'=>'save.user','uses'=>'UsersController@save','action'=>'create']);
                Route::get('users/edit/{id}/{role}',['as'=>'edit.user','uses'=>'UsersController@edit','action'=>'edit']);
                Route::put('users/update/{id}/{role}',['as'=>'update.user','uses'=>'UsersController@update','action'=>'edit']);
                Route::delete('users/delete/{id}/{role}',['as'=>'delete.user','uses'=>'UsersController@delete','action'=>'delete']);
            //});

            //Roles
            //Route::group(['module'=>'roles','icon'=>'fa-id-badge'], function() {
                Route::get('roles', ['as' => 'roles', 'uses' => 'RolesController@index','action'=>'view']);
                Route::get('roles/create', ['as' => 'create.role', 'uses' => 'RolesController@create','action'=>'create']);
                Route::post('roles/save', ['as' => 'save.role', 'uses' => 'RolesController@save','action'=>'create']);
                Route::get('roles/edit/{id}', ['as' => 'edit.role', 'uses' => 'RolesController@edit','action'=>'edit']);
                Route::put('roles/update/{id}', ['as' => 'update.role', 'uses' => 'RolesController@update','action'=>'edit']);
                Route::delete('roles/delete/{id}', ['as' => 'delete.role', 'uses' => 'RolesController@delete','action'=>'delete']);
            //});

            //Designations
            //Route::group(['module'=>'designation','icon'=>'fa-users'], function() {
                Route::get('designations', ['as' => 'designations', 'uses' => 'DesignationsController@index','action'=>'view']);
                Route::get('designations/create', ['as' => 'create.designation', 'uses' => 'DesignationsController@create','action'=>'create']);
                Route::post('designations/save', ['as' => 'save.designation', 'uses' => 'DesignationsController@save','action'=>'create']);
                Route::get('designations/edit/{id}', ['as' => 'edit.designation', 'uses' => 'DesignationsController@edit','action'=>'edit']);
                Route::put('designations/update/{id}', ['as' => 'update.designation', 'uses' => 'DesignationsController@update','action'=>'edit']);
                Route::delete('designations/delete/{id}', ['as' => 'delete.designation', 'uses' => 'DesignationsController@delete','action'=>'delete']);
            //});

            //Modules
            Route::get('modules/{role_id?}',['as'=>'modules','uses'=>'ModulesController@index']);
            Route::post('modules/save',['as'=>'modules.save','uses'=>'ModulesController@saveModule']);
            Route::get('permissions/{module_id?}/{role_id?}',['as'=>'permissions','uses'=>'ModulesController@permissions']);
            Route::post('permissions/save',['as'=>'permissions.save','uses'=>'ModulesController@savePermissions']);

            /*----------------------###########################---------------------------*/

            //Zones
            Route::group(['module'=>'zones','icon'=>'fa-map-o'], function() {
                Route::get('zones', ['as' => 'zones', 'uses' => 'CityZonesController@index','action'=>'view']);
                Route::get('zones/create', ['as' => 'create.zone', 'uses' => 'CityZonesController@create','action'=>'create']);
                Route::post('zones/save', ['as' => 'save.zone', 'uses' => 'CityZonesController@save','action'=>'create']);
                Route::get('zones/edit/{id}', ['as' => 'edit.zone', 'uses' => 'CityZonesController@edit','action'=>'edit']);
                Route::put('zones/update/{id}', ['as' => 'update.zone', 'uses' => 'CityZonesController@update','action'=>'edit']);
                Route::delete('zones/delete/{id}', ['as' => 'delete.zone', 'uses' => 'CityZonesController@delete','action'=>'delete']);
            });

            //Qualities
            Route::group(['module'=>'qualities','icon'=>'fa-tags'], function() {
                Route::get('qualities', ['as' => 'qualities', 'uses' => 'QualitiesController@index','action'=>'view']);
                Route::get('qualities/create', ['as' => 'create.quality', 'uses' => 'QualitiesController@create','action'=>'create']);
                Route::post('qualities/save', ['as' => 'save.quality', 'uses' => 'QualitiesController@save','action'=>'create']);
                Route::get('qualities/edit/{id}', ['as' => 'edit.quality', 'uses' => 'QualitiesController@edit','action'=>'edit']);
                Route::put('qualities/update/{id}', ['as' => 'update.quality', 'uses' => 'QualitiesController@update','action'=>'edit']);
                Route::delete('qualities/delete/{id}', ['as' => 'delete.quality', 'uses' => 'QualitiesController@delete','action'=>'delete']);
                Route::get('qualities/import', ['as' => 'import.quality', 'uses' => 'QualitiesController@importQuality','action'=>'import']);
                Route::post('qualities/import/save', ['as' => 'import.quality.save', 'uses' => 'QualitiesController@saveImportQuality','action'=>'import']);
            });

            //Packings
            Route::group(['module'=>'packing','icon'=>'fa-shopping-bag'], function() {
                Route::get('packings', ['as' => 'packings', 'uses' => 'PackingsController@index','action'=>'view']);
                Route::get('packings/create', ['as' => 'create.packing', 'uses' => 'PackingsController@create','action'=>'create']);
                Route::post('packings/save', ['as' => 'save.packing', 'uses' => 'PackingsController@save','action'=>'create']);
                Route::get('packings/edit/{id}', ['as' => 'edit.packing', 'uses' => 'PackingsController@edit','action'=>'edit']);
                Route::put('packings/update/{id}', ['as' => 'update.packing', 'uses' => 'PackingsController@update','action'=>'edit']);
                Route::delete('packings/delete/{id}', ['as' => 'delete.packing', 'uses' => 'PackingsController@delete','action'=>'delete']);
            });

            //Packing Types
            Route::group(['module'=>'packing_type','icon'=>''], function() {
                Route::get('packing-types', ['as' => 'packing-types', 'uses' => 'PackingTypesController@index','action'=>'view']);
                Route::get('packing-types/create', ['as' => 'create.packing-type', 'uses' => 'PackingTypesController@create','action'=>'create']);
                Route::post('packing-types/save', ['as' => 'save.packing-type', 'uses' => 'PackingTypesController@save','action'=>'create']);
                Route::get('packing-types/edit/{id}', ['as' => 'edit.packing-type', 'uses' => 'PackingTypesController@edit','action'=>'edit']);
                Route::put('packing-types/update/{id}', ['as' => 'update.packing-type', 'uses' => 'PackingTypesController@update','action'=>'edit']);
                Route::delete('packing-types/delete/{id}', ['as' => 'delete.packing-type', 'uses' => 'PackingTypesController@delete','action'=>'delete']);
            });

            //Sample Registers
            Route::group(['module'=>'sample_register','icon'=>'fa-database'], function() {
                Route::get('sample-registers', ['as' => 'sample-registers', 'uses' => 'SampleRegistersController@index','action'=>'view']);
                Route::get('sample-registers/create/{sample?}', ['as' => 'create.sample-register', 'uses' => 'SampleRegistersController@create','action'=>'create']);
                Route::post('sample-registers/save', ['as' => 'save.sample-register', 'uses' => 'SampleRegistersController@save','action'=>'create']);
                Route::get('sample-registers/edit/{id}', ['as' => 'edit.sample-register', 'uses' => 'SampleRegistersController@edit','action'=>'edit']);
                Route::put('sample-registers/update/{id}', ['as' => 'update.sample-register', 'uses' => 'SampleRegistersController@update','action'=>'edit']);
                Route::delete('sample-registers/delete/{id}', ['as' => 'delete.sample-register', 'uses' => 'SampleRegistersController@delete','action'=>'delete']);
            });

            //Sample Outwards
            Route::group(['module'=>'sample_outward','icon'=>'fa-shopping-basket'], function() {
                Route::get('sample-outwards', ['as' => 'sample-outwards', 'uses' => 'SampleOutwardsController@index','action'=>'view']);
                Route::get('sample-outwards/create', ['as' => 'create.sample-outward', 'uses' => 'SampleOutwardsController@create','action'=>'create']);
                Route::post('sample-outwards/save', ['as' => 'save.sample-outward', 'uses' => 'SampleOutwardsController@save','action'=>'create']);
                Route::get('sample-outwards/edit/{id}', ['as' => 'edit.sample-outward', 'uses' => 'SampleOutwardsController@edit','action'=>'edit']);
                Route::put('sample-outwards/update/{id}', ['as' => 'update.sample-outward', 'uses' => 'SampleOutwardsController@update','action'=>'edit']);
                Route::delete('sample-outwards/delete/{id}', ['as' => 'delete.sample-outward', 'uses' => 'SampleOutwardsController@delete','action'=>'delete']);
            });

            //Cooking Report
            Route::group(['module'=>'cooking_report','icon'=>'fa-address-book-o'], function() {
                Route::get('cooking/reports', ['as' => 'cooking-reports', 'uses' => 'CookingController@index','action'=>'view']);
                Route::get('cooking/reports/create', ['as' => 'create.cooking-report', 'uses' => 'CookingController@create','action'=>'create']);
                Route::post('cooking/reports/save', ['as' => 'save.cooking-report', 'uses' => 'CookingController@save','action'=>'create']);
                Route::get('cooking/reports/edit/{id}', ['as' => 'edit.cooking-report', 'uses' => 'CookingController@edit','action'=>'edit']);
                Route::put('cooking/reports/update/{id}', ['as' => 'update.cooking-report', 'uses' => 'CookingController@update','action'=>'edit']);
                Route::delete('cooking/reports/delete/{id}', ['as' => 'delete.cooking-report', 'uses' => 'CookingController@delete','action'=>'delete']);
            });

            //Loading Report
            Route::get('loading/reports', ['as'=>'loading-reports','uses'=>'LoadingReportController@index']);
            Route::get('loading/reports/create',['as'=>'create.loading-report','uses'=>'LoadingReportController@create']);
            Route::post('loading/reports/save',['as'=>'save.loading-report','uses'=>'LoadingReportController@save']);
            Route::get('loading/reports/edit/{id}',['as'=>'edit.loading-report','uses'=>'LoadingReportController@edit']);
            Route::put('loading/reports/update/{id}',['as'=>'update.loading-report','uses'=>'LoadingReportController@update']);
            Route::delete('loading/reports/delete/{id}',['as'=>'delete.loading-report','uses'=>'LoadingReportController@delete']);

            //Sample Lab Report
            Route::group(['module'=>'sample_analysis_report','icon'=>'fa-flask'], function() {
                Route::get('sample-lab/reports', ['as' => 'sample-lab-reports', 'uses' => 'SampleLabReportController@index','action'=>'view']);
                Route::get('sample-lab/reports/create', ['as' => 'create.sample-lab-report', 'uses' => 'SampleLabReportController@create','action'=>'create']);
                Route::post('sample-lab/reports/save', ['as' => 'save.sample-lab-report', 'uses' => 'SampleLabReportController@save','action'=>'create']);
                Route::get('sample-lab/reports/edit/{id}', ['as' => 'edit.sample-lab-report', 'uses' => 'SampleLabReportController@edit','action'=>'edit']);
                Route::put('sample-lab/reports/update/{id}', ['as' => 'update.sample-lab-report', 'uses' => 'SampleLabReportController@update','action'=>'edit']);
                Route::delete('sample-lab/reports/delete/{id}', ['as' => 'delete.sample-lab-report', 'uses' => 'SampleLabReportController@delete','action'=>'delete']);
            });

            //Deals
            Route::group(['module'=>'deals','icon'=>'fa-thumbs-up'], function() {
                Route::get('deals', ['as' => 'deals', 'uses' => 'DealsController@index','action'=>'view']);
                Route::get('deals/create', ['as' => 'deals.create', 'uses' => 'DealsController@create','action'=>'create']);
                Route::post('deals/save', ['as' => 'deals.save', 'uses' => 'DealsController@save','action'=>'create']);
                Route::get('deals/edit/{id}', ['as' => 'deals.edit', 'uses' => 'DealsController@edit','action'=>'edit']);
                Route::put('deals/update/{id}', ['as' => 'deals.update', 'uses' => 'DealsController@update','action'=>'edit']);
                Route::delete('deals/delete/{id}', ['as' => 'deals.delete', 'uses' => 'DealsController@delete','action'=>'delete']);
            });

            //Deal Lab Report
            Route::group(['module'=>'deal_lab_report','icon'=>'fa-handshake-o'], function() {
                Route::get('deal-lab/reports', ['as' => 'deal-lab-reports', 'uses' => 'DealLabReportController@index','action'=>'view']);
                Route::get('deal-lab/reports/create', ['as' => 'create.deal-lab-report', 'uses' => 'DealLabReportController@create','action'=>'create']);
                Route::post('deal-lab/reports/save', ['as' => 'save.deal-lab-report', 'uses' => 'DealLabReportController@save','action'=>'create']);
                Route::get('deal-lab/reports/edit/{id}', ['as' => 'edit.deal-lab-report', 'uses' => 'DealLabReportController@edit','action'=>'edit']);
                Route::put('deal-lab/reports/update/{id}', ['as' => 'update.deal-lab-report', 'uses' => 'DealLabReportController@update','action'=>'edit']);
                Route::delete('deal-lab/reports/delete/{id}', ['as' => 'delete.deal-lab-report', 'uses' => 'DealLabReportController@delete','action'=>'delete']);
            });

            //Offers
            Route::group(['module'=>'offers','icon'=>'fa-thumbs-o-up'], function() {
                Route::get('offers', ['as' => 'offers', 'uses' => 'OffersController@index','action'=>'view']);
                Route::get('offers/create', ['as' => 'create.offer', 'uses' => 'OffersController@create','action'=>'create']);
                Route::post('offers/save', ['as' => 'save.offer', 'uses' => 'OffersController@save','action'=>'create']);
                Route::get('offers/edit/{id}', ['as' => 'edit.offer', 'uses' => 'OffersController@edit','action'=>'edit']);
                Route::put('offers/update/{id}', ['as' => 'update.offer', 'uses' => 'OffersController@update','action'=>'edit']);
                Route::delete('offers/delete/{id}', ['as' => 'delete.offer', 'uses' => 'OffersController@delete','action'=>'delete']);
            });

            //Documents
            Route::group(['module'=>'documents','icon'=>'fa-file-text'], function() {
                Route::get('documents', ['as' => 'documents', 'uses' => 'DocumentsController@index','action'=>'view']);
                Route::get('documents/create', ['as' => 'create.document', 'uses' => 'DocumentsController@create','action'=>'create']);
                Route::post('documents/save', ['as' => 'save.document', 'uses' => 'DocumentsController@save','action'=>'create']);
                Route::get('documents/edit/{id}', ['as' => 'edit.document', 'uses' => 'DocumentsController@edit','action'=>'edit']);
                Route::put('documents/update/{id}', ['as' => 'update.document', 'uses' => 'DocumentsController@update','action'=>'edit']);
                Route::delete('documents/delete/{id}', ['as' => 'delete.document', 'uses' => 'DocumentsController@delete','action'=>'delete']);
            });

            //Payment Reminders
            Route::group(['module'=>'payment_reminders','icon'=>'fa-bell-o'], function() {
                Route::get('reminders', ['as' => 'payment_reminders', 'uses' => 'PaymentReminderController@index','action'=>'view']);
                Route::get('reminders/create', ['as' => 'create.reminder', 'uses' => 'PaymentReminderController@create','action'=>'create']);
                Route::post('reminders/save', ['as' => 'save.reminder', 'uses' => 'PaymentReminderController@save','action'=>'create']);
                Route::get('reminders/edit/{id}', ['as' => 'edit.reminder', 'uses' => 'PaymentReminderController@edit','action'=>'edit']);
                Route::put('reminders/update/{id}', ['as' => 'update.reminder', 'uses' => 'PaymentReminderController@update','action'=>'edit']);
                Route::delete('reminders/delete/{id}', ['as' => 'delete.reminder', 'uses' => 'PaymentReminderController@delete','action'=>'delete']);
            });

            //Live Prices
            Route::group(['module'=>'live_prices','icon'=>'fa-inr'], function(){
                Route::match(['get','post'],'live/price/{rice_name?}',['as'=>'live_prices','uses'=>'LivePricesController@index','action'=>'create']);
                Route::post('live/prices/save/',['as'=>'save.price','uses'=>'LivePricesController@savePrice','action'=>'create']);
                Route::get('price/delete/{id}',['as'=>'delete.price','uses'=>'LivePricesController@delete','action'=>'delete']);
            });

            //Ports
            Route::group(['module'=>'ports','icon'=>'fa-bus'], function(){
                Route::get('ports',['as'=>'ports','uses'=>'PortsController@index','action'=>'create']);
                Route::post('ports/save',['as'=>'ports.save','uses'=>'PortsController@save','action'=>'create']);
            });
            
            //Plan
            Route::get('plan',['as'=>'plan.create','uses'=>'PlanController@index']);
            Route::post('plan/save',['as'=>'plan.save','uses'=>'PlanController@save']);
            Route::post('plan/update',['as'=>'plan.update','uses'=>'PlanController@updatePlan']);
            
            //Ajax Routes
            Route::post('city/save',['as'=>'save.city.modal','uses'=>'AjaxController@saveCity']);
            Route::get('sample/register/{sntc_no}',['as'=>'sample.register.details','uses'=>'AjaxController@getSampleRegisterFromSntc']);
            Route::get('deal/lab/{sntc_no}',['as'=>'deal.lab.report.details','uses'=>'AjaxController@getDataForDealLabReport']);
            Route::get('push/notification' , ['as' => 'send.push.notification' , 'uses' => 'NotificationController@index']);
            Route::POST('send/push/notification' , ['as' => 'post.push.notification' , 'uses' => 'NotificationController@sendNotification']);
            Route::get('trial/period' , ['as' => 'trial.period' , 'uses' => 'TrialPeriodController@index']);
            Route::post('trial/period/save' , ['as' => 'trialPeriod.save' , 'uses' => 'TrialPeriodController@save']);
        });

        Route::group(['middleware'=>'field_runner'], function(){

            //Samples
            Route::group(['module'=>'sample_collection_report','icon'=>'fa-file-text-o'], function() {
                Route::get('samples', ['as' => 'samples', 'uses' => 'SamplesController@index','action'=>'view']);
                Route::get('samples/create', ['as' => 'create.sample', 'uses' => 'SamplesController@create','action'=>'create']);
                Route::post('samples/save', ['as' => 'save.sample', 'uses' => 'SamplesController@save','action'=>'create']);
                Route::get('samples/edit/{id}', ['as' => 'edit.sample', 'uses' => 'SamplesController@edit','action'=>'edit']);
                Route::put('samples/update/{id}', ['as' => 'update.sample', 'uses' => 'SamplesController@update','action'=>'edit']);
                Route::delete('samples/delete/{id}', ['as' => 'delete.sample', 'uses' => 'SamplesController@delete','action'=>'delete']);
            });

            //Couriers
            Route::group(['module'=>'courier','icon'=>'fa-envelope-o'], function() {
                Route::get('couriers', ['as' => 'couriers', 'uses' => 'CouriersController@index','action'=>'view']);
                Route::get('couriers/create', ['as' => 'create.courier', 'uses' => 'CouriersController@create','action'=>'create']);
                Route::post('couriers/save', ['as' => 'save.courier', 'uses' => 'CouriersController@save','action'=>'create']);
                Route::get('couriers/edit/{id}', ['as' => 'edit.courier', 'uses' => 'CouriersController@edit','action'=>'edit']);
                Route::put('couriers/update/{id}', ['as' => 'update.courier', 'uses' => 'CouriersController@update','action'=>'edit']);
                Route::delete('couriers/delete/{id}', ['as' => 'delete.courier', 'uses' => 'CouriersController@delete','action'=>'delete']);
            });

        });

    });

    Auth::routes();

    Route::get('logout','Auth\LoginController@logout');
});
