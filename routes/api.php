<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\ProductVariantController;
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
    Route::post('login', 'login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', 'logout');
        Route::get('me', 'me');
    });
});

Route::apiResources($catalog, ['only' => ['index', 'show']]);

Route::middleware(['auth:sanctum', 'admin'])->group(function () use ($catalog) {
    Route::apiResources($catalog, ['except' => ['index', 'show']]);
    Route::apiResource('users', UserController::class);
});
