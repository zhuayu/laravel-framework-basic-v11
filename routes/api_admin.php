<?php
use App\Http\Controllers\Api\Admin\Permission\AuthController;
use App\Http\Controllers\Api\Admin\Permission\AdministratorController;
use App\Http\Controllers\Api\Admin\Permission\RoleController;
use App\Http\Controllers\Api\Admin\Permission\PermissionController;
use App\Http\Controllers\Api\Admin\Vip\VipUserController;
use App\Http\Controllers\Api\Admin\Vip\VipSkuController;
use App\Http\Controllers\Api\Admin\Vip\VipUserHistoryController;


Route::post('/user/login-by-phone', [AuthController::class, 'loginByPhone']);
Route::post('/user/login-send-sms', [AuthController::class, 'loginSendSMS']);

Route::middleware(['auth:admin'])->group(function () {
    // 所有权限
    Route::get('/permissions', [PermissionController::class, 'index'])->middleware('AdminPermission:permissions-index');
    Route::get('/permissions/my', [PermissionController::class, 'my']);
    // 角色管理
    Route::get('/roles', [RoleController::class, 'index'])->middleware('AdminPermission:roles-index');
    Route::post('/roles', [RoleController::class, 'store'])->middleware('AdminPermission:roles-store');
    Route::get('/roles/{id}', [RoleController::class, 'show'])->middleware('AdminPermission:roles-show');
    Route::put('/roles/{id}', [RoleController::class, 'update'])->middleware('AdminPermission:roles-update');
    Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->middleware('AdminPermission:roles-delete');
    // 管理员管理
    Route::get('/administrators', [AdministratorController::class, 'index'])->middleware('AdminPermission:administrators-index');
    Route::post('/administrators', [AdministratorController::class, 'store'])->middleware('AdminPermission:administrators-store');
    Route::get('/administrators/{id}', [AdministratorController::class, 'show'])->middleware('AdminPermission:administrators-show');
    Route::put('/administrators/{id}', [AdministratorController::class, 'update'])->middleware('AdminPermission:administrators-update');
    Route::delete('/administrators/{id}', [AdministratorController::class, 'destroy'])->middleware('AdminPermission:administrators-delete');
    // VIP 管理
    Route::get('/vip/user', [VipUserController::class, 'index'])->middleware('AdminPermission:vip-user-index');
    Route::post('/vip/user', [VipUserController::class, 'store'])->middleware('AdminPermission:vip-user-store');
    Route::get('/vip/skus', [VipSkuController::class, 'index'])->middleware('AdminPermission:vip-user-history-index');
    Route::get('/vip/histories', [VipUserHistoryControlle::class, 'index'])->middleware('AdminPermission:vip-user-history-index');
});
