<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;

// Route for register and login
Route::post('register',[AuthController::class, 'register']);
Route::post('login',[AuthController::class, 'login']);

Route::group(['middleware' => 'auth:sanctum'], function(){
    Route::post('refreshtoken', [AuthController::class, 'refreshToken']);
    Route::post('logout',[AuthController::class, 'logout']);
    Route::resource('products', ProductController::class);
});

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
