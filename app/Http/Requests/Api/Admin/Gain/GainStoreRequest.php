<?php

namespace App\Http\Requests\Api\Admin\Gain;

use Illuminate\Foundation\Http\FormRequest;

class GainStoreRequest extends FormRequest
{
    public function rules()
    {
        return [
            'ids'  =>  "array|required",
            'num'  =>  "numeric|required"
        ];
    }
}
