<?php 
    use App\Http\Controllers\PortalApiController;
    
    Route::group(['prefix' => 'portal','namespace'=>'portal'], function() {
        Route::post('save/user' , [PortalApiController::class , 'saveUser']);
        Route::post('login/user' , [PortalApiController::class , 'loginUser']);
        Route::post('verify/otp/login' , [PortalApiController::class , 'verifyOTPAndLogin']);
        Route::post('verify/otp' , [PortalApiController::class , 'verifyOTP']);
        Route::post('resend/otp' , [PortalApiController::class , 'resendOTP']);


        Route::post('update/user/details' , [PortalApiController::class , 'updateUserDetails']);
        Route::get('get/user/details/{userId}' , [PortalApiController::class , 'getUserDetails']);
        Route::delete('delete/user/{userId}' , [PortalApiController::class , 'deleteUser']);

        Route::get('plans' , [PortalApiController::class , 'getPlans']);
        Route::POST('web/user/subscription' , [PortalApiController::class , 'webUserSubscription']);
        Route::POST('web/create-order' , [PortalApiController::class , 'webCreateOrder']);
        Route::POST('web/verify-payment' , [PortalApiController::class , 'webVerifyPayment']);


        Route::get('get/web/plans' , [PortalApiController::class , 'getWebPlans']);

        
        Route::get('get/latest/updated/count' , [PortalApiController::class , 'getLatestUpdatedCount']);

    });