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

Route::prefix('v1/auth')->group(function(){
    Route::controller(AuthController::class)->group(function () {
        Route::post('/signup','signup');
        Route::post('/verifyEmail','verifyEmail');
        Route::post('/resendVFCode','resendVFCode');
        Route::post('/verifyTwoFactor','verifyTwoFactor');
        Route::post('/login','login');
    
        Route::group(['middleware'=>['auth:sanctum']],function(){
            Route::controller(AuthController::class)->group(function(){
                Route::get('/logout','logout');
                Route::post('/refreshToken','refreshToken');
            });
            Route::controller(EmailController::class)->group(function(){
                Route::get('/sendEmail','sendEmail');
            });
        });            
    });
});


