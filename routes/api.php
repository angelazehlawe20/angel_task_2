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
        Route::post('/resendVFCode','resendVFCode');
        Route::post('/confirmEmail_VFCode','confirmEmail_VFCode');
        Route::post('/login','login');
        
    
        Route::group(['middleware'=>['auth:sanctum']],function(){
            Route::controller(AuthController::class)->group(function(){
                Route::post('/resend2FAcode','resend2FAcode');
                Route::post('/Confirm2FACode','Confirm2FACode');
                Route::get('/logout','logout');
                Route::post('/refreshToken','refreshToken');
            });
            Route::controller(EmailController::class)->group(function(){
                Route::get('/sendEmail','sendEmail');
            });
        });            
    });
});


