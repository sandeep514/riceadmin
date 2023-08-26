<?php

use Illuminate\Support\Facades\Route;

Route::get('/',function(){
    return redirect()->route('home');
});

Route::get('sendhtmlemail', 'MailController@html_email');

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
                Route::get('get/new/vendor',['as'=>'get.new.vendors','uses'=>'UsersController@getVendors','action'=>'getVendors']);
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


            //dollarExcel
            Route::get('excel',['as'=>'dollarExcel.create','uses'=>'DollarController@index']);
            Route::post('excel/save',['as'=>'dollarExcel.save','uses'=>'DollarController@save']);

            Route::get('excel/ocean/freight',['as'=>'dollarExcel.create.ocean.freight','uses'=>'DollarController@indexFreight']);
            Route::post('dollar/save/ocean/freight',['as'=>'dollarExcel.save.ocean.freight','uses'=>'DollarController@saveOceanFreight']);
            
            Route::get('excel/quality/master',['as'=>'dollarExcel.create.quality.master','uses'=>'DollarController@qualityMaster']);
            Route::post('dollar/save/quality/master',['as'=>'dollarExcel.save.quality.master','uses'=>'DollarController@saveQualityMaster']);
            
            Route::get('dollarExcel/default/value/master',['as'=>'dollarExcel.default.value.master','uses'=>'DollarController@defaultValueMaster']);
            Route::post('save/dollarExcel/default/value/master',['as'=>'dollarExcel.default.value.master.save','uses'=>'DollarController@saveDefaultValueMaster']);
            
            Route::get('domestic/transport/master',['as'=>'domestic.transport.master','uses'=>'DollarController@domesticTransportMaster']);
            Route::post('save/domestic/transport/master',['as'=>'domestic.transport.master.save','uses'=>'DollarController@saveDomestictransportMaster']);
            
            Route::get('vendor/category/master',['as'=>'vendor.category.master','uses'=>'DollarController@vendorCategoryMaster']);
            Route::post('save/vendor/category/master',['as'=>'vendor.category.master.save','uses'=>'DollarController@saveVendorCategoryVendor']);
            
            Route::get('bag/vendor/master',['as'=>'bag.vendor.master','uses'=>'DollarController@bagVendorMaster']);
            Route::post('save/bag/vendor/master',['as'=>'bag.vendor.master.save','uses'=>'DollarController@saveBagVendor']);
            


            //Ajax Routes
            Route::post('city/save',['as'=>'save.city.modal','uses'=>'AjaxController@saveCity']);
            Route::get('sample/register/{sntc_no}',['as'=>'sample.register.details','uses'=>'AjaxController@getSampleRegisterFromSntc']);
            Route::get('deal/lab/{sntc_no}',['as'=>'deal.lab.report.details','uses'=>'AjaxController@getDataForDealLabReport']);
            Route::get('push/notification' , ['as' => 'send.push.notification' , 'uses' => 'NotificationController@index']);
            Route::POST('send/push/notification' , ['as' => 'post.push.notification' , 'uses' => 'NotificationController@sendNotification']);
            Route::get('trial/period' , ['as' => 'trial.period' , 'uses' => 'TrialPeriodController@index']);
            Route::post('trial/period/save' , ['as' => 'trialPeriod.save' , 'uses' => 'TrialPeriodController@save']);



            Route::get('rice/query/master' ,            ['as' => 'rice.query.master',           'uses' => 'Controller@riceQualityMaster']);
            Route::post('update/rice/query/master' ,    ['as' => 'update.rice.query.master',    'uses' => 'Controller@updateRiceQualityMaster']);
            Route::get('activate/query/{id}' ,          ['as' => 'activate.query',              'uses' => 'Controller@activateQuery']);
            Route::get('rice/query/master/accept/{id}', ['as' => 'rice.query.master.accept',    'uses' => 'Controller@riceQualityMasterAccept']);
            Route::get('rice/query/master/sold/{id}', ['as' => 'rice.query.master.sold',    'uses' => 'Controller@updateQueryMasterStatusAsSold']);

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

    Route::group(['prefix' => 'master'] , function(){
        Route::get('/' ,                        ['as' => 'master.index'         ,'uses' => 'MasterController@index' ] );

        //rice type
        Route::get('list/rice/type' ,           ['as' => 'master.list.rice.type'        ,'uses' => 'MasterController@listRiceType' ] );
        Route::post('create/rice/type' ,        ['as' => 'master.create.rice.type'      ,'uses' => 'MasterController@createRiceType' ] );
        Route::get('delete/rice/type/{id}' ,    ['as' => 'master.delete.rice.type'      ,'uses' => 'MasterController@deleteRiceType' ] );
        Route::get('get/rice/type/{id}' ,       ['as' => 'master.get.rice.type'         ,'uses' => 'MasterController@getRiceTypeById'] );
        Route::POST('update/rice/type' ,        ['as' => 'master.update.rice.type'      ,'uses' => 'MasterController@updateRiceTypeById'] );

        // rice Quality
        Route::get('list/rice/quality' ,            ['as' => 'master.list.rice.quality'     ,'uses' => 'MasterController@listRiceQuality' ] );
        Route::post('create/rice/quality' ,         ['as' => 'master.create.rice.quality'   ,'uses' => 'MasterController@createRiceQuality' ] );
        Route::get('delete/rice/quality/{id}',      ['as' => 'master.delete.rice.quality'   ,'uses' => 'MasterController@deleteRiceQuality' ] );


        Route::get('edit/rice/quality/{id}',      ['as' => 'master.edit.rice.quality'   ,'uses' => 'MasterController@editRiceQuality' ] );


        Route::get('get/rice/quality/{id}' ,        ['as' => 'master.get.rice.quality'      ,'uses' => 'MasterController@getRicequalityById'] );
        Route::POST('update/rice/quality' ,         ['as' => 'master.update.rice.quality'   ,'uses' => 'MasterController@updateRiceQualityById'] );

        //rice City
        Route::get('list/city' ,                ['as' => 'master.list.city'     ,'uses' => 'MasterController@listCity' ] );
        Route::post('create/city' ,             ['as' => 'master.create.city'   ,'uses' => 'MasterController@createCity' ] );
        Route::get('delete/city/{id}' ,         ['as' => 'master.delete.city'   ,'uses' => 'MasterController@deleteCity' ] );
        Route::get('get/rice/city/{id}' ,       ['as' => 'master.get.city'      ,'uses' => 'MasterController@getCityById'] );
        Route::POST('update/rice/city' ,        ['as' => 'master.update.city'   ,'uses' => 'MasterController@updateCityById'] );
        Route::get('city/status/{city}' ,       ['as' => 'master.city.changeStatus'   ,'uses' => 'MasterController@statusCity'] );

        //Transport States
        Route::get('list/state' ,               ['as' => 'master.transport.list.state'    ,'uses' => 'MasterController@listPort' ] );
        Route::post('create/state' ,            ['as' => 'master.transport.create.state'  ,'uses' => 'MasterController@createPort' ] );
        Route::get('delete/state/{id}' ,        ['as' => 'master.transport.delete.state'  ,'uses' => 'MasterController@deletePort' ] );
        Route::get('edit/state/{id}' ,          ['as' => 'master.transport.edit.state'    ,'uses' => 'MasterController@editPort']);
        Route::post('update/state' ,            ['as' => 'master.transport.update.state'  ,'uses' => 'MasterController@updatePort']);


        //Transport route
        Route::get('list/route' ,               ['as' => 'master.transport.list.route'      ,'uses' => 'MasterController@listRoute' ] );
        Route::post('create/route' ,            ['as' => 'master.transport.create.route'    ,'uses' => 'MasterController@createRoute' ] );
        Route::get('delete/route/{id}' ,        ['as' => 'master.transport.delete.route'    ,'uses' => 'MasterController@deleteRoute' ] );
        Route::put('get/transport/route/{id}' , ['as' => 'master.transport.update.route'    ,'uses' => 'MasterController@updateTransportRoute' ] );
        Route::get('get/route/transport/{id}' , ['as' => 'master.get.transport.route'       ,'uses' => 'MasterController@getTransportRoute' ] );
        Route::get('delete/route/transport/{id}' , ['as' => 'master.delete.transport.route'       ,'uses' => 'MasterController@deleteStatePort' ] );

        //Transport ports
        // Route::get('list/ports' ,            ['as' => 'master.transport.list.ports'    ,'uses' => 'MasterController@listPort' ] );
        // Route::post('create/ports' ,         ['as' => 'master.transport.create.ports'  ,'uses' => 'MasterController@createPort' ] );
        // Route::delete('delete/ports/{id}' ,  ['as' => 'master.transport.delete.ports'  ,'uses' => 'MasterController@deletePort' ] );


        // change date of existing user
        Route::get('change/existing/date' , ['as' => 'change.date.of.existing.user'      ,'uses' => 'MasterController@changeDateofExistingUser' ] );
        Route::POST('save/trial/period' ,   ['as' => 'trialPeriod.save'      ,'uses' => 'MasterController@saveTrialPeriod' ] );

        // change date of existing user
        Route::get('change/trial/period/date' , ['as' => 'change.date.trial.period'      ,'uses' => 'MasterController@changeTrialPeriodDate' ] );
        Route::POST('save/trial/month/period' ,   ['as' => 'trialPeriodMonth.save'      ,'uses' => 'MasterController@trialPeriodMonthSave' ] );

        // create.version
        Route::get('create/version', ['as' => 'create.version', 'uses' => 'MasterController@createVersion']);
        // save.version
        Route::post('save/version', ['as' => 'save.version', 'uses' => 'MasterController@saveVersion']);

        Route::get('create/calculator' , ['as' => 'create.calculator' , 'uses' => 'MasterController@createCalculator']);
        Route::get('report/usd/prices' , ['as' => 'report.calculator' , 'uses' => 'MasterController@USDPriceReport']);
        Route::get('delete/rice/quality/{id}' , ['as' => 'delete.rice.quality' , 'uses' => 'MasterController@deleteRiceQualityUSD']);
        Route::get('edit/rice/quality/{id}' , ['as' => 'edit.rice.quality' , 'uses' => 'MasterController@editRiceQualityUSD']);
        Route::POST('save/calculator' , ['as' => 'calculator.save' , 'uses' => 'MasterController@saveCalculator']);
        Route::POST('update/calculator' , ['as' => 'calculator.update' , 'uses' => 'MasterController@updateCalculator']);
        
        Route::GET('clone/calculator/{id}' , ['as' => 'clone.rice.quality' , 'uses' => 'MasterController@updateToTodaysCalculation']);


        Route::get('get/usd/coupons' , [ 'as' => 'get.usd.coupons' , 'uses' => 'USDPlanController@index']);
        Route::get('status/usd/coupons/{id}' , [ 'as' => 'change.status.usd.coupon' , 'uses' => 'USDPlanController@ChangeStatus']);
        Route::POST('save/usd/coupon' , [ 'as' => 'save.usd.coupon' , 'uses' => 'USDPlanController@saveUSDPlan']);

        Route::get('get/usd/plan' , [ 'as' => 'get.usd.plan' , 'uses' => 'USDPlanController@Planindex']);
        Route::get('status/usd/plan/{id}' , [ 'as' => 'change.status.usd.plan' , 'uses' => 'USDPlanController@PlanChangeStatus']);
        Route::POST('save/usd/plan' , [ 'as' => 'save.usd.plan' , 'uses' => 'USDPlanController@PlansaveUSDPlan']);
        
    });


    Route::get('contact/detail/master' , ['as' => 'contact.details.master' , 'uses' => 'ContactController@index']);
    Route::POST('contact/save/master' , ['as' => 'contact.save' , 'uses' => 'ContactController@createContact']);


    Route::get('terms/condition' , [ 'as' => 'terms.condition' , 'uses' => 'USDPlanController@termCondition']);
    Route::POST('save/terms/condition' , [ 'as' => 'save.terms.condition' , 'uses' => 'USDPlanController@saveTermCondition']);

    Route::POST('send/seller/confirm/message', [ 'as' => 'send.seller.confirm.message' , 'uses' => 'USDPlanController@sendSellerConfirmMessage']);


    Auth::routes();
    Route::POST('change/chat/status' , ['as' => 'change.chat.status' , 'uses' => 'UsersController@changeChatStatus']);

    Route::POST('hot/deal/push/notification' , ['as' => 'post.hot.deal.push.notification' , 'uses' => 'NotificationController@hotDealPushNotification']);

    Route::GET('hot/deal/notification/master' , ['as' => 'hot.deal.notification.master' , 'uses' => 'NotificationController@hotDealIndex']);

    Route::GET('packing/public/master' , ['as' => 'public.packing.master' , 'uses' => 'PublicPackingMasterController@index']);
    
    Route::POST('public/packing/master/controller' , ['as' => 'public.packing.master.controller' , 'uses' => 'PublicPackingMasterController@save']);


    Route::GET('update/hot/deal/status/{statusType}/{hotDealNotifId}' , ['as' => 'update.hot.deal.status' , 'uses' => 'NotificationController@updateHotDealStatus']);
    Route::get('logout','Auth\LoginController@logout');



    Route::GET('brands' , ['as' => 'master.brand' , 'uses' => 'BrandController@index']);
    Route::GET('brands/create' , ['as' => 'master.brand.create' , 'uses' => 'BrandController@create']);
    Route::POST('brands/save' , ['as' => 'master.brand.save' , 'uses' => 'BrandController@save']);
    Route::GET('brands/edit/{brandId}' , ['as' => 'master.brand.edit' , 'uses' => 'BrandController@edit']);
    Route::POST('brands/update' , ['as' => 'master.brand.update' , 'uses' => 'BrandController@update']);
    Route::get('brands/change/status/{brandid}' , ['as' => 'master.brand.change.status' , 'uses' => 'BrandController@changeStatus']);


    Route::GET('wand' , ['as' => 'master.wand' , 'uses' => 'WandController@index']);
    Route::GET('wand/create/{formId}' , ['as' => 'master.wand.create' , 'uses' => 'WandController@create']);
    Route::POST('wand/save' , ['as' => 'master.wand.save' , 'uses' => 'WandController@save']);
    Route::GET('wand/edit/{WandId}' , ['as' => 'master.wand.edit' , 'uses' => 'WandController@edit']);
    Route::POST('wand/update' , ['as' => 'master.wand.update' , 'uses' => 'WandController@update']);
    Route::get('wand/change/status/{Wandid}' , ['as' => 'master.wand.change.status' , 'uses' => 'WandController@changeStatus']);



    Route::get('list/sell/queries/INR' , ['as' => 'master.list.sell.queries.INR' , 'uses' => 'MasterController@listSellQueries']);

    // trade
    Route::GET('trade' , ['as' => 'master.trade' , 'uses' => 'TradeController@index']);
    Route::GET('trade/create' , ['as' => 'master.trade.create' , 'uses' => 'TradeController@create']);
    Route::POST('trade/save' , ['as' => 'master.trade.save' , 'uses' => 'TradeController@save']);
    Route::GET('trade/edit/{tradeId}' , ['as' => 'master.trade.edit' , 'uses' => 'TradeController@edit']);
    Route::POST('trade/update' , ['as' => 'master.trade.update' , 'uses' => 'TradeController@update']);
    Route::get('trade/change/status/{tradeid}' , ['as' => 'master.trade.change.status' , 'uses' => 'TradeController@changeStatus']);

});
