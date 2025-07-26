<?php

namespace App\Http\Requests\Api\Web\Notification;

use Illuminate\Foundation\Http\FormRequest;

class NotificationByNameRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'nullable|string',
            
        ];
    }
}
