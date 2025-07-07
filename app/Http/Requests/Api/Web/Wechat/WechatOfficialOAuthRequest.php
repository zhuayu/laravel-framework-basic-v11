<?php

namespace App\Http\Requests\Api\Web\Wechat;

use Illuminate\Foundation\Http\FormRequest;

class WechatOfficialOAuthRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'redirect_uri' => 'required|string',
        ];
    }
}
