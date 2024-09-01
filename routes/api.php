<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::prefix('v1/auth')->group(function () {
    Route::post('/register',[AuthController::class,'register']);
    Route::post('/verifyEmail',[AuthController::class,'verifyEmail']);
    Route::post('/verifyTwoFactor', [AuthController::class, 'verifyTwoFactor']);
    Route::post('/login',[AuthController::class,'login']);



    Route::group(['middleware'=>['auth:sanctum']],function(){
        Route::get('/sendEmail',[EmailController::class,'sendEmail']);
        Route::get('/logout',[AuthController::class,'logout']);
        Route::post('/refreshToken',[AuthController::class,'refreshToken']);


    });

});

