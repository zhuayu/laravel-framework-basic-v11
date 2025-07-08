<?php

namespace App\Http\Requests\Api\Admin\Permission;

use Illuminate\Foundation\Http\FormRequest;

class RoleRequest extends FormRequest
{
    public function rules()
    {
        $rules = [
            "display_name" => 'required|string',
            'description' => 'required|string',
            'permission_ids' => 'required|array'
        ];


        if($this->method() === 'POST') {
            $rules += [
                'name' => 'required|max:255|unique:roles,name',
            ];
        } else {
            $rules += [
                'name' => 'required|max:255|string',
            ];
        }

        return $rules;
    }

    public function attributes()
    {
        return [
            'name' => '角色名称',
            'display_name' => '展示名称',
            'description' => '描述',
            'permission_ids' => '权限ID'
        ];
    }
}
