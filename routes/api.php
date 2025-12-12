<?php

use App\Ports\Http\Controllers\Api\CategoryController;
use App\Ports\Http\Controllers\Api\OrderController;
use App\Ports\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => 'api',
    'prefix' => '/v1'
], function () {
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'getAll']);
        Route::get('/{id}', [ProductController::class, 'find']);
    });

    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'getAll']);
        Route::get('/{id}', [CategoryController::class, 'findById']);
    });

    Route::prefix('orders')->group(function () {
        Route::post(  '/basket/add', [OrderController::class, 'basketAdd']);
        Route::post(  '/pay/{orderId}', [OrderController::class, 'pay']);
        Route::post(  '/delete/{orderId}', [OrderController::class, 'delete']);

        Route::get(  '/{orderId}/detail', [OrderController::class, 'orderDetail']);
    });
});
