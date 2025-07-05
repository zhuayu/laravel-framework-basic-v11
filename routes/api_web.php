<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Web\User\UserController;

Route::post('/user/login-by-account', [UserController::class, 'loginByAccount']);
Route::post('/user/login-by-phone', [UserController::class, 'loginByPhone']);
Route::post('/user/login-send-sms', [UserController::class, 'loginSendSMS']);

Route::middleware(['auth'])->group(function () {
    Route::get('/user/user-info', [UserController::class, 'userInfo']);
});

