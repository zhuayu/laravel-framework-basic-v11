<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Web\User\UserController;


use App\Http\Controllers\Api\Web\Wechat\WechatWebController;
use App\Http\Controllers\Api\Web\Wechat\WechatMiniprogramController;
use App\Http\Controllers\Api\Web\Wechat\WechatOfficialController;
use App\Http\Controllers\Api\Web\Org\OrganizationController;
use App\Http\Controllers\Api\Web\Org\OrgDepartmentController;
use App\Http\Controllers\Api\Web\Org\OrgGroupController;
use App\Http\Controllers\Api\Web\Org\OrgGroupUserController;
use App\Http\Controllers\Api\Web\Org\OrgDepartmentUserController;

Route::post('/user/login-by-account', [UserController::class, 'loginByAccount']);
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

    // 组织/部门/群组
    Route::get('organizations', [OrganizationController::class, 'index']);
    Route::post('organizations', [OrganizationController::class, 'store']);
    Route::get('organizations/{id}', [OrganizationController::class, 'show']);
    Route::put('organizations/{id}', [OrganizationController::class, 'update']);
    Route::delete('organizations/{id}', [OrganizationController::class, 'destroy']);

    Route::get('organizations/{orgId}/departments', [OrgDepartmentController::class, 'index']);
    Route::post('organizations/{orgId}/departments', [OrgDepartmentController::class, 'store']);
    Route::put('organizations/{orgId}/departments/{id}', [OrgDepartmentController::class, 'update']);
    Route::delete('organizations/{orgId}/departments/{id}', [OrgDepartmentController::class, 'destroy']);
    Route::put('organizations/{orgId}/departments/{id}/move', [OrgDepartmentController::class, 'move']);

    Route::get('organizations/{orgId}/groups', [OrgGroupController::class, 'index']);
    Route::post('organizations/{orgId}/groups', [OrgGroupController::class, 'store']);
    Route::get('organizations/{orgId}/groups/{id}', [OrgGroupController::class, 'show']);
    Route::put('organizations/{orgId}/groups/{id}', [OrgGroupController::class, 'update']);
    Route::delete('organizations/{orgId}/groups/{id}', [OrgGroupController::class, 'destroy']);

    Route::get('organizations/{orgId}/department-users', [OrgDepartmentUserController::class, 'index']);
    Route::post('organizations/{orgId}/department-users', [OrgDepartmentUserController::class, 'store']);
    Route::put('organizations/{orgId}/department-users/{id}', [OrgDepartmentUserController::class, 'update']);
    Route::delete('organizations/{orgId}/department-users/{id}', [OrgDepartmentUserController::class, 'destroy']);

    Route::get('organizations/{orgId}/groups/{groupId}/users', [OrgGroupUserController::class, 'index']);
    Route::post('organizations/{orgId}/groups/{groupId}/users', [OrgGroupUserController::class, 'store']);
    Route::put('organizations/{orgId}/groups/{groupId}/users/{id}', [OrgGroupUserController::class, 'update']);
    Route::delete('organizations/{orgId}/groups/{groupId}/users/{id}', [OrgGroupUserController::class, 'destroy']);

});

