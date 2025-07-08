<?php

namespace App\Http\Requests\Api\Admin\Permission;

use Illuminate\Foundation\Http\FormRequest;

class AdminLoginSendSMSRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'phone' => ['required','string'],
        ];
    }
}
