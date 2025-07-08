<?php

namespace App\Http\Requests\Api\Admin\Permission;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Permission\Administrator;

class AdministratorRequest extends FormRequest
{
    public function rules()
    {
        if ($this->method() === 'POST') {
            $rules = [
                "role_ids" => "required|array",
                'phone' => ['required', 'string'],
                'name' => 'nullable|string',
            ];
        } else {
            $id = request()->route('id');
            $admin = Administrator::findOrFail($id);
            $userId = $admin->id;
            $rules = [
                "role_ids" => "required|array",
                'phone' => ['nullable', 'string', Rule::unique('administrators', 'phone')->ignore($userId)],
                'name' => 'nullable|string',
            ];
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'phone.unique' => '手机号已占用',
        ];
    }
}
