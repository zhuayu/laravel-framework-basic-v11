<?php

namespace App\Http\Requests\Api\Web\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserLoginSendSMSRequest extends FormRequest
{
    public function rules()
    {
        return [
            'phone_prefix' => [
                'required',
                'string',
                'max:8',
                function ($attribute, $value, $fail) {
                    // 验证国家代码格式 (如 +86, 1, 44)
                    if (!preg_match('/^\+?[0-9]{1,6}$/', $value)) {
                        $fail('无效的国家代码格式');
                    }
                }
            ],
            'phone' => [
                'required',
                'string',
                'max:20',
                function ($attribute, $value, $fail) {
                    // 验证手机号格式
                    if (!preg_match('/^[0-9]{5,15}$/', $value)) {
                        $fail('无效的手机号格式');
                    }
                },
                // 唯一性检查放在控制器中处理
            ],
        ];
    }
}