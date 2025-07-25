<?php

namespace App\Http\Requests\Api\Admin\Vip;

use Illuminate\Foundation\Http\FormRequest;

class VipUserIndexRequest extends FormRequest
{
    public function rules()
    {
        return [
            'nickname' => 'nullable|string',
            'phone' => 'nullable|numeric',
            'user_id' => 'nullable|numeric',
            'vip_id' => 'nullable|numeric',
        ];
    }
}
