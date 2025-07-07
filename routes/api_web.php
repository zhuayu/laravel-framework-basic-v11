<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Web\User\UserController;


use App\Http\Controllers\Api\Web\Wechat\WechatWebController;
use App\Http\Controllers\Api\Web\Wechat\WechatMiniprogramController;
use App\Http\Controllers\Api\Web\Wechat\WechatOfficialController;

Route::post('/user/login-by-phone', [UserController::class, 'loginByPhone']);
Route::post('/user/login-send-sms', [UserController::class, 'loginSendSMS']);

Route::get('/wechat/app/:id/web/oauth', [WechatWebController::class, 'oauth']);
Route::get('/wechat/app/:id/miniprogram/oauth', [WechatMiniprogramController::class, 'oauth']);
Route::get('/wechat/app/:id/miniprogram/phone', [WechatMiniprogramController::class, 'getphone']);

Route::get('/wechat/app/:id/official/oauth', [WechatOfficialController::class, 'oauth']);
Route::get('/wechat/app/:id/official/sdk-config', [WechatOfficialController::class, 'sdkConfig']);
Route::get('/wechat/app/:id/official/scan-qrcode', [WechatOfficialController::class, 'scanQrCode']);
Route::get('/wechat/app/:id/official/scan-oauth', [WechatOfficialController::class, 'scanOAuth']);
Route::get('/wechat/app/:id/official/scan-status', [WechatOfficialController::class, 'scanStatus']);
Route::get('/wechat/app/:id/official/message-callback', [WechatOfficialController::class, 'messageCheck']);
Route::post('/wechat/app/:id/official/message-callback', [WechatOfficialController::class, 'messageCallback']);

Route::middleware(['auth'])->group(function () {
    Route::get('/user/user-info', [UserController::class, 'userInfo']);
    Route::post('/user/bind-account', [UserController::class, 'bindAccount']);
    Route::post('/user/bind-phone', [UserController::class, 'bindPhone']);
});

