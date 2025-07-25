<?php

namespace App\Http\Requests\Api\Admin\Gain;

use Illuminate\Foundation\Http\FormRequest;

class GainUserHistoryIndexRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_id' => 'numeric|nullable',
            'type'   => 'numeric|nullable',
            'slug' => 'string|nullable'
        ];
    }
}
