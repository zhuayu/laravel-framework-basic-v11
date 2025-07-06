<?php

namespace App\Http\Requests\Api\Web\Wechat;

use Illuminate\Foundation\Http\FormRequest;

class WechatOfficialScanAuthRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'key' => 'required|string',
        ];
    }
}
