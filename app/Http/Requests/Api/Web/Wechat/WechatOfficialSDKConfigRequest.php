<?php

namespace App\Http\Requests\Api\Web\Wechat;

use Illuminate\Foundation\Http\FormRequest;

class WechatOfficialSDKConfigRequest extends FormRequest
{
    public function rules()
    {
        return [
            'url' => 'required|string',
            'apis' => 'required|array',
            'tags' => 'nullable|array'
        ];
    }
}
