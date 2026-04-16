<?php 
    use App\Http\Controllers\PortalApiController;
    
    // ✅ Add session middleware for cookie-based authentication
    Route::group([
        'prefix' => 'portal',
        'namespace' => 'portal',
        'middleware' => [
            \App\Http\Middleware\EncryptCookies::class, // ✅ Decrypt cookies
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class, // ✅ Add cookies to response
            \Illuminate\Session\Middleware\StartSession::class, // ✅ Start session (reads cookie)
        ]
    ], function() {
        Route::post('save/user', [PortalApiController::class, 'saveUser']);
        Route::post('login/user', [PortalApiController::class, 'loginUser']);
        Route::post('verify/otp/login', [PortalApiController::class, 'verifyOTPAndLogin']);
        Route::post('verify/otp', [PortalApiController::class, 'verifyOTP']);
        Route::post('resend/otp', [PortalApiController::class, 'resendOTP']);

        // Cookie/session based endpoints should not require bearer token.
        Route::post('session', [PortalApiController::class, 'getSession']);
        Route::post('logout', [PortalApiController::class, 'logout']);

        Route::get('get/web/plans', [PortalApiController::class, 'getWebPlans']);
        Route::get('live/price/events', [PortalApiController::class, 'getLivePriceEvents']);

        Route::post('update/user/details', [PortalApiController::class, 'updateUserDetails'])
            ->middleware(['portal.session_or_token']);
        Route::post('delete/uploaded-document', [PortalApiController::class, 'deleteUserUploadedDocument'])
            ->middleware(['portal.session_or_token']);

        /** Multipart chunked upload for PAN or GST/FSSAI (see PortalApiController::uploadPortalDocumentChunk). */
        Route::post('upload/document-chunk', [PortalApiController::class, 'uploadPortalDocumentChunk'])
            ->middleware(['portal.session_or_token']);

        Route::group(['middleware' => ['portal.api.token']], function () {

            Route::get('get/user/details/{userId}', [PortalApiController::class, 'getUserDetails']);
            Route::delete('delete/user/{userId}', [PortalApiController::class, 'deleteUser']);

            Route::get('plans', [PortalApiController::class, 'getPlans']);
            Route::post('web/user/subscription', [PortalApiController::class, 'webUserSubscription']);
            Route::post('web/renew-subscription', [PortalApiController::class, 'webRenewSubscription']);
            Route::post('web/create-order', [PortalApiController::class, 'webCreateOrder']);
            Route::post('web/verify-payment', [PortalApiController::class, 'webVerifyPayment']);

            Route::post('web/plans/by-role-category', [PortalApiController::class, 'getWebPlansByRoleCategory']);
            Route::get('years/closure-status', [PortalApiController::class, 'getYearClosureStatus']);
            
            Route::get('get/latest/updated/count', [PortalApiController::class, 'getLatestUpdatedCount']);
            
            // ✅ Get web access permissions for user
            Route::post('web-access', [PortalApiController::class, 'getWebAccess']);
        });
    });
