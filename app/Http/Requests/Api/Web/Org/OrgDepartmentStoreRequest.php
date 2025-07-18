<?php

namespace App\Http\Requests\Api\Web\Org;

use Illuminate\Foundation\Http\FormRequest;

class OrgDepartmentStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:50',
            'parent_id' => 'required|exists:org_departments,id'
        ];
    }
}
