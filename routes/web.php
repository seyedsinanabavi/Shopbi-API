<?php

use App\Http\Controllers\Api\V1\RobotsController;
use App\Http\Controllers\Api\V1\SitemapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return response()->json([
        'name'    => config('app.name'),
        'version' => '1.0.0',
        'status'  => 'API is running',
    ]);
});

// ==================== SEO ====================
Route::get('/sitemap.xml', [SitemapController::class, 'index']);
Route::get('/robots.txt',  [RobotsController::class, 'index']);