<?php

namespace App\Http\Requests\Api\Web\Org;

use Illuminate\Foundation\Http\FormRequest;

class OrgDepartmentUserUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'nullable|string',
            'dept_id' => 'nullable|integer',
            'status' => 'nullable|integer',
            'phone' => [
                'nullable',
                'string',
                'max:20',
                function ($attribute, $value, $fail) {
                    // 验证手机号格式
                    if (!preg_match('/^[0-9]{5,15}$/', $value)) {
                        $fail('无效的手机号格式');
                    }
                },
            ],
        ];
    }
}
