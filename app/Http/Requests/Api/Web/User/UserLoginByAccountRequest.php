<?php

namespace App\Http\Requests\Api\Web\User;

use Illuminate\Foundation\Http\FormRequest;

class UserLoginByAccountRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'account' => 'required|string|min:5|max:20',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'password.regex' => '密码必须包含大小写字母、数字和特殊字符',
        ];
    }
}
