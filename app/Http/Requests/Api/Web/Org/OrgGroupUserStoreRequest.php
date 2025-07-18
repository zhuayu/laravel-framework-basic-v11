<?php

namespace App\Http\Requests\Api\Web\Org;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Org\OrgGroupUser;

class OrgGroupUserStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|integer',
            'role' => ['required', Rule::in([
                OrgGroupUser::ROLE_OWNER,
                OrgGroupUser::ROLE_ADMIN,
                OrgGroupUser::ROLE_MEMBER
            ])],
        ];
    }
}
