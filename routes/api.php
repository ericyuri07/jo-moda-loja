<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\ProductVariantController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

$catalog = [
    'categories' => CategoryController::class,
    'products' => ProductController::class,
    'product-variants' => ProductVariantController::class,
    'product-images' => ProductImageController::class,
];

Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('register-admin', 'registerAdmin')->middleware('throttle:5,1');
    Route::post('login', 'login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', 'logout');
        Route::get('me', 'me');
    });
});

Route::apiResources($catalog, ['only' => ['index', 'show']]);

// fotos enviadas pelo app (públicas para a loja exibir)
Route::get('uploads/{filename}', [UploadController::class, 'show'])
    ->where('filename', '[A-Za-z0-9._-]+');

Route::middleware(['auth:sanctum', 'admin'])->group(function () use ($catalog) {
    Route::apiResources($catalog, ['except' => ['index', 'show']]);
    Route::apiResource('users', UserController::class);
    Route::post('uploads', [UploadController::class, 'store']);
});
