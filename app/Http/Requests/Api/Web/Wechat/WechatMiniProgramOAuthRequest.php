<?php

namespace App\Http\Requests\Api\Web\Wechat;

use Illuminate\Foundation\Http\FormRequest;

class WechatMiniProgramOAuthRequest extends FormRequest
{
    public function rules(): array
    {
       return [
            'code' => 'required|string',
        ];
    }
}
