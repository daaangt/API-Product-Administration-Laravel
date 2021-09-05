<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\ProductsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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


// Route::get('/docs', function () {
//     return view('vendor.l5-swagger.index');
// });

/* Authetication */
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

/* Unauthenticated Routes */
Route::apiResource('products', ProductsController::class)->only([
    'index', 'show'
]);

Route::apiResource('categories', CategoriesController::class)->only([
    'index', 'show'
]);

/* Authenticated routes */
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('products', ProductsController::class)->except([
        'index', 'show'
    ]);

    Route::apiResource('categories', CategoriesController::class)->except([
        'index', 'show'
    ]);

    Route::post('/logout', [AuthController::class, 'logout']);
});
