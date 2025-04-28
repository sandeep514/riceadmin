<?php 
    use App\Http\Controllers\PortalApiController;
    
    Route::group(['prefix' => 'portal','namespace'=>'portal'], function() {
    	Route::post('save/user' , [PortalApiController::class , 'saveUser']);
        Route::post('verify/otp' , [PortalApiController::class , 'verifyOTP']);
        Route::post('resend/otp' , [PortalApiController::class , 'resendOTP']);


        Route::post('update/user/details' , [PortalApiController::class , 'updateUserDetails']);
        Route::get('get/user/details/{userId}' , [PortalApiController::class , 'getUserDetails']);
        Route::delete('delete/user/{userId}' , [PortalApiController::class , 'deleteUser']);
    });
