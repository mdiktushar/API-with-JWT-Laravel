<?php

use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\Auth\OTPController;
use App\Http\Controllers\API\Auth\PasswordController;
use Illuminate\Support\Facades\Route;



// Guest routes - Accessible by unauthenticated users only
Route::middleware('guest:api')->group(function ($router) {
        // Authentication-related routes
    Route::controller(AuthController::class)->group(function () {
        Route::post('login', 'login')->name('login');
        Route::post('register', 'register')->name('register');
    });
    // OTP-related routes
    Route::controller(OTPController::class)->group(function () {
        Route::post('oto-send', 'otpSend')->name('otp.send');
        Route::post('oto-match', 'otpMatch')->name('otp.match');
    });
    // Password-related routes
    Route::controller(PasswordController::class)->group(function () {
        Route::post('chage-password', 'changePassword')->name('change.password');
    });
});



// Authenticated routes - Accessible only by authenticated users
Route::middleware('auth:api')->group(function () {
    // Authentication-related routes
    Route::controller(AuthController::class)->group(function () {
        Route::post('logout', 'logout')->name('logout');
        Route::post('refresh', 'refresh')->name('refresh.token');
    });
});