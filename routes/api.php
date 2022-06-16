<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductsImageController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\CategoryController;
use \App\Http\Controllers\ShopController;
use \App\Http\Controllers\ProductController;
use \App\Http\Controllers\CartController;
use \App\Http\Controllers\RateController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => 'api'], function ($router) {
    Route::group(['prefix' => 'auth'], function ($router) {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::get('/user-profile', [AuthController::class, 'userProfile']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    Route::group(['middleware' => 'auth:api'], function ($router) {
        Route::resource('categories', CategoryController::class)->only(['index', 'show']);
        Route::resource('shops', ShopController::class)->only(['index', 'show']);
        Route::resource('products', ProductController::class)->only(['index', 'show']);

        Route::group(['middleware' => 'userType:seller'], function () {
            Route::prefix('user')->group(function () {
                Route::post('/create', [UserController::class, 'create']);
                Route::get('/showall', [UserController::class, 'showAll']);
                Route::get('/find/{id}', [UserController::class, 'find']);
                Route::post('/update/{id}', [UserController::class, 'update']);
                Route::post('/destroy/{id}', [UserController::class, 'delete']);
            });

            Route::resource('categories', CategoryController::class)->except(['index', 'show']);
            Route::resource('shops', ShopController::class)->except(['index', 'show']);
            Route::resource('products', ProductController::class)->except(['index', 'show']);
        });

        Route::group(['middleware' => 'userType:buyer'], function () {
            Route::post('product/images/{id}', [ProductsImageController::class, 'store']);
            Route::delete('product/images/{id}', [ProductsImageController::class, 'destroy']);
            Route::post('product/images/reorder/{id}', [ProductsImageController::class, 'reorder']);

            Route::get('carts', [CartController::class, 'index']);
            Route::post('cart', [CartController::class, 'store']);
            Route::put('cart/{id}', [CartController::class, 'update']);
            Route::delete('cart/{id}', [CartController::class, 'destroy']);
            Route::post('cart/checkout', [CartController::class, 'checkout']);
            Route::resource('rates', RateController::class);
        });
    });
});
