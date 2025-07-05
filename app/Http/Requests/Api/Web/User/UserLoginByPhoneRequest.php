<?php

namespace App\Http\Requests\Api\Web\User;

use Illuminate\Foundation\Http\FormRequest;

class UserLoginByPhoneRequest extends FormRequest
{
    public function rules()
    {
        return [
            'key' => 'required|string',
            'phone_prefix' => 'required|string|max:8',
            'phone' => 'required|string|max:20',
            'code' => 'required|string|min:4|max:6',
        ];
    }
}
