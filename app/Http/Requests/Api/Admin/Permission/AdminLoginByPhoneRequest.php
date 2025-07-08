<?php

namespace App\Http\Requests\Api\Admin\Permission;

use Illuminate\Foundation\Http\FormRequest;

class AdminLoginByPhoneRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'phone' => ['required','string'],
            'code' => ['required','string'],
            'key' => ['required','string'],
        ];
    }
}
