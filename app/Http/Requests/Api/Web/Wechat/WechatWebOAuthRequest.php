<?php

namespace App\Http\Requests\Api\Web\Wechat;

use Illuminate\Foundation\Http\FormRequest;

class WechatWebOAuthRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'code' => 'required|string',
            'redirect_uri' => 'nullable|string',
        ];
    }
}
