<?php

namespace App\Http\Requests\Api\Web\Org;

use Illuminate\Foundation\Http\FormRequest;

class OrgDepartmentUserIndexRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'dept_id' => 'nullable|integer',
            'user_id' => 'nullable|integer',
            'phone' => 'nullable|string',
            'name' => 'nullable|string',
        ];
    }
}
