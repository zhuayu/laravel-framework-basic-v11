<?php

namespace App\Http\Controllers\Api\Web\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Web\User\UserUpdateRequest;

class UserController extends Controller
{
    public function userInfo(UserUpdateRequest $request) {
        return $this->success(['ok' => true]);
    }
}
