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

Route::prefix('v1/auth')->group(function() {

    Route::controller(AuthController::class)->group(function () {
        Route::post('/signup', 'signup');
        Route::post('/login', 'login');
        Route::post('/confirmEmail_VFCode', 'confirmEmail_VFCode');
        Route::post('/resendVFCode', 'resendVFCode')->middleware('email.not.verified');


        Route::middleware(['auth:sanctum','refresh.token'])->group(function () {
            Route::post('/resend2FAcode', 'resend2FAcode');
            Route::post('/Confirm2FACode', 'Confirm2FACode');
            Route::post('/refreshToken', 'refreshToken');

        });

        Route::middleware(['auth:sanctum'])->group(function () {
            Route::get('/logout', 'logout');
        });
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::controller(EmailController::class)->group(function () {
            Route::get('/sendEmail', 'sendEmail');
        });
    });

});



