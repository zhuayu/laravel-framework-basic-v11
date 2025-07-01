<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Web\User\UserController;

Route::get('/user/user-info', [UserController::class, 'userInfo']);
