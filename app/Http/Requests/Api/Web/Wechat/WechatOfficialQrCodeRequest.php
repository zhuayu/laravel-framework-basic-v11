<?php

namespace App\Http\Requests\Api\Web\Wechat;

use Illuminate\Foundation\Http\FormRequest;

class WechatOfficialQrCodeRequest extends FormRequest
{
    public function rules()
    {
        return [
            'scene' => 'required|string',
        ];
    }
}
