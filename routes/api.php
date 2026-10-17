<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\SettingController;
use App\Http\Controllers\Api\V1\TagController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // ==================== Auth (عمومی) ====================
    Route::prefix('auth')->group(function () {
        Route::post('send-otp',   [AuthController::class, 'sendOtp'])->middleware('throttle:5,1');
        Route::post('verify-otp', [AuthController::class, 'verifyOtp'])->middleware('throttle:10,1');
        Route::post('register',   [AuthController::class, 'register'])->middleware('throttle:5,1');
    });

    // ==================== Auth (نیاز به لاگین) ====================
    Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
        Route::get('me',          [AuthController::class, 'me']);
        Route::post('logout',     [AuthController::class, 'logout']);
        Route::post('logout-all', [AuthController::class, 'logoutAll']);
        Route::post('refresh',    [AuthController::class, 'refresh']);
    });

    // ==================== Posts (عمومی) ====================
    Route::get('posts',        [PostController::class, 'index']);
    Route::get('posts/{post}', [PostController::class, 'show']);

    // ==================== Categories (عمومی) ====================
    Route::get('categories',            [CategoryController::class, 'index']);
    Route::get('categories/{category}', [CategoryController::class, 'show']);

    // ==================== Tags (عمومی) ====================
    Route::get('tags',       [TagController::class, 'index']);
    Route::get('tags/{tag}', [TagController::class, 'show']);

    // ==================== Comments (عمومی) ====================
    Route::get('posts/{post}/comments', [CommentController::class, 'index']);

    // ==================== Users (عمومی) ====================
    Route::get('users/{user}', [UserController::class, 'show']);

    // ==================== Settings (عمومی) ====================
    Route::get('settings', [SettingController::class, 'index']);

    // ==================== نیاز به لاگین ====================
    Route::middleware('auth:sanctum')->group(function () {

        // ----- Posts -----
        Route::post('posts',           [PostController::class, 'store']);
        Route::match(['put', 'patch'], 'posts/{post}', [PostController::class, 'update']);
        Route::delete('posts/{post}',  [PostController::class, 'destroy']);
        Route::get('my-posts',         [PostController::class, 'myPosts']);

        // ----- Comments -----
        Route::post('posts/{post}/comments', [CommentController::class, 'store']);
        Route::delete('comments/{comment}',  [CommentController::class, 'destroy']);

        // ----- Profile -----
        Route::get('profile',       [UserController::class, 'profile']);
        Route::put('profile',       [UserController::class, 'updateProfile']);
        Route::get('profile/posts', [UserController::class, 'myPosts']);

        // ----- Categories (ادمین/ادیتور) -----
        Route::middleware('role:admin|editor')->group(function () {
            Route::post('categories',              [CategoryController::class, 'store']);
            Route::put('categories/{category}',    [CategoryController::class, 'update']);
            Route::delete('categories/{category}', [CategoryController::class, 'destroy']);
        });

        // ----- Tags (ادمین/ادیتور) -----
        Route::middleware('role:admin|editor')->group(function () {
            Route::post('tags',         [TagController::class, 'store']);
            Route::put('tags/{tag}',    [TagController::class, 'update']);
            Route::delete('tags/{tag}', [TagController::class, 'destroy']);
        });
    });
});