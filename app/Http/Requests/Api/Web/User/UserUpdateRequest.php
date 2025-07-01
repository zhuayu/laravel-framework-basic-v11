<?php

namespace App\Http\Requests\Api\Web\User;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'test' => 'required|string',
        ];
    }
    
    public function messages(): array
    {
        return [
            'test.required' => '测试字段不能为空',
        ];
    }
}
