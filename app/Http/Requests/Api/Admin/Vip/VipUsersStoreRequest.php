<?php

namespace App\Http\Requests\Api\Admin\Vip;

use Illuminate\Foundation\Http\FormRequest;

class VipUsersStoreRequest extends FormRequest
{
    public function rules()
    {
        return [
            'users' => 'required|array',
            'vip_sku_id' => 'required|numeric',
            'number' => 'required|numeric',
            'remark' => 'nullable|string',
            'type' => 'required|numeric'
        ];
    }
}
