<?php

namespace App\Http\Requests\Api\Admin\Vip;

use Illuminate\Foundation\Http\FormRequest;

class VipUserHistoryIndexRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'nullable|string',
            'user_id' => 'nullable|numeric'
        ];
    }
}