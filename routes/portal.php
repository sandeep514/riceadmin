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
        Route::get('paddy/trades', [\App\Http\Controllers\PaddyApiController::class, 'listPaddyTrades']);
        Route::get('paddy/trade/{id}', [\App\Http\Controllers\PaddyApiController::class, 'getPaddyTradeDetail']);
        Route::post('interested/paddy/trade', [\App\Http\Controllers\PaddyApiController::class, 'showPaddyTradeInterest']);

        // Interested functionality APIs
        Route::get('interested/rice-qualities', [PortalApiController::class, 'getRiceQualitiesList']);
        Route::get('interested/rice-form', [PortalApiController::class, 'getRiceFormsList']);
        Route::get('interested/rice-forms', [PortalApiController::class, 'getRiceFormsList']);
        Route::post('interested/wands', [PortalApiController::class, 'getWandsByRiceFormMap']);
        Route::post('interested/save', [PortalApiController::class, 'saveUserInterestedMap']);

        Route::post('update/user/details', [PortalApiController::class, 'updateUserDetails'])
            ->middleware(['portal.session_or_token']);
        Route::post('delete/uploaded-document', [PortalApiController::class, 'deleteUserUploadedDocument'])
            ->middleware(['portal.session_or_token']);

        /** Multipart chunked upload for PAN or GST/FSSAI (see PortalApiController::uploadPortalDocumentChunk). */
        Route::post('upload/document-chunk', [PortalApiController::class, 'uploadPortalDocumentChunk'])
            ->middleware(['portal.session_or_token']);

        // Invoice PDF: signed URL from payment history, or same Bearer/X-API-TOKEN as other portal APIs.
        Route::get('web/invoice/{id}', [PortalApiController::class, 'downloadWebInvoice'])
            ->name('portal.web.invoice')
            ->middleware('signed.or.portal.token')
            ->where('id', '[0-9]+');

        Route::group(['middleware' => ['portal.api.token']], function () {

            Route::get('get/user/details/{userId}', [PortalApiController::class, 'getUserDetails']);
            Route::delete('delete/user/{userId}', [PortalApiController::class, 'deleteUser']);

            Route::get('plans', [PortalApiController::class, 'getPlans']);
            Route::post('web/user/subscription', [PortalApiController::class, 'webUserSubscription']);
            Route::post('web/renew-subscription', [PortalApiController::class, 'webRenewSubscription']);
            Route::post('web/create-order', [PortalApiController::class, 'webCreateOrder']);
            Route::post('web/verify-payment', [PortalApiController::class, 'webVerifyPayment']);

            Route::post('web/rice-bag-product/create', [\App\Http\Controllers\WebRiceBagProductController::class, 'create']);
            Route::post('web/rice-bag-product/update', [\App\Http\Controllers\WebRiceBagProductController::class, 'update']);
            Route::get('web/rice-bag-product/list/{userId}', [\App\Http\Controllers\WebRiceBagProductController::class, 'listByUser']);
            Route::delete('web/rice-bag-product/image/{imageId}', [\App\Http\Controllers\WebRiceBagProductController::class, 'deleteImage']);
            Route::get('web/rice-bag-product/{id}', [\App\Http\Controllers\WebRiceBagProductController::class, 'show'])->where('id', '[0-9]+');
            Route::delete('web/rice-bag-product/{id}', [\App\Http\Controllers\WebRiceBagProductController::class, 'delete'])->where('id', '[0-9]+');

            Route::post('web/cartoon-product/create', [\App\Http\Controllers\WebCartoonProductController::class, 'create']);
            Route::post('web/cartoon-product/update', [\App\Http\Controllers\WebCartoonProductController::class, 'update']);
            Route::get('web/cartoon-product/list/{userId}', [\App\Http\Controllers\WebCartoonProductController::class, 'listByUser']);
            Route::delete('web/cartoon-product/image/{imageId}', [\App\Http\Controllers\WebCartoonProductController::class, 'deleteImage']);
            Route::get('web/cartoon-product/{id}', [\App\Http\Controllers\WebCartoonProductController::class, 'show'])->where('id', '[0-9]+');
            Route::delete('web/cartoon-product/{id}', [\App\Http\Controllers\WebCartoonProductController::class, 'delete'])->where('id', '[0-9]+');

            Route::post('web/cylinder-product/create', [\App\Http\Controllers\WebCylinderProductController::class, 'create']);
            Route::post('web/cylinder-product/update', [\App\Http\Controllers\WebCylinderProductController::class, 'update']);
            Route::get('web/cylinder-product/list/{userId}', [\App\Http\Controllers\WebCylinderProductController::class, 'listByUser']);
            Route::delete('web/cylinder-product/image/{imageId}', [\App\Http\Controllers\WebCylinderProductController::class, 'deleteImage']);
            Route::get('web/cylinder-product/{id}', [\App\Http\Controllers\WebCylinderProductController::class, 'show'])->where('id', '[0-9]+');
            Route::delete('web/cylinder-product/{id}', [\App\Http\Controllers\WebCylinderProductController::class, 'delete'])->where('id', '[0-9]+');

            Route::post('web/plans/by-role-category', [PortalApiController::class, 'getWebPlansByRoleCategory']);
            Route::get('years/closure-status', [PortalApiController::class, 'getYearClosureStatus']);
            
            Route::get('get/latest/updated/count', [PortalApiController::class, 'getLatestUpdatedCount']);
            
            // ✅ Get web access permissions for user
            Route::post('web-access', [PortalApiController::class, 'getWebAccess']);
            Route::post('web/subscription-history', [PortalApiController::class, 'getWebSubscriptionHistory']);
            Route::get('web/notifications', [PortalApiController::class, 'getWebPortalNotifications']);
            Route::post('web/notifications', [PortalApiController::class, 'getWebPortalNotifications']);
            Route::post('web/notifications/clear', [PortalApiController::class, 'clearWebPortalNotifications']);
        });
    });
