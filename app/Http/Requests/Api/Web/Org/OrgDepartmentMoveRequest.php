<?php

namespace App\Http\Requests\Api\Web\Org;

use Illuminate\Foundation\Http\FormRequest;

class OrgDepartmentMoveRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'target_id' => 'required|exists:org_departments,id',
            'position' => 'required|in:before,after,inside'
        ];
    }
}
