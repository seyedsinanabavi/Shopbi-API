<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\PostController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // ==================== Auth ====================
    Route::prefix('auth')->group(function () {
        Route::post('send-otp',   [AuthController::class, 'sendOtp'])->middleware('throttle:5,1');
        Route::post('verify-otp', [AuthController::class, 'verifyOtp'])->middleware('throttle:10,1');
        Route::post('register',   [AuthController::class, 'register'])->middleware('throttle:5,1');
    });

    Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
        Route::get('me',          [AuthController::class, 'me']);
        Route::post('logout',     [AuthController::class, 'logout']);
        Route::post('logout-all', [AuthController::class, 'logoutAll']);
        Route::post('refresh',    [AuthController::class, 'refresh']);
    });

    // ==================== Posts ====================
    Route::prefix('posts')->group(function () {
        // عمومی
        Route::get('/',        [PostController::class, 'index']);
        Route::get('/{post}',  [PostController::class, 'show']);

        // نیاز به لاگین
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/',           [PostController::class, 'store']);
            Route::match(['put', 'patch'], '/{post}', [PostController::class, 'update']);
            Route::delete('/{post}',   [PostController::class, 'destroy']);
        });
    });

    // مقالات من
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('my-posts', [PostController::class, 'myPosts']);
    });
});